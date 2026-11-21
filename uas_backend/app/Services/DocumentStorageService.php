<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\EmployeeDecision;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    /**
     * حفظ وتخزين وثيقة أرشيفية لموظف
     */
    public static function storeEmployeeDocument(Employee $employee, UploadedFile $file, array $data): Document
    {
        $uuid = (string) Str::uuid();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $folder = 'archives/' . ($employee->employee_number ?? $employee->employee_id);
        $fileName = $uuid . '.' . $ext;
        $storageKey = $folder . '/' . $fileName;

        // تخزين في القرص المحمي الخاص
        $file->storeAs($folder, $fileName, 'local');
        $sha256 = hash_file('sha256', $file->getRealPath());

        $docTypeId = $data['document_type_id'] ?? (DocumentType::first()?->document_type_id ?? 1);
        $docTitle = $data['document_title'] ?? 'وثيقة رسمية مؤرشفة';

        $document = Document::create([
            'employee_id' => $employee->employee_id,
            'document_type_id' => $docTypeId,
            'document_title' => $docTitle,
            'document_number' => $data['document_number'] ?? null,
            'document_date' => $data['document_date'] ?? now()->toDateString(),
            'issuing_authority' => $data['issuing_authority'] ?? 'جامعة إب',
            'description' => $data['description'] ?? null,
            'is_confidential' => !empty($data['is_confidential']),
        ]);

        DocumentVersion::create([
            'document_id' => $document->document_id,
            'version_number' => 1,
            'storage_key' => $storageKey,
            'original_file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: ($ext === 'pdf' ? 'application/pdf' : 'image/' . $ext),
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => $sha256,
            'uploaded_at' => now(),
            'is_current' => true,
        ]);

        AuditLogService::log('DOCUMENT_UPLOAD', 'documents', $document->document_id, null, [
            'document_title' => $document->document_title,
            'document_number' => $document->document_number,
            'employee_id' => $employee->employee_id,
        ]);

        return $document;
    }

    /**
     * حفظ وتخزين الصورة الشخصية للموظف
     */
    public static function storeEmployeePhoto(Employee $employee, UploadedFile $photo): Document
    {
        $photoDocType = DocumentType::firstOrCreate(
            ['type_code' => 'PHOTO'],
            ['type_name_ar' => 'صورة شخصية', 'applies_to' => 'employee', 'is_active' => true]
        );

        $docPhoto = Document::create([
            'employee_id' => $employee->employee_id,
            'document_type_id' => $photoDocType->document_type_id,
            'document_title' => 'الصورة الشخصية الرسمية',
            'document_date' => now()->toDateString(),
            'issuing_authority' => 'جامعة إب',
            'is_confidential' => false,
        ]);

        $pUuid = (string) Str::uuid();
        $ext = strtolower($photo->getClientOriginalExtension() ?: 'jpg');
        $pFolder = 'photos/' . ($employee->employee_number ?? $employee->employee_id);
        $pFileName = $pUuid . '.' . $ext;
        $pStorageKey = $pFolder . '/' . $pFileName;

        $photo->storeAs($pFolder, $pFileName, 'public');

        DocumentVersion::create([
            'document_id' => $docPhoto->document_id,
            'version_number' => 1,
            'storage_key' => $pStorageKey,
            'original_file_name' => $photo->getClientOriginalName(),
            'mime_type' => $photo->getMimeType(),
            'file_size_bytes' => $photo->getSize(),
            'checksum_sha256' => hash_file('sha256', $photo->getRealPath()),
            'uploaded_at' => now(),
            'is_current' => true,
        ]);

        $employee->photo_document_id = $docPhoto->document_id;
        $employee->save();

        return $docPhoto;
    }

    /**
     * حفظ وتخزين وثيقة قرار إداري
     */
    public static function storeDecisionDocument(EmployeeDecision $decision, UploadedFile $file): Document
    {
        $docType = DocumentType::firstOrCreate(
            ['type_code' => 'DECISION_DOC'],
            ['type_name_ar' => 'قرار إداري رسمي', 'applies_to' => 'decision', 'is_active' => true]
        );

        $document = Document::create([
            'employee_id' => $decision->employee_id,
            'document_type_id' => $docType->document_type_id,
            'document_title' => 'وثيقة قرار إداري: ' . $decision->decision_number,
            'document_number' => $decision->decision_number,
            'document_date' => $decision->issue_date,
            'issuing_authority' => $decision->issuing_authority,
            'is_confidential' => true,
        ]);

        $uuid = (string) Str::uuid();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $folder = 'archives/decisions';
        $fileName = $uuid . '.' . $ext;
        $storageKey = $folder . '/' . $fileName;

        $file->storeAs($folder, $fileName, 'local');

        DocumentVersion::create([
            'document_id' => $document->document_id,
            'version_number' => 1,
            'storage_key' => $storageKey,
            'original_file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: ($ext === 'pdf' ? 'application/pdf' : 'image/' . $ext),
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
            'uploaded_at' => now(),
            'is_current' => true,
        ]);

        $decision->documents()->attach($document->document_id);

        return $document;
    }

    /**
     * حذف آمن للوثيقة ومستنداتها الفيزيائية مع منع كسر المفاتيح الأجنبية
     */
    public static function deleteDocument(Document $doc): void
    {
        foreach ($doc->versions as $version) {
            if (!empty($version->storage_key)) {
                Storage::disk('public')->delete($version->storage_key);
                Storage::disk('local')->delete($version->storage_key);
            }
        }

        DB::table('decision_documents')->where('document_id', $doc->document_id)->delete();
        DB::table('qualification_documents')->where('document_id', $doc->document_id)->delete();
        DB::table('employment_documents')->where('document_id', $doc->document_id)->delete();
        DB::table('employees')->where('photo_document_id', $doc->document_id)->update(['photo_document_id' => null]);

        $doc->versions()->delete();
        $doc->delete();

        AuditLogService::log('DOCUMENT_DELETE', 'documents', $doc->document_id, [
            'document_title' => $doc->document_title,
            'employee_id' => $doc->employee_id,
        ], null);
    }
}
