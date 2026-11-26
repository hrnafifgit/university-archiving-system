<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
use App\Models\OrganizationalUnit;
use App\Services\AuditLogService;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchiveController extends Controller
{
    /**
     * شاشة المستودع الرقمي ومحرك بحث الأرشيف الإلكتروني
     */
    public function index(Request $request)
    {
        $query = Document::with(['employee', 'documentType', 'currentVersion', 'versions']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('document_title', 'ilike', "%{$s}%")
                  ->orWhere('document_number', 'ilike', "%{$s}%")
                  ->orWhere('description', 'ilike', "%{$s}%")
                  ->orWhereHas('employee', function ($eq) use ($s) {
                      $eq->where('full_name_ar', 'ilike', "%{$s}%")
                         ->orWhere('employee_number', 'ilike', "%{$s}%");
                  });
            });
        }

        if ($request->filled('type_id')) {
            $query->where('document_type_id', $request->type_id);
        }

        if ($request->filled('confidential')) {
            $query->where('is_confidential', $request->confidential == '1');
        }

        $activeUnit = null;
        $unitId = $request->unit_id ?? $request->dept_id;
        if (!empty($unitId)) {
            $query->whereHas('employee.employmentRecords', function ($q) use ($unitId) {
                $q->where('organizational_unit_id', $unitId);
            });
            $activeUnit = \App\Models\OrganizationalUnit::find($unitId);
        }

        $collegeId = $request->college_id ?? $request->faculty_id;
        if (!empty($collegeId)) {
            $query->whereHas('employee.employmentRecords.organizationalUnit', function ($q) use ($collegeId) {
                $q->where('organizational_unit_id', $collegeId)
                  ->orWhere('parent_unit_id', $collegeId);
            });
            if (!$activeUnit) {
                $activeUnit = \App\Models\OrganizationalUnit::find($collegeId);
            }
        }

        $documents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $documentTypes = DocumentType::orderBy('type_name_ar')->get();
        $colleges = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'administration'])
            ->orderBy('unit_name_ar')
            ->get(['organizational_unit_id', 'unit_name_ar']);
        $departments = OrganizationalUnit::where('unit_type', 'department')
            ->orderBy('unit_name_ar')
            ->get(['organizational_unit_id', 'unit_name_ar', 'parent_unit_id']);
        $employees = Employee::orderBy('full_name_ar')->limit(150)->get(['employee_id', 'full_name_ar', 'employee_number']);
        $totalDocsCount = Document::count();
        $totalVersionsCount = DocumentVersion::count();

        return view('archive.index', compact(
            'documents',
            'documentTypes',
            'colleges',
            'departments',
            'employees',
            'totalDocsCount',
            'totalVersionsCount',
            'activeUnit'
        ));
    }

    /**
     * أرشفة وثيقة جديدة مباشرة من المستودع الرقمي
     */
    public function storeDirect(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'document_type_id' => 'required|exists:document_types,document_type_id',
            'document_title' => 'required|string|max:250',
            'document_number' => 'nullable|string|max:100',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600',
            'description' => 'nullable|string',
            'is_confidential' => 'nullable|boolean',
        ], [
            'document_file.required' => 'يرجى اختيار ملف الوثيقة.',
            'document_file.max' => 'حجم الملف يتجاوز الحد الأقصى المسموح به (25 ميجابايت).',
            'document_file.mimes' => 'نوع الملف غير مدعوم، الصيغ المقبولة: PDF, JPG, PNG.',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        
        $document = DocumentStorageService::storeEmployeeDocument($employee, $request->file('document_file'), $validated);

        Cache::forget('dashboard_core_metrics');

        return redirect()->route('archive.index')
            ->with('success', 'تمت أرشفة الوثيقة في المستودع الرقمي المحمي بنجاح وحفظ الملف إلكترونياً.');
    }

    /**
     * رفع نسخة أحدث (Version Upgrade v2, v3...) لوثيقة موجودة
     */
    public function uploadNewVersion(Request $request, $docId)
    {
        $document = Document::with('employee')->findOrFail($docId);

        $request->validate([
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600',
        ], [
            'document_file.required' => 'يرجى اختيار ملف الوثيقة المطلوب رفعه.',
            'document_file.max' => 'حجم الملف يتجاوز الحد الأقصى المسموح به (25 ميجابايت).',
            'document_file.mimes' => 'نوع الملف غير مدعوم، الصيغ المقبولة: PDF, JPG, PNG.',
        ]);

        $file = $request->file('document_file');
        $uuid = (string) Str::uuid();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $employee = $document->employee;

        // تعطيل النسخة الحالية وجعلها أرشيف تاريخي
        DocumentVersion::where('document_id', $document->document_id)->update(['is_current' => false]);

        $nextVersionNum = (DocumentVersion::where('document_id', $document->document_id)->max('version_number') ?? 0) + 1;
        $folder = 'archives/' . ($employee?->employee_number ?? $employee?->employee_id ?? 'general');
        $fileName = $uuid . '_v' . $nextVersionNum . '.' . $ext;
        $storageKey = $folder . '/' . $fileName;

        // حفظ الملف في القرص المحمي الخاص disk: local
        $file->storeAs($folder, $fileName, 'local');
        $fullStoredPath = Storage::disk('local')->path($storageKey);
        $sha256 = file_exists($fullStoredPath) ? hash_file('sha256', $fullStoredPath) : hash('sha256', $fileName);

        $newVersion = DocumentVersion::create([
            'document_id' => $document->document_id,
            'version_number' => $nextVersionNum,
            'storage_key' => $storageKey,
            'original_file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: ($ext === 'pdf' ? 'application/pdf' : 'image/' . $ext),
            'file_size_bytes' => $file->getSize(),
            'checksum_sha256' => $sha256,
            'uploaded_at' => now(),
            'is_current' => true,
        ]);

        // توثيق حركة ترقية النسخة
        AuditLogService::log('DOCUMENT_VERSION_UPGRADE', 'documents', $document->document_id, null, [
            'version_number' => $nextVersionNum,
            'file_name' => $file->getClientOriginalName(),
            'checksum' => $sha256,
        ]);

        Cache::forget('dashboard_core_metrics');

        return redirect()->route('archive.preview', $newVersion->document_version_id)
            ->with('success', "تم رفع النسخة الجديدة (الإصدار v{$nextVersionNum}) للوثيقة بنجاح وتحديث البصمة الرقمية.");
    }

    /**
     * معاينة ملف الوثيقة داخل العارض
     */
    public function preview($versionId)
    {
        $version = DocumentVersion::with('document.employee')->findOrFail($versionId);

        $path = null;

        if (!empty($version->storage_key)) {
            if (Storage::disk('local')->exists($version->storage_key)) {
                $path = Storage::disk('local')->path($version->storage_key);
            } elseif (Storage::disk('public')->exists($version->storage_key)) {
                $path = Storage::disk('public')->path($version->storage_key);
            } elseif (Storage::disk('local')->exists('public/' . $version->storage_key)) {
                $path = Storage::disk('local')->path('public/' . $version->storage_key);
            }
        }

        // فحص تلقائي للإصدار الأحدث إذا كان الإصدار الحالي مجرد قيد بانتظار الملف
        if ((!$path || !file_exists($path)) && $version->document) {
            $currVer = $version->document->currentVersion;
            if ($currVer && $currVer->document_version_id !== $version->document_version_id && !empty($currVer->storage_key)) {
                if (Storage::disk('local')->exists($currVer->storage_key)) {
                    $path = Storage::disk('local')->path($currVer->storage_key);
                    $version = $currVer;
                }
            }
        }

        if ($path && file_exists($path)) {
            $mime = $version->mime_type ?: (mime_content_type($path) ?: 'application/pdf');
            $originalName = $version->original_file_name ?: basename($path);

            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . rawurlencode($originalName) . '"; filename*=UTF-8\'\'' . rawurlencode($originalName),
                'X-Frame-Options' => 'SAMEORIGIN',
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        // في حال كان الملف الافتراضي بانتظار الرفع، نعرض صفحة معاينة مؤقتة بدلاً من خطأ
        return view('archive.preview_placeholder', compact('version'));
    }

    /**
     * استعراض أو تنزيل ملف وثيقة
     */
    public function download($versionId)
    {
        $version = DocumentVersion::findOrFail($versionId);

        $path = null;

        if (!empty($version->storage_key)) {
            if (Storage::disk('local')->exists($version->storage_key)) {
                $path = Storage::disk('local')->path($version->storage_key);
            } elseif (Storage::disk('public')->exists($version->storage_key)) {
                $path = Storage::disk('public')->path($version->storage_key);
            } elseif (Storage::disk('local')->exists('public/' . $version->storage_key)) {
                $path = Storage::disk('local')->path('public/' . $version->storage_key);
            }
        }

        // فحص تلقائي للإصدار الأحدث إذا كان الإصدار الحالي مجرد قيد بانتظار الملف
        if ((!$path || !file_exists($path)) && $version->document) {
            $currVer = $version->document->currentVersion;
            if ($currVer && $currVer->document_version_id !== $version->document_version_id && !empty($currVer->storage_key)) {
                if (Storage::disk('local')->exists($currVer->storage_key)) {
                    $path = Storage::disk('local')->path($currVer->storage_key);
                    $version = $currVer;
                }
            }
        }

        if ($path && file_exists($path)) {
            // توثيق حركة التنزيل في سجل الرقابة
            AuditLogService::log('DOCUMENT_DOWNLOAD', 'documents', $version->document_id, null, [
                'version_number' => $version->version_number,
                'file_name' => $version->original_file_name,
            ]);

            return response()->download($path, $version->original_file_name);
        }

        // في حال كان الملف افتراضياً مرحلاً من الإكسل
        return back()->with('error', "الملف الفيزيائي ({$version->original_file_name}) محفوظ في مستودع الأرشيف الورقي بانتظار المسح الضوئي المباشر. البصمة الرقمية المسجلة: " . substr($version->checksum_sha256, 0, 16) . '...');
    }
}
