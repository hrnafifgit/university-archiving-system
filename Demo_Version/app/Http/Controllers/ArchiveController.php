<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Models\Employee;
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
        $query = Document::with([
            'employee.employmentRecords.organizationalUnit',
            'documentType',
            'currentVersion',
            'versions'
        ]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('document_title', 'ilike', "%{$s}%")
                  ->orWhere('document_number', 'ilike', "%{$s}%")
                  ->orWhere('description', 'ilike', "%{$s}%")
                  ->orWhere('issuing_authority', 'ilike', "%{$s}%")
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

        if ($request->filled('status')) {
            if ($request->status === 'digital') {
                $query->whereHas('currentVersion', function ($q) {
                    $q->where('file_size_bytes', '>', 0);
                });
            } elseif ($request->status === 'paper') {
                $query->where(function($sub) {
                    $sub->whereDoesntHave('currentVersion', function ($q) {
                        $q->where('file_size_bytes', '>', 0);
                    })->orWhereDoesntHave('versions');
                });
            } elseif ($request->status === 'trash') {
                $query->onlyTrashed();
            }
        }

        $activeUnit = null;
        $collegeId = $request->college_id ?? $request->faculty_id;
        $unitId = $request->unit_id ?? $request->dept_id;

        if (!empty($unitId)) {
            $query->whereHas('employee.employmentRecords', function ($q) use ($unitId) {
                $q->where('organizational_unit_id', $unitId);
            });
            $activeUnit = \App\Models\OrganizationalUnit::find($unitId);
        } elseif (!empty($collegeId)) {
            $query->whereHas('employee.employmentRecords.organizationalUnit', function ($q) use ($collegeId) {
                $q->where('organizational_unit_id', $collegeId)
                  ->orWhere('parent_unit_id', $collegeId);
            });
            $activeUnit = \App\Models\OrganizationalUnit::find($collegeId);
        }

        $perPage = $request->input('per_page', 24);
        $documents = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        $documentTypes = DocumentType::orderBy('type_name_ar')->get();
        $employees = Employee::orderBy('full_name_ar')->limit(150)->get(['employee_id', 'full_name_ar', 'employee_number']);
        
        $totalDocsCount = Document::count();
        $digitalDocsCount = Document::whereHas('currentVersion', function ($q) {
            $q->where('file_size_bytes', '>', 0);
        })->count();
        $paperDocsCount = max(0, $totalDocsCount - $digitalDocsCount);
        $confidentialDocsCount = Document::where('is_confidential', true)->count();
        $trashedDocsCount = Document::onlyTrashed()->count();
        $totalVersionsCount = DocumentVersion::count();
        $totalStorageBytes = DocumentVersion::sum('file_size_bytes') ?: 0;

        // جلب الكليات والأقسام لفلترة الأرشيف
        $colleges = \App\Models\OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'administration'])->orderBy('unit_name_ar')->get();
        $departments = \App\Models\OrganizationalUnit::where('unit_type', 'department')->orderBy('unit_name_ar')->get();

        return view('archive.index', compact(
            'documents',
            'documentTypes',
            'employees',
            'totalDocsCount',
            'digitalDocsCount',
            'paperDocsCount',
            'confidentialDocsCount',
            'trashedDocsCount',
            'totalVersionsCount',
            'totalStorageBytes',
            'activeUnit',
            'colleges',
            'departments'
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

    /**
     * تصدير وثائق الأرشيف المفلترة إلى Excel / CSV
     */
    public function exportExcel(Request $request)
    {
        $query = Document::with([
            'employee.employmentRecords.organizationalUnit',
            'documentType',
            'currentVersion'
        ]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('document_title', 'ilike', "%{$s}%")
                  ->orWhere('document_number', 'ilike', "%{$s}%")
                  ->orWhere('description', 'ilike', "%{$s}%")
                  ->orWhere('issuing_authority', 'ilike', "%{$s}%")
                  ->orWhereHas('employee', function ($eq) use ($s) {
                      $eq->where('full_name_ar', 'ilike', "%{$s}%")
                         ->orWhere('employee_number', 'ilike', "%{$s}%");
                  });
            });
        }

        if ($request->filled('type_id')) {
            $query->where('document_type_id', $request->type_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'digital') {
                $query->whereHas('currentVersion', fn($q) => $q->where('file_size_bytes', '>', 0));
            } elseif ($request->status === 'paper') {
                $query->whereDoesntHave('currentVersion', fn($q) => $q->where('file_size_bytes', '>', 0));
            } elseif ($request->status === 'trash') {
                $query->onlyTrashed();
            }
        }

        $collegeId = $request->college_id ?? $request->faculty_id;
        $unitId = $request->unit_id ?? $request->dept_id;

        if (!empty($unitId)) {
            $query->whereHas('employee.employmentRecords', function ($q) use ($unitId) {
                $q->where('organizational_unit_id', $unitId);
            });
        } elseif (!empty($collegeId)) {
            $query->whereHas('employee.employmentRecords.organizationalUnit', function ($q) use ($collegeId) {
                $q->where('organizational_unit_id', $collegeId)
                  ->orWhere('parent_unit_id', $collegeId);
            });
        }

        $docs = $query->orderByDesc('created_at')->get();
        $fileName = 'سجل_وثائق_الأرشيف_' . date('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($docs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'رقم القيد',
                'عنوان وموضوع الوثيقة',
                'التصنيف',
                'الكادر المعني',
                'الرقم الوظيفي',
                'الكلية / القسم',
                'الحالة الرقمية',
                'حجم الملف (KB)',
                'جهة الإصدار',
                'تاريخ الأرشفة',
                'البصمة الرقمية (SHA-256)'
            ]);

            foreach ($docs as $d) {
                $v = $d->currentVersion;
                $hasFile = $v && $v->file_size_bytes > 0;
                $emp = $d->employee;
                $unit = $emp?->employmentRecords?->first()?->organizationalUnit?->unit_name_ar ?? 'جامعة إب';

                fputcsv($handle, [
                    $d->document_number ?? '—',
                    $d->document_title,
                    $d->documentType?->type_name_ar ?? 'عام',
                    $emp?->full_name_ar ?? 'سجل عام',
                    $emp?->employee_number ?? '—',
                    $unit,
                    $hasFile ? 'مؤرشف رقمياً' : 'قيد ورقي',
                    $hasFile ? number_format($v->file_size_bytes / 1024, 1) : '0',
                    $d->issuing_authority ?? 'جامعة إب',
                    $d->created_at ? $d->created_at->format('Y-m-d') : '—',
                    $v?->checksum_sha256 ?? '—',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * حذف وثيقة أرشيفية (نقل إلى سلة المحذوفات Soft Delete)
     */
    public function destroy($id)
    {
        $doc = Document::findOrFail($id);
        $title = $doc->document_title;
        $doc->delete();

        AuditLogService::log('DOCUMENT_DELETE', 'documents', $id, null, ['title' => $title]);

        return back()->with('success', "تم نقل الوثيقة ({$title}) إلى سلة المحذوفات بنجاح!");
    }

    /**
     * استعادة وثيقة من سلة المحذوفات
     */
    public function restore($id)
    {
        $doc = Document::onlyTrashed()->findOrFail($id);
        $doc->restore();

        AuditLogService::log('DOCUMENT_RESTORE', 'documents', $id, null, ['title' => $doc->document_title]);

        return back()->with('success', "تمت استعادة الوثيقة ({$doc->document_title}) بنجاح إلى المستودع الرقمي!");
    }

    /**
     * الحذف النهائي للوثيقة من قاعدة البيانات
     */
    public function forceDelete($id)
    {
        $doc = Document::onlyTrashed()->findOrFail($id);
        $title = $doc->document_title;
        $doc->forceDelete();

        AuditLogService::log('DOCUMENT_FORCE_DELETE', 'documents', $id, null, ['title' => $title]);

        return back()->with('success', "تم حذف الوثيقة ({$title}) نهائياً من النظام.");
    }

    /**
     * جلب سجل الرقابة والتدقيق للوثيقة (Audit Trail API)
     */
    public function auditTrail($id)
    {
        $logs = \App\Models\AuditLog::with('user')
            ->where('entity_name', 'documents')
            ->where('entity_id', $id)
            ->orderByDesc('occurred_at')
            ->limit(15)
            ->get()
            ->map(function ($l) {
                $actionNames = [
                    'DOCUMENT_UPLOAD' => 'أرشفة ورفع ملف جديد',
                    'DOCUMENT_PREVIEW' => 'معاينة إلكترونية للملف',
                    'DOCUMENT_DOWNLOAD' => 'تنزيل وتحميل الملف',
                    'DOCUMENT_VERSION_UPGRADE' => 'ترقية إصدار رقمي جديد',
                    'DOCUMENT_DELETE' => 'نقل إلى سلة المحذوفات',
                    'DOCUMENT_RESTORE' => 'استعادة من سلة المحذوفات',
                ];

                return [
                    'action' => $actionNames[$l->action_code] ?? $l->action_code,
                    'user'   => $l->user?->username ?? 'مسؤول النظام',
                    'date'   => $l->occurred_at ? $l->occurred_at->format('Y-m-d H:i:s') : '—',
                    'ip'     => $l->ip_address ?? '127.0.0.1',
                    'meta'   => $l->new_values
                ];
            });

        return response()->json($logs);
    }

    /**
     * الأرشفة الجماعية بالسحب والإفلات (Bulk Upload)
     */
    public function bulkUpload(Request $request, DocumentStorageService $storageService)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'document_type_id' => 'required|exists:document_types,document_type_id',
            'files' => 'required|array|min:1|max:20',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:25600',
        ], [
            'files.required' => 'يرجى تحديد ملف واحد على الأقل.',
            'files.max' => 'يمكن رفع 20 ملفاً كحد أقصى في الدفعة الواحدة.',
            'files.*.mimes' => 'يجب أن تكون الملفات بصيغة PDF أو صور.',
            'files.*.max' => 'الحد الأقصى لحجم كل ملف هو 25 ميجابايت.',
        ]);

        $uploadedCount = 0;
        $employeeId = $request->employee_id;
        $typeId = $request->document_type_id;

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $title = pathinfo($originalName, PATHINFO_FILENAME);
            $mime = $file->getMimeType() ?: 'application/pdf';
            $size = $file->getSize();

            $doc = Document::create([
                'document_id' => (string) Str::uuid(),
                'employee_id' => $employeeId,
                'document_type_id' => $typeId,
                'document_title' => $title,
                'document_number' => 'BLK-' . strtoupper(Str::random(6)),
                'document_date' => now()->toDateString(),
                'issuing_authority' => 'جامعة إب',
                'is_confidential' => $request->boolean('is_confidential', false),
            ]);

            $storageKey = $storageService->store($file, 'documents/' . $doc->document_id);
            $fullStoredPath = Storage::disk('local')->path($storageKey);
            $sha256 = file_exists($fullStoredPath) ? hash_file('sha256', $fullStoredPath) : hash('sha256', $originalName);

            DocumentVersion::create([
                'document_version_id' => (string) Str::uuid(),
                'document_id' => $doc->document_id,
                'version_number' => 1,
                'storage_key' => $storageKey,
                'original_file_name' => $originalName,
                'mime_type' => $mime,
                'file_size_bytes' => $size,
                'checksum_sha256' => $sha256,
                'uploaded_at' => now(),
                'is_current' => true,
            ]);

            AuditLogService::log('DOCUMENT_UPLOAD', 'documents', $doc->document_id, null, [
                'bulk' => true,
                'file_name' => $originalName,
                'file_size' => $size,
            ]);

            $uploadedCount++;
        }

        return back()->with('success', "تم بنجاح أرشفة وتخزين ({$uploadedCount}) ملفات دفعة واحدة في المستودع الرقمي!");
    }
}
