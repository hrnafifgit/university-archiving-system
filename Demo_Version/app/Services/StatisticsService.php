<?php

namespace App\Services;

use App\Models\AcademicRank;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\EmployeeDecision;
use App\Models\EmployeeQualification;
use App\Models\EmploymentRecord;
use App\Models\EmploymentStatus;
use App\Models\OrganizationalUnit;
use App\Models\QualificationLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * حساب واستخراج كافة مؤشرات لوحة الإحصائيات المركزية الشاملة
     * تدعم التصفية حسب الكلية، الرتبة العلمية، والنطاق الزمني
     */
    public function getDashboardMetrics(array $filters = [], bool $forceRefresh = false): array
    {
        $hasCustomFilters = !empty(array_filter($filters, fn($v) => !empty($v) && $v !== 'all'));
        $cacheKey = $hasCustomFilters 
            ? 'dashboard_unified_metrics_' . md5(json_encode($filters))
            : 'dashboard_core_metrics';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
            Cache::forget('dashboard_core_metrics');
        }

        return Cache::remember($cacheKey, 180, function () use ($filters) {
            return $this->computeMetrics($filters);
        });
    }

    /**
     * المعالجة الحسابية المجمعة من واقع قاعدة بيانات PostgreSQL
     */
    protected function computeMetrics(array $filters): array
    {
        $collegeId = $filters['college_id'] ?? null;
        $rankId = $filters['rank_id'] ?? null;
        $period = $filters['period'] ?? 'all';

        // ----------------------------------------------------
        // 1. المحور الأول: الكادر البشري والأكاديمي
        // ----------------------------------------------------
        $empQuery = DB::table('employees as e')
            ->join('employment_records as er', function ($j) {
                $j->on('e.employee_id', '=', 'er.employee_id')
                  ->where('er.is_primary', '=', true)
                  ->whereNull('er.end_date');
            })
            ->join('employee_categories as ec', 'er.employee_category_id', '=', 'ec.employee_category_id')
            ->join('employment_statuses as es', 'er.employment_status_id', '=', 'es.employment_status_id')
            ->where('e.is_active_record', true);

        if ($collegeId) {
            $empQuery->where(function ($q) use ($collegeId) {
                $q->where('er.organizational_unit_id', $collegeId)
                  ->orWhereIn('er.organizational_unit_id', function ($sub) use ($collegeId) {
                      $sub->select('organizational_unit_id')
                          ->from('organizational_units')
                          ->where('parent_unit_id', $collegeId);
                  });
            });
        }

        if ($rankId) {
            $empQuery->where('er.academic_rank_id', $rankId);
        }

        $totalEmployees = (clone $empQuery)->count();
        $academicCount = (clone $empQuery)->where('ec.category_code', 'ACADEMIC')->count();
        $adminCount = (clone $empQuery)->where('ec.category_code', 'ADMIN')->count();
        $contractCount = (clone $empQuery)->where('ec.category_code', 'CONTRACTOR')->count();
        $activeDutyCount = (clone $empQuery)->where('es.is_active_status', true)->count();
        $inactiveDutyCount = max(0, $totalEmployees - $activeDutyCount);

        // توزيع النوع الاجتماعي
        $femaleCount = (clone $empQuery)->where(function ($q) {
            $q->where('e.gender', 'female')
              ->orWhere('e.gender', 'F')
              ->orWhere('e.gender', 'أنثى')
              ->orWhere('e.gender', 'انثى');
        })->count();
        $maleCount = max(0, $totalEmployees - $femaleCount);

        // توزيع الرتب العلمية
        $ranksDistribution = DB::table('academic_ranks as ar')
            ->leftJoin('employment_records as er', function ($j) use ($collegeId) {
                $j->on('ar.academic_rank_id', '=', 'er.academic_rank_id')
                  ->where('er.is_primary', '=', true)
                  ->whereNull('er.end_date');
                if ($collegeId) {
                    $j->where(function ($q) use ($collegeId) {
                        $q->where('er.organizational_unit_id', $collegeId)
                          ->orWhereIn('er.organizational_unit_id', function ($sub) use ($collegeId) {
                              $sub->select('organizational_unit_id')
                                  ->from('organizational_units')
                                  ->where('parent_unit_id', $collegeId);
                          });
                    });
                }
            })
            ->leftJoin('employees as e', function ($j) {
                $j->on('er.employee_id', '=', 'e.employee_id')
                  ->where('e.is_active_record', true);
            })
            ->select(
                'ar.academic_rank_id',
                'ar.rank_name_ar',
                'ar.rank_order',
                DB::raw('COUNT(e.employee_id) as total')
            )
            ->groupBy('ar.academic_rank_id', 'ar.rank_name_ar', 'ar.rank_order')
            ->orderBy('ar.rank_order')
            ->get();

        // توزيع المؤهلات العلمية (دكتوراه، ماجستير، بكالوريوس...)
        $qualificationsDistribution = DB::table('qualification_levels as ql')
            ->leftJoin('employee_qualifications as eq', 'ql.qualification_level_id', '=', 'eq.qualification_level_id')
            ->leftJoin('employees as e', function ($j) {
                $j->on('eq.employee_id', '=', 'e.employee_id')
                  ->where('e.is_active_record', true);
            })
            ->select(
                'ql.qualification_level_id',
                'ql.level_name_ar',
                'ql.level_order',
                DB::raw('COUNT(DISTINCT e.employee_id) as total')
            )
            ->groupBy('ql.qualification_level_id', 'ql.level_name_ar', 'ql.level_order')
            ->orderBy('ql.level_order')
            ->get();

        // ----------------------------------------------------
        // 2. المحور الثاني: الأرشيف والمستودع الرقمي
        // ----------------------------------------------------
        $docQuery = DB::table('documents as d');
        if ($collegeId) {
            $docQuery->join('employment_records as er', function ($j) use ($collegeId) {
                $j->on('d.employee_id', '=', 'er.employee_id')
                  ->where('er.is_primary', true)
                  ->whereNull('er.end_date');
                $j->where(function ($q) use ($collegeId) {
                    $q->where('er.organizational_unit_id', $collegeId)
                      ->orWhereIn('er.organizational_unit_id', function ($sub) use ($collegeId) {
                          $sub->select('organizational_unit_id')
                              ->from('organizational_units')
                              ->where('parent_unit_id', $collegeId);
                      });
                });
            });
        }

        $totalDocuments = (clone $docQuery)->count();

        // الوثائق حسب النوع
        $documentsByType = DB::table('document_types as dt')
            ->leftJoin('documents as d', 'dt.document_type_id', '=', 'd.document_type_id')
            ->select(
                'dt.document_type_id',
                'dt.type_name_ar',
                DB::raw('COUNT(d.document_id) as total')
            )
            ->groupBy('dt.document_type_id', 'dt.type_name_ar')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // سعة التخزين من واقع نسخ الملفات الفعلية
        $storageStats = DB::table('document_versions')
            ->select(
                DB::raw('COALESCE(SUM(file_size_bytes), 0) as total_bytes'),
                DB::raw('COALESCE(AVG(file_size_bytes), 0) as avg_bytes'),
                DB::raw('COUNT(document_version_id) as total_versions')
            )
            ->first();

        $totalBytes = (float)($storageStats->total_bytes ?? 0);
        $totalStorageMB = round($totalBytes / (1024 * 1024), 2);
        $totalStorageGB = round($totalBytes / (1024 * 1024 * 1024), 3);
        $avgFileSizeKB = round(((float)($storageStats->avg_bytes ?? 0)) / 1024, 1);

        // توزيع صيغ الملفات (MIME Types)
        $storageByMime = DB::table('document_versions')
            ->select(
                DB::raw("COALESCE(NULLIF(mime_type, ''), 'application/octet-stream') as mime"),
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(file_size_bytes), 0) as total_bytes')
            )
            ->groupBy('mime')
            ->orderByDesc('total_bytes')
            ->get()
            ->map(function ($row) {
                $row->size_mb = round(((float)$row->total_bytes) / (1024 * 1024), 2);
                $row->label = str_contains($row->mime, 'pdf') ? 'ملفات PDF' : (str_contains($row->mime, 'image') ? 'صور رقمية' : 'مستندات أخرى');
                return $row;
            });

        // وثائق منتهية الصلاحية
        $expiredDocumentsCount = DB::table('documents')
            ->whereNotNull('retention_until')
            ->where('retention_until', '<', now()->toDateString())
            ->count();

        // وثائق بدون مرفق ملفات
        $documentsWithoutFilesCount = DB::table('documents as d')
            ->leftJoin('document_versions as dv', 'd.document_id', '=', 'dv.document_id')
            ->whereNull('dv.document_version_id')
            ->count();

        // كادر يمتلك وثائق مؤرشفة
        $employeesWithDocsCount = DB::table('employees as e')
            ->where('e.is_active_record', true)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('documents as d')
                  ->whereColumn('d.employee_id', 'e.employee_id');
            })
            ->count();

        // نسبة اكتمال أرشفة الكادر (Archiving Completeness)
        $archiveCompletenessRate = $totalEmployees > 0 
            ? round(($employeesWithDocsCount / $totalEmployees) * 100, 1) 
            : 0;

        // ----------------------------------------------------
        // 3. المحور الثالث: جودة البيانات واكتمال السجلات (DAMA-DMBOK)
        // ----------------------------------------------------
        $employeesWithoutDocsCount = max(0, $totalEmployees - $employeesWithDocsCount);

        // كادر بدون مؤهل علمي مسجل
        $employeesWithoutQualCount = DB::table('employees as e')
            ->where('e.is_active_record', true)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('employee_qualifications as eq')
                  ->whereColumn('eq.employee_id', 'e.employee_id');
            })
            ->count();

        // كادر بدون رقم وطني
        $missingNationalIdCount = DB::table('employees')
            ->where('is_active_record', true)
            ->where(function ($q) {
                $q->whereNull('national_id')->orWhere('national_id', '');
            })
            ->count();

        // كادر بدون رقم هاتف
        $missingPhoneCount = DB::table('employees')
            ->where('is_active_record', true)
            ->where(function ($q) {
                $q->whereNull('phone_number')->orWhere('phone_number', '');
            })
            ->count();

        // مؤشر جودة البيانات الشامل (Data Quality Score)
        // يعتمد على 4 معايير: وجود الرقم الوطني، الهاتف، المؤهل، والوثيقة
        $qualityScore = 100;
        if ($totalEmployees > 0) {
            $penalties = 0;
            $penalties += ($employeesWithoutDocsCount / $totalEmployees) * 35; // 35% للأرشفة
            $penalties += ($employeesWithoutQualCount / $totalEmployees) * 25; // 25% للمؤهل
            $penalties += ($missingNationalIdCount / $totalEmployees) * 25;   // 25% للرقم الوطني
            $penalties += ($missingPhoneCount / $totalEmployees) * 15;        // 15% للاتصال
            $qualityScore = max(0, round(100 - $penalties, 1));
        }

        // قائمة عينات من السجلات غير المكتملة للتنبيه السريع (Actionable Gaps)
        $incompleteEmployees = DB::table('employees as e')
            ->join('employment_records as er', function ($j) {
                $j->on('e.employee_id', '=', 'er.employee_id')
                  ->where('er.is_primary', true)
                  ->whereNull('er.end_date');
            })
            ->join('organizational_units as ou', 'er.organizational_unit_id', '=', 'ou.organizational_unit_id')
            ->leftJoin('academic_ranks as ar', 'er.academic_rank_id', '=', 'ar.academic_rank_id')
            ->where('e.is_active_record', true)
            ->where(function ($q) {
                $q->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('documents')->whereColumn('documents.employee_id', 'e.employee_id');
                })->orWhereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))->from('employee_qualifications')->whereColumn('employee_qualifications.employee_id', 'e.employee_id');
                })->orWhereNull('e.national_id')->orWhere('e.national_id', '');
            })
            ->select(
                'e.employee_id',
                'e.employee_number',
                'e.full_name_ar',
                'ou.unit_name_ar as college_name',
                'ar.rank_name_ar',
                'e.national_id',
                'e.phone_number',
                DB::raw('(SELECT COUNT(*) FROM documents WHERE documents.employee_id = e.employee_id) as doc_count'),
                DB::raw('(SELECT COUNT(*) FROM employee_qualifications WHERE employee_qualifications.employee_id = e.employee_id) as qual_count')
            )
            ->limit(10)
            ->get();

        // ----------------------------------------------------
        // 4. المحور الرابع: الكليات وسعة الهيكل التنظيمي
        // ----------------------------------------------------
        $collegesStats = DB::table('organizational_units as ou')
            ->whereIn('ou.unit_type', ['faculty', 'college', 'center', 'institute'])
            ->leftJoin('organizational_units as children', 'ou.organizational_unit_id', '=', 'children.parent_unit_id')
            ->leftJoin('employment_records as er', function ($j) {
                $j->on(function ($cond) {
                    $cond->on('ou.organizational_unit_id', '=', 'er.organizational_unit_id')
                         ->orOn('children.organizational_unit_id', '=', 'er.organizational_unit_id');
                })
                ->where('er.is_primary', true)
                ->whereNull('er.end_date');
            })
            ->leftJoin('employees as e', function ($j) {
                $j->on('er.employee_id', '=', 'e.employee_id')
                  ->where('e.is_active_record', true);
            })
            ->select(
                'ou.organizational_unit_id',
                'ou.unit_name_ar',
                DB::raw('COUNT(DISTINCT children.organizational_unit_id) as departments_count'),
                DB::raw('COUNT(DISTINCT e.employee_id) as staff_count')
            )
            ->groupBy('ou.organizational_unit_id', 'ou.unit_name_ar')
            ->orderByDesc('staff_count')
            ->get();

        // ----------------------------------------------------
        // 5. المحور الخامس: القرارات الإدارية والحركات الوظيفية
        // ----------------------------------------------------
        $totalDecisions = DB::table('employee_decisions')->count();

        $decisionsByStatus = DB::table('decision_statuses as ds')
            ->leftJoin('employee_decisions as ed', 'ds.decision_status_id', '=', 'ed.decision_status_id')
            ->select('ds.decision_status_id', 'ds.status_name_ar', DB::raw('COUNT(ed.decision_id) as total'))
            ->groupBy('ds.decision_status_id', 'ds.status_name_ar')
            ->get();

        $decisionsByType = DB::table('decision_types as dt')
            ->leftJoin('employee_decisions as ed', 'dt.decision_type_id', '=', 'ed.decision_type_id')
            ->select('dt.decision_type_id', 'dt.type_name_ar', DB::raw('COUNT(ed.decision_id) as total'))
            ->groupBy('dt.decision_type_id', 'dt.type_name_ar')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // ----------------------------------------------------
        // 6. المحور السادس: سجل الأنشطة والرقابة والعمليات اليومية (Audit & Activity)
        // ----------------------------------------------------
        $totalAuditLogs = DB::table('audit_logs')->count();
        $successfulLogins = DB::table('audit_logs')->where('action_code', 'LOGIN')->count();
        $failedLogins = DB::table('audit_logs')->where('action_code', 'LOGIN_FAILED')->count();
        $documentsUploadedLogs = DB::table('audit_logs')->where('action_code', 'DOCUMENT_UPLOAD')->count();

        // أحدث الأنشطة المنفذة في النظام
        $recentAuditLogs = DB::table('audit_logs as al')
            ->leftJoin('app_users as u', 'al.user_id', '=', 'u.user_id')
            ->leftJoin('employees as e', 'u.employee_id', '=', 'e.employee_id')
            ->select(
                'al.audit_log_id',
                'al.action_code',
                'al.entity_name',
                'al.occurred_at',
                'al.ip_address',
                'u.username',
                DB::raw("COALESCE(e.full_name_ar, u.username, 'مستخدم النظام') as user_full_name")
            )
            ->orderByDesc('al.occurred_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $log->action_label = match ($log->action_code) {
                    'DOCUMENT_UPLOAD' => 'رفع وثيقة جديدة',
                    'DOCUMENT_DELETE' => 'حذف وثيقة',
                    'DOCUMENT_VERSION_UPGRADE' => 'تحديث إصدار وثيقة',
                    'LOGIN' => 'تسجيل دخول ناجح',
                    'LOGIN_FAILED' => 'محاولة دخول فاشلة',
                    'LOGOUT' => 'تسجيل خروج',
                    'EMPLOYEE_CREATE' => 'إضافة كادر جديد',
                    'EMPLOYEE_UPDATE' => 'تحديث بيانات كادر',
                    'DECISION_CREATE' => 'إصدار قرار إداري',
                    default => $log->action_code,
                };
                return $log;
            });

        // ----------------------------------------------------
        // 7. أحدث السجلات المضافة في النظام
        // ----------------------------------------------------
        $recentEmployees = Employee::with([
            'currentEmployment.organizationalUnit',
            'currentEmployment.academicRank',
            'currentEmployment.employmentStatus'
        ])
        ->where('is_active_record', true)
        ->latest('created_at')
        ->limit(8)
        ->get();

        // ----------------------------------------------------
        // 8. مصفوفات الإحصائيات والمخططات البيانية الرسمية الستة (Official Matrices)
        // ----------------------------------------------------
        $excelMatrices = $this->computeOfficialMatrices($collegeId);

        // قائمة الكليات لتغذية الفلاتر المنسدلة
        $allColleges = OrganizationalUnit::whereIn('unit_type', ['faculty', 'college', 'center', 'institute'])
            ->orderBy('unit_name_ar')
            ->get(['organizational_unit_id', 'unit_name_ar']);

        // قائمة الرتب لتغذية الفلاتر
        $allRanks = AcademicRank::orderBy('rank_order')->get(['academic_rank_id', 'rank_name_ar']);

        // نسخة متوافقة مع التسميات القديمة
        $collegesDistribution = $collegesStats;

        return compact(
            'filters',
            'totalEmployees',
            'academicCount',
            'adminCount',
            'contractCount',
            'activeDutyCount',
            'inactiveDutyCount',
            'maleCount',
            'femaleCount',
            'ranksDistribution',
            'qualificationsDistribution',
            'totalDocuments',
            'documentsByType',
            'totalBytes',
            'totalStorageMB',
            'totalStorageGB',
            'avgFileSizeKB',
            'storageByMime',
            'expiredDocumentsCount',
            'documentsWithoutFilesCount',
            'employeesWithDocsCount',
            'archiveCompletenessRate',
            'employeesWithoutDocsCount',
            'employeesWithoutQualCount',
            'missingNationalIdCount',
            'missingPhoneCount',
            'qualityScore',
            'incompleteEmployees',
            'collegesStats',
            'collegesDistribution',
            'totalDecisions',
            'decisionsByStatus',
            'decisionsByType',
            'totalAuditLogs',
            'successfulLogins',
            'failedLogins',
            'documentsUploadedLogs',
            'recentAuditLogs',
            'recentEmployees',
            'excelMatrices',
            'allColleges',
            'allRanks'
        );
    }

    /**
     * استخراج المصفوفات الإحصائية الستة ديناميكياً بالكامل من واقع جداول قاعدة البيانات
     * بحيث تنعكس أي إضافة أو تعديل في الرتب، المؤهلات، أو الحالات فورياً وتلقائياً
     */
    public function computeOfficialMatrices(?string $collegeId = null): array
    {
        $baseQuery = DB::table('employees as e')
            ->join('employment_records as er', function ($j) {
                $j->on('e.employee_id', '=', 'er.employee_id')
                  ->where('er.is_primary', true)
                  ->whereNull('er.end_date');
            })
            ->where('e.is_active_record', true);

        if ($collegeId) {
            $baseQuery->where(function ($q) use ($collegeId) {
                $q->where('er.organizational_unit_id', $collegeId)
                  ->orWhereIn('er.organizational_unit_id', function ($sub) use ($collegeId) {
                      $sub->select('organizational_unit_id')
                          ->from('organizational_units')
                          ->where('parent_unit_id', $collegeId);
                  });
            });
        }

        // 1. إحصائية الجنس ديناميكياً من قاعدة البيانات
        $genderRows = (clone $baseQuery)
            ->select(
                DB::raw("CASE 
                    WHEN e.gender IN ('M', 'male', 'ذكر') THEN 'ذكر'
                    WHEN e.gender IN ('F', 'female', 'أنثى', 'انثى') THEN 'أنثى'
                    ELSE 'غير محدد'
                END as name"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('name')
            ->orderByDesc('count')
            ->get()
            ->map(fn($g) => ['name' => $g->name, 'count' => (int)$g->count])
            ->toArray();

        $matrixGender = [
            'chart_title' => 'احصائية عدد الذكور والاناث',
            'header_label' => 'الجنس',
            'header_bg' => 'bg-[#244c6b]',
            'chart_id' => 'chartGender',
            'rows' => $genderRows,
            'total' => array_sum(array_column($genderRows, 'count')),
            'labels' => array_column($genderRows, 'name'),
            'data' => array_column($genderRows, 'count'),
            'colors' => ['#4682b4', '#d9534f', '#a0aec0'],
        ];

        // 2. إحصائية الدرجة الحالية ديناميكياً من جدول academic_ranks
        $rankQuery = DB::table('academic_ranks as ar')
            ->leftJoin('employment_records as er', function ($j) use ($collegeId) {
                $j->on('ar.academic_rank_id', '=', 'er.academic_rank_id')
                  ->where('er.is_primary', true)
                  ->whereNull('er.end_date');
                if ($collegeId) {
                    $j->where(function ($q) use ($collegeId) {
                        $q->where('er.organizational_unit_id', $collegeId)
                          ->orWhereIn('er.organizational_unit_id', function ($sub) use ($collegeId) {
                              $sub->select('organizational_unit_id')
                                  ->from('organizational_units')
                                  ->where('parent_unit_id', $collegeId);
                          });
                    });
                }
            })
            ->leftJoin('employees as e', function ($j) {
                $j->on('er.employee_id', '=', 'e.employee_id')
                  ->where('e.is_active_record', true);
            })
            ->select(
                'ar.rank_name_ar as name',
                DB::raw('COUNT(e.employee_id) as count')
            )
            ->groupBy('ar.academic_rank_id', 'ar.rank_name_ar', 'ar.rank_order')
            ->orderBy('ar.rank_order')
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'count' => (int)$r->count])
            ->toArray();

        $matrixRanks = [
            'chart_title' => 'احصائية بحسب الدرجة الحالية',
            'header_label' => 'الدرجة الحالية',
            'header_bg' => 'bg-[#1c6b82]',
            'chart_id' => 'chartRanks',
            'rows' => $rankQuery,
            'total' => array_sum(array_column($rankQuery, 'count')),
            'labels' => array_column($rankQuery, 'name'),
            'data' => array_column($rankQuery, 'count'),
            'color' => '#d0533f',
        ];

        // 3. إحصائية الموقف الوظيفي ديناميكياً من حقل appointment_basis
        $posQuery = (clone $baseQuery)
            ->select(
                DB::raw("COALESCE(NULLIF(TRIM(er.appointment_basis), ''), 'غير محدد') as name"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('name')
            ->orderByDesc('count')
            ->get()
            ->map(fn($p) => ['name' => $p->name, 'count' => (int)$p->count])
            ->toArray();

        $matrixPosition = [
            'chart_title' => 'احصائية بحسب الموقف الوظيفي',
            'header_label' => 'الموقف الوظيفي',
            'header_bg' => 'bg-[#5c467d]',
            'chart_id' => 'chartPosition',
            'rows' => $posQuery,
            'total' => array_sum(array_column($posQuery, 'count')),
            'labels' => array_column($posQuery, 'name'),
            'data' => array_column($posQuery, 'count'),
            'color' => '#8770a9',
        ];

        // 4. إحصائية الحالة بكشف الراتب ديناميكياً من حقل payroll_status
        $payQuery = (clone $baseQuery)
            ->select(
                DB::raw("COALESCE(NULLIF(TRIM(er.payroll_status), ''), 'غير محدد') as name"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('name')
            ->orderByDesc('count')
            ->get()
            ->map(fn($p) => ['name' => $p->name, 'count' => (int)$p->count])
            ->toArray();

        $matrixPayroll = [
            'chart_title' => 'احصائية بحسب الحالة ك راتب',
            'header_label' => 'الحالة بكشف الراتب',
            'header_bg' => 'bg-[#4d6637]',
            'chart_id' => 'chartPayroll',
            'rows' => $payQuery,
            'total' => array_sum(array_column($payQuery, 'count')),
            'labels' => array_column($payQuery, 'name'),
            'data' => array_column($payQuery, 'count'),
            'color' => '#cc4c39',
        ];

        // 5. إحصائية المؤهل العلمي ديناميكياً من جدول qualification_levels
        $qualQuery = DB::table('qualification_levels as ql')
            ->leftJoin('employee_qualifications as eq', 'ql.qualification_level_id', '=', 'eq.qualification_level_id')
            ->leftJoin('employees as e', function ($j) {
                $j->on('eq.employee_id', '=', 'e.employee_id')
                  ->where('e.is_active_record', true);
            })
            ->select(
                'ql.level_name_ar as name',
                DB::raw('COUNT(DISTINCT e.employee_id) as count')
            )
            ->groupBy('ql.qualification_level_id', 'ql.level_name_ar', 'ql.level_order')
            ->orderBy('ql.level_order')
            ->get()
            ->map(fn($q) => ['name' => $q->name, 'count' => (int)$q->count])
            ->toArray();

        $matrixQualifications = [
            'chart_title' => 'احصائية بحسب المؤهل العلمي',
            'header_label' => 'المؤهل العلمي',
            'header_bg' => 'bg-[#6b582b]',
            'chart_id' => 'chartQualifications',
            'rows' => $qualQuery,
            'total' => array_sum(array_column($qualQuery, 'count')),
            'labels' => array_column($qualQuery, 'name'),
            'data' => array_column($qualQuery, 'count'),
            'color' => '#b82e24',
        ];

        // 6. إحصائية الحالة شؤون أكاديمية ديناميكياً من جدول employment_statuses
        $acadQuery = DB::table('employment_statuses as es')
            ->leftJoin('employment_records as er', function ($j) use ($collegeId) {
                $j->on('es.employment_status_id', '=', 'er.employment_status_id')
                  ->where('er.is_primary', true)
                  ->whereNull('er.end_date');
                if ($collegeId) {
                    $j->where(function ($q) use ($collegeId) {
                        $q->where('er.organizational_unit_id', $collegeId)
                          ->orWhereIn('er.organizational_unit_id', function ($sub) use ($collegeId) {
                              $sub->select('organizational_unit_id')
                                  ->from('organizational_units')
                                  ->where('parent_unit_id', $collegeId);
                          });
                    });
                }
            })
            ->leftJoin('employees as e', function ($j) {
                $j->on('er.employee_id', '=', 'e.employee_id')
                  ->where('e.is_active_record', true);
            })
            ->select(
                'es.status_name_ar as name',
                DB::raw('COUNT(e.employee_id) as count')
            )
            ->groupBy('es.employment_status_id', 'es.status_name_ar')
            ->orderByDesc('count')
            ->get()
            ->map(fn($s) => ['name' => $s->name, 'count' => (int)$s->count])
            ->toArray();

        $matrixAcademicStatus = [
            'chart_title' => 'احصائية بحسب الحالة ش اكاديمية',
            'header_label' => 'الحالة شؤون اكاديمية',
            'header_bg' => 'bg-[#783636]',
            'chart_id' => 'chartAcademicStatus',
            'rows' => $acadQuery,
            'total' => array_sum(array_column($acadQuery, 'count')),
            'labels' => array_column($acadQuery, 'name'),
            'data' => array_column($acadQuery, 'count'),
            'color' => '#d0533f',
        ];

        return [
            'gender' => $matrixGender,
            'ranks' => $matrixRanks,
            'position' => $matrixPosition,
            'payroll' => $matrixPayroll,
            'qualifications' => $matrixQualifications,
            'academicStatus' => $matrixAcademicStatus,
        ];
    }
}
