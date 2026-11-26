<?php

namespace App\Services;

use App\Models\AppPermission;
use App\Models\AppRole;
use App\Models\AppUser;
use App\Models\WorkflowStep;
use App\Models\WorkflowTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PermissionService
{
    public const MODULES = [
        'dashboard' => [
            'label'   => 'الرئيسية (لوحة المؤشرات)',
            'icon'    => 'fa-chart-pie',
            'actions' => ['view'],
        ],
        'employees' => [
            'label'   => 'دليل الكادر (الأكاديمي والإداري)',
            'icon'    => 'fa-users',
            'actions' => ['view', 'create', 'edit', 'delete'],
        ],
        'decisions' => [
            'label'   => 'القرارات الإدارية والإجازات',
            'icon'    => 'fa-stamp',
            'actions' => ['view', 'create', 'edit', 'delete'],
        ],
        'archive' => [
            'label'   => 'المستودع الرقمي (الأرشيف)',
            'icon'    => 'fa-folder-tree',
            'actions' => ['view', 'create', 'edit', 'delete'],
        ],
        'org_units' => [
            'label'   => 'الهيكل الأكاديمي والبيانات المرجعية',
            'icon'    => 'fa-sitemap',
            'actions' => ['view', 'create', 'edit', 'delete'],
        ],
        'reports' => [
            'label'   => 'مركز التقارير والكشوفات',
            'icon'    => 'fa-file-contract',
            'actions' => ['view', 'export'],
        ],
        'workflow' => [
            'label'   => 'مسار الاعتمادات المتسلسل',
            'icon'    => 'fa-clipboard-check',
            'actions' => ['view', 'create', 'confirm'],
        ],
        'system_settings' => [
            'label'   => 'الصلاحيات والأمان (الأدوار والمستخدمين)',
            'icon'    => 'fa-user-shield',
            'actions' => ['view', 'create', 'edit', 'delete'],
        ],
    ];

    public const ACTIONS = [
        'view'    => ['label' => 'عرض', 'icon' => 'fa-eye'],
        'create'  => ['label' => 'إضافة', 'icon' => 'fa-plus'],
        'edit'    => ['label' => 'تعديل', 'icon' => 'fa-pen'],
        'delete'  => ['label' => 'حذف', 'icon' => 'fa-trash'],
        'export'  => ['label' => 'تصدير', 'icon' => 'fa-file-excel'],
        'confirm' => ['label' => 'اعتماد', 'icon' => 'fa-circle-check'],
    ];

    public const ROLES_ORDER = [
        'مدير نظام',
        'أمين عام',
        'مدير إدارة',
        'رئيس قسم',
        'إداري',
        'أكاديمي',
    ];

    /**
     * التحقق من امتلاك الجلسة الحالية لصلاحية معينة (مع دعم الصلاحيات الموروثة)
     */
    public static function hasPerm(string $permissionCode, ?array $overridePerms = null): bool
    {
        if ($overridePerms === null) {
            $username = strtolower((string) Session::get('logged_username', ''));
            $roleName = (string) Session::get('logged_role_name', '');
            if ($username === 'admin' || $roleName === 'مدير نظام' || str_contains($roleName, 'مدير النظام') || str_contains(strtolower($roleName), 'super admin')) {
                return true;
            }
            $perms = Session::get('logged_permissions', []);
        } else {
            $perms = $overridePerms;
        }

        if (in_array($permissionCode, $perms, true)) {
            return true;
        }

        // ربط الصلاحيات القديمة بمصفوفة الصلاحيات الحديثة
        $aliases = [
            'manage_employees'   => ['employees.create', 'employees.edit'],
            'delete_employees'   => ['employees.delete'],
            'manage_decisions'   => ['decisions.create', 'decisions.edit'],
            'delete_decisions'   => ['decisions.delete'],
            'manage_archive'     => ['archive.create', 'archive.edit'],
            'delete_archive'     => ['archive.delete'],
            'manage_master_data' => ['org_units.create', 'org_units.edit'],
            'view_reports'       => ['reports.view'],
        ];

        if (isset($aliases[$permissionCode])) {
            foreach ($aliases[$permissionCode] as $alias) {
                if (in_array($alias, $perms, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * تحديد الصفحة الافتراضية المناسبة للمستخدم بناءً على الصلاحيات الممنوحة لدوره
     */
    public static function getLandingRoute(?array $permissions = null): string
    {
        $username = strtolower((string) Session::get('logged_username', ''));
        $roleName = (string) Session::get('logged_role_name', '');
        if ($username === 'admin' || $roleName === 'مدير نظام' || str_contains($roleName, 'مدير النظام') || str_contains(strtolower($roleName), 'super admin')) {
            return route('dashboard');
        }

        if (self::hasPerm('dashboard.view', $permissions)) return route('dashboard');
        if (self::hasPerm('employees.view', $permissions)) return route('employees.index');
        if (self::hasPerm('decisions.view', $permissions)) return route('decisions.index');
        if (self::hasPerm('archive.view', $permissions)) return route('archive.index');
        if (self::hasPerm('org_units.view', $permissions)) return route('master_data.index');
        if (self::hasPerm('reports.view', $permissions)) return route('reports.index');
        if (self::hasPerm('workflow.view', $permissions)) return route('workflow.index');
        if (self::hasPerm('system_settings.view', $permissions)) return route('security.roles.index');

        return route('employees.index');
    }

    /**
     * هل المستخدم الحالي في وضع القراءة فقط لوحدة معينة؟
     */
    public static function isReadOnly(string $module): bool
    {
        return !self::hasPerm("{$module}.create")
            && !self::hasPerm("{$module}.edit")
            && !self::hasPerm("{$module}.delete")
            && !self::hasPerm("{$module}.confirm");
    }

    /**
     * جلب الصلاحيات الفعلية لمستخدم (صلاحيات الدور + الاستثناءات الفردية)
     */
    public static function getEffectivePermissions(string $userId): array
    {
        $role = DB::table('user_roles')
            ->join('app_roles', 'user_roles.role_id', '=', 'app_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->select('app_roles.role_id', 'app_roles.role_name')
            ->first();

        if ($role && ($role->role_name === 'مدير نظام' || $role->role_name === 'مدير النظام' || str_contains(strtolower($role->role_name), 'super admin'))) {
            return AppPermission::pluck('permission_code')->toArray();
        }

        $rolePerms = [];
        if ($role) {
            $rolePerms = DB::table('role_permissions')
                ->join('app_permissions', 'role_permissions.permission_id', '=', 'app_permissions.permission_id')
                ->where('role_permissions.role_id', $role->role_id)
                ->pluck('app_permissions.permission_code')
                ->toArray();
        }

        // تطبيق الصلاحيات الاستثنائية للمستخدم
        $custom = DB::table('user_custom_permissions')
            ->join('app_permissions', 'user_custom_permissions.permission_id', '=', 'app_permissions.permission_id')
            ->where('user_custom_permissions.user_id', $userId)
            ->select('app_permissions.permission_code', 'user_custom_permissions.is_granted')
            ->get();

        $effective = array_flip($rolePerms);
        foreach ($custom as $c) {
            if ($c->is_granted) {
                $effective[$c->permission_code] = true;
            } else {
                unset($effective[$c->permission_code]);
            }
        }

        return array_keys($effective);
    }

    /**
     * بذر الأدوار الستة والصلاحيات التفصيلية والمستخدمين التجريبيين ومعاملات الاعتماد
     */
    public static function ensureSeeded(): void
    {
        // 1. بذر الصلاحيات الفعلية حسب عمليات كل شاشة
        $permMap = [];
        foreach (self::MODULES as $modKey => $modInfo) {
            $allowedActions = $modInfo['actions'] ?? array_keys(self::ACTIONS);
            foreach ($allowedActions as $actKey) {
                $actInfo = self::ACTIONS[$actKey] ?? null;
                if (!$actInfo) continue;
                $code = "{$modKey}.{$actKey}";
                $perm = AppPermission::firstOrCreate(
                    ['permission_code' => $code],
                    [
                        'permission_id' => (string) Str::uuid(),
                        'description' => "{$actInfo['label']} — {$modInfo['label']}",
                    ]
                );
                $permMap[$code] = $perm->permission_id;
            }
        }

        // 2. بذر الأدوار الستة
        $defaultMatrix = [
            'مدير نظام' => array_keys($permMap),
            'أمين عام' => [
                'dashboard.view',
                'employees.view',
                'decisions.view',
                'archive.view',
                'org_units.view',
                'reports.view', 'reports.export',
                'workflow.view', 'workflow.confirm',
            ],
            'مدير إدارة' => [
                'dashboard.view',
                'employees.view', 'employees.create', 'employees.edit',
                'decisions.view', 'decisions.create', 'decisions.edit',
                'archive.view', 'archive.create', 'archive.edit',
                'org_units.view', 'org_units.edit',
                'reports.view', 'reports.export',
                'workflow.view', 'workflow.create', 'workflow.confirm',
            ],
            'رئيس قسم' => [
                'dashboard.view',
                'employees.view', 'employees.edit',
                'decisions.view',
                'archive.view',
                'org_units.view',
                'reports.view',
                'workflow.view', 'workflow.confirm',
            ],
            'إداري' => [
                'dashboard.view',
                'employees.view', 'employees.create', 'employees.edit',
                'decisions.view', 'decisions.create', 'decisions.edit',
                'archive.view', 'archive.create', 'archive.edit',
                'org_units.view',
                'reports.view',
                'workflow.view', 'workflow.create',
            ],
            'أكاديمي' => [
                'dashboard.view',
                'employees.view',
                'org_units.view',
                'reports.view',
                'workflow.view',
            ],
        ];

        $roleIds = [];
        foreach (self::ROLES_ORDER as $roleName) {
            $role = AppRole::firstOrCreate(
                ['role_name' => $roleName],
                [
                    'role_id' => (string) Str::uuid(),
                    'description' => "الدور الوظيفي: {$roleName}",
                ]
            );
            $roleIds[$roleName] = $role->role_id;

            // إذا لم يكن للدور أي صلاحيات من المصفوفة الجديدة، نربط له الافتراضية
            $existingCount = DB::table('role_permissions')
                ->where('role_id', $role->role_id)
                ->whereIn('permission_id', array_values($permMap))
                ->count();

            if ($existingCount === 0 && isset($defaultMatrix[$roleName])) {
                foreach ($defaultMatrix[$roleName] as $pCode) {
                    if (isset($permMap[$pCode])) {
                        DB::table('role_permissions')->insertOrIgnore([
                            'role_id' => $role->role_id,
                            'permission_id' => $permMap[$pCode],
                        ]);
                    }
                }
            }
        }

        // 3. التأكد من وجود المستخدم الرئيسي (admin) فقط دون إعادة بذر مستخدمين تجريبيين عشوائيين
        $adminUser = DB::table('app_users')->where('username', 'admin')->first();
        if (!$adminUser) {
            $adminId = (string) Str::uuid();
            DB::table('app_users')->insert([
                'user_id' => $adminId,
                'username' => 'admin',
                'display_name_ar' => 'م. مدير النظام',
                'password_hash' => Hash::make('admin123'),
                'is_active' => true,
                'allow_direct_password_change' => true,
                'require_confirm_sensitive_edit' => true,
                'created_at' => now(),
            ]);
        } else {
            $adminId = $adminUser->user_id;
            if (empty($adminUser->display_name_ar)) {
                DB::table('app_users')->where('user_id', $adminId)->update(['display_name_ar' => 'م. مدير النظام']);
            }
        }

        if (!DB::table('user_roles')->where('user_id', $adminId)->exists() && isset($roleIds['مدير نظام'])) {
            DB::table('user_roles')->insertOrIgnore([
                'user_id' => $adminId,
                'role_id' => $roleIds['مدير نظام'],
            ]);
        }

        // 4. بذر معاملات اعتماد تجريبية فقط إذا كان الجدول فارغاً
        if (WorkflowTransaction::count() === 0) {
            self::seedSampleWorkflows(['admin' => $adminId]);
        }
    }

    private static function seedSampleWorkflows(array $userIds): void
    {
        $staffId = $userIds['staff_admin'] ?? null;
        $deptId = $userIds['dept_head'] ?? null;
        $mgrId = $userIds['admin_manager'] ?? null;
        $secId = $userIds['sec_general'] ?? null;

        $samples = [
            [
                'code' => 'WF-2026-001',
                'type' => 'hourly_contract',
                'title' => 'عقد تدريس بالساعات — د. محمد علي (كلية العلوم الإدارية)',
                'desc' => 'تعاقد لتدريس مادة محاسبة التكاليف بواقع 6 ساعات أسبوعياً للفصل الأول.',
                'amount' => 450000,
                'dept' => 'قسم المحاسبة',
                'step' => 2,
                'status' => 'pending_dept_head',
                'steps' => [
                    [1, 'الإدخال والحفظ المبدئي', 'إداري', $staffId, 'موظف إدخال إداري', 'submitted', 'تم إدخال العقد وإرفاق المرفقات.', now()->subDays(2)],
                    [2, 'تأكيد رئيس القسم', 'رئيس قسم', null, null, 'pending', null, null],
                    [3, 'تأكيد مدير الإدارة', 'مدير إدارة', null, null, 'pending', null, null],
                    [4, 'اعتماد الأمين العام', 'أمين عام', null, null, 'pending', null, null],
                ],
            ],
            [
                'code' => 'WF-2026-002',
                'type' => 'budget',
                'title' => 'اعتماد بند موازنة المختبرات — كلية الهندسة',
                'desc' => 'طلب تعزيز مالي لتحديث أجهزة مختبر الشبكات والحاسوب.',
                'amount' => 2800000,
                'dept' => 'كلية الهندسة',
                'step' => 3,
                'status' => 'pending_admin_manager',
                'steps' => [
                    [1, 'الإدخال والحفظ المبدئي', 'إداري', $staffId, 'موظف إدخال إداري', 'submitted', 'رفع عرض الأسعار والمذكرة.', now()->subDays(4)],
                    [2, 'تأكيد رئيس القسم', 'رئيس قسم', $deptId, 'د. رئيس قسم المحاسبة', 'confirmed', 'موافق فنياً، الاحتياج ضروري للطلبة.', now()->subDays(3)],
                    [3, 'تأكيد مدير الإدارة', 'مدير إدارة', null, null, 'pending', null, null],
                    [4, 'اعتماد الأمين العام', 'أمين عام', null, null, 'pending', null, null],
                ],
            ],
            [
                'code' => 'WF-2026-003',
                'type' => 'decision',
                'title' => 'قرار تثبيت وترقية كادر إداري — شؤون الطلاب',
                'desc' => 'ترقية موظف من الدرجة الرابعة إلى الثالثة بناءً على تقارير الأداء.',
                'amount' => null,
                'dept' => 'نيابة شؤون الطلاب',
                'step' => 4,
                'status' => 'pending_sec_general',
                'steps' => [
                    [1, 'الإدخال والحفظ المبدئي', 'إداري', $staffId, 'موظف إدخال إداري', 'submitted', 'استيفاء ملف الترقية.', now()->subDays(5)],
                    [2, 'تأكيد رئيس القسم', 'رئيس قسم', $deptId, 'د. رئيس قسم المحاسبة', 'confirmed', 'توصية بالموافقة.', now()->subDays(4)],
                    [3, 'تأكيد مدير الإدارة', 'مدير إدارة', $mgrId, 'أ. مدير عام الموارد البشرية', 'confirmed', 'مستوفي للشروط القانونية والخدمة المدنية.', now()->subDays(2)],
                    [4, 'اعتماد الأمين العام', 'أمين عام', null, null, 'pending', null, null],
                ],
            ],
            [
                'code' => 'WF-2026-004',
                'type' => 'hourly_contract',
                'title' => 'تجديد عقد بالساعات — أ. سارة أحمد (كلية الآداب)',
                'desc' => 'تجديد عقد تدريس اللغة الإنجليزية للفصل الثاني.',
                'amount' => 320000,
                'dept' => 'قسم اللغة الإنجليزية',
                'step' => 5,
                'status' => 'approved',
                'steps' => [
                    [1, 'الإدخال والحفظ المبدئي', 'إداري', $staffId, 'موظف إدخال إداري', 'submitted', 'إدخال بيانات التجديد.', now()->subDays(7)],
                    [2, 'تأكيد رئيس القسم', 'رئيس قسم', $deptId, 'د. رئيس قسم المحاسبة', 'confirmed', 'موافق.', now()->subDays(6)],
                    [3, 'تأكيد مدير الإدارة', 'مدير إدارة', $mgrId, 'أ. مدير عام الموارد البشرية', 'confirmed', 'تمت المراجعة المالية.', now()->subDays(5)],
                    [4, 'اعتماد الأمين العام', 'أمين عام', $secId, 'أ.د. الأمين العام للجامعة', 'confirmed', 'يُعتمد وينفذ حسب النظام.', now()->subDays(4)],
                ],
            ],
        ];

        foreach ($samples as $s) {
            $tx = WorkflowTransaction::create([
                'transaction_code' => $s['code'],
                'transaction_type' => $s['type'],
                'title' => $s['title'],
                'description' => $s['desc'],
                'amount' => $s['amount'],
                'department_name' => $s['dept'],
                'created_by' => $staffId,
                'created_by_name' => 'موظف إدخال إداري',
                'current_step' => $s['step'],
                'status' => $s['status'],
            ]);

            foreach ($s['steps'] as $st) {
                WorkflowStep::create([
                    'transaction_id' => $tx->transaction_id,
                    'step_number' => $st[0],
                    'step_title' => $st[1],
                    'required_role' => $st[2],
                    'actor_id' => $st[3],
                    'actor_name' => $st[4],
                    'action' => $st[5],
                    'notes' => $st[6],
                    'acted_at' => $st[7],
                ]);
            }
        }
    }
}
