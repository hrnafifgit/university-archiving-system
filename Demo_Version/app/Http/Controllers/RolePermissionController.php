<?php

namespace App\Http\Controllers;

use App\Models\AppPermission;
use App\Models\AppRole;
use App\Models\AppUser;
use App\Models\Employee;
use App\Services\AuditLogService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    /**
     * شاشة إدارة الأدوار ومصفوفة الصلاحيات التفصيلية وأمان الحساب
     */
    public function index(Request $request)
    {
        PermissionService::ensureSeeded();

        $roles = AppRole::with('permissions')->get()->sortBy(function ($r) {
            $idx = array_search($r->role_name, PermissionService::ROLES_ORDER, true);
            return $idx === false ? 99 : $idx;
        })->values();

        $users = AppUser::with(['roles', 'employee'])->orderBy('username')->get();
        $assignedEmpIds = $users->pluck('employee_id')->filter()->toArray();
        $availableEmployees = Employee::whereNotIn('employee_id', $assignedEmpIds)
            ->orderBy('full_name_ar')
            ->get(['employee_id', 'full_name_ar', 'employee_number']);
        $allPermissions = AppPermission::all()->keyBy('permission_code');

        $mode = $request->query('mode', 'role'); // role | user
        $selectedRoleId = $request->query('role_id', $roles->first()?->role_id);
        $selectedUserId = $request->query('user_id', $users->first()?->user_id);

        $selectedRole = $roles->firstWhere('role_id', $selectedRoleId) ?? $roles->first();
        $selectedUser = $users->firstWhere('user_id', $selectedUserId) ?? $users->first();

        $grantedCodes = [];
        if ($mode === 'user' && $selectedUser) {
            $grantedCodes = PermissionService::getEffectivePermissions($selectedUser->user_id);
        } elseif ($selectedRole) {
            $grantedCodes = $selectedRole->permissions->pluck('permission_code')->toArray();
        }

        return view('security.roles_matrix', [
            'roles' => $roles,
            'users' => $users,
            'availableEmployees' => $availableEmployees,
            'modules' => PermissionService::MODULES,
            'actions' => PermissionService::ACTIONS,
            'mode' => $mode,
            'selectedRole' => $selectedRole,
            'selectedUser' => $selectedUser,
            'grantedCodes' => $grantedCodes,
        ]);
    }

    /**
     * حفظ مصفوفة الصلاحيات وإعدادات أمان الحساب
     */
    public function updateMatrix(Request $request)
    {
        PermissionService::ensureSeeded();

        $mode = $request->input('mode', 'role');
        $selectedPerms = $request->input('permissions', []); // array of permission_codes
        $allPermRecords = AppPermission::all()->keyBy('permission_code');

        if ($mode === 'user') {
            $userId = $request->input('user_id');
            $user = AppUser::with('roles')->findOrFail($userId);

            // حفظ إعدادات أمان الحساب
            DB::table('app_users')->where('user_id', $userId)->update([
                'allow_direct_password_change' => $request->boolean('allow_direct_password_change'),
                'require_confirm_sensitive_edit' => $request->boolean('require_confirm_sensitive_edit'),
            ]);

            // مقارنة صلاحيات الدور الأساسية مع المختارة لتحديد الاستثناءات
            $role = $user->roles->first();
            $roleCodes = $role ? $role->permissions()->pluck('permission_code')->toArray() : [];

            DB::table('user_custom_permissions')->where('user_id', $userId)->delete();

            foreach ($allPermRecords as $code => $permObj) {
                $inRole = in_array($code, $roleCodes, true);
                $inSelected = in_array($code, $selectedPerms, true);

                if ($inSelected !== $inRole) {
                    DB::table('user_custom_permissions')->insert([
                        'id' => (string) Str::uuid(),
                        'user_id' => $userId,
                        'permission_id' => $permObj->permission_id,
                        'is_granted' => $inSelected,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (Session::get('logged_user_id') === $userId) {
                Session::put('logged_permissions', PermissionService::getEffectivePermissions($userId));
            }

            AuditLogService::log('USER_PERMISSIONS_UPDATED', 'app_users', $userId, null, ['permissions_count' => count($selectedPerms)]);

            return redirect()->route('security.roles.index', ['mode' => 'user', 'user_id' => $userId])
                ->with('success', "تم حفظ الصلاحيات الاستثنائية وإعدادات الأمان للمستخدم ({$user->username}) بنجاح.");
        }

        // وضع تعديل الدور الوظيفي
        $roleId = $request->input('role_id');
        $role = AppRole::findOrFail($roleId);

        DB::table('role_permissions')->where('role_id', $roleId)->delete();
        foreach ($selectedPerms as $code) {
            if (isset($allPermRecords[$code])) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $allPermRecords[$code]->permission_id,
                ]);
            }
        }

        // تحديث الجلسة إذا كان الدور المعدل هو الدور الحالي
        if (Session::get('logged_role_name') === $role->role_name && Session::has('logged_user_id')) {
            Session::put('logged_permissions', PermissionService::getEffectivePermissions(Session::get('logged_user_id')));
        }

        AuditLogService::log('ROLE_PERMISSIONS_UPDATED', 'app_roles', $roleId, null, ['role' => $role->role_name, 'count' => count($selectedPerms)]);

        return redirect()->route('security.roles.index', ['mode' => 'role', 'role_id' => $roleId])
            ->with('success', "تم حفظ مصفوفة الصلاحيات للدور ({$role->role_name}) بنجاح.");
    }

    /**
     * إضافة دور وظيفي جديد ومنحه صلاحيات أولية
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:100|unique:app_roles,role_name',
            'description' => 'nullable|string|max:250',
            'permissions' => 'nullable|array',
        ], [
            'role_name.required' => 'يرجى كتابة اسم الدور الوظيفي.',
            'role_name.unique' => 'هذا الدور مسجل مسبقاً.',
        ]);

        $role = AppRole::create([
            'role_id' => (string) Str::uuid(),
            'role_name' => trim($validated['role_name']),
            'description' => $validated['description'] ?? "دور مخصص: {$validated['role_name']}",
        ]);

        if (!empty($validated['permissions'])) {
            $permIds = AppPermission::whereIn('permission_code', $validated['permissions'])->pluck('permission_id');
            foreach ($permIds as $pid) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $role->role_id,
                    'permission_id' => $pid,
                ]);
            }
        }

        AuditLogService::log('ROLE_CREATED', 'app_roles', $role->role_id, null, ['role_name' => $role->role_name]);

        return redirect()->route('security.roles.index', ['mode' => 'role', 'role_id' => $role->role_id])
            ->with('success', "تم إنشاء الدور ({$role->role_name}) ومنحه الصلاحيات المحددة.");
    }

    /**
     * فحص توفر اسم المستخدم لحظياً (منع التكرار)
     */
    public function checkUsername(Request $request)
    {
        $username = strtolower(trim($request->query('username', '')));
        if (empty($username)) {
            return response()->json(['available' => false, 'message' => '']);
        }

        $exists = DB::table('app_users')
            ->whereRaw('LOWER(TRIM(username)) = ?', [$username])
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'اسم المستخدم محجوز مسبقاً لشخص آخر، يرجى اختيار اسم آخر!' : 'اسم المستخدم متاح ويمكن استخدامه.',
        ]);
    }

    /**
     * تعيين كادر كمستخدم للنظام وتخصيص دوره وصلاحياته
     */
    public function assignEmployeeUser(Request $request)
    {
        $normalizedUsername = strtolower(trim((string) $request->input('username', '')));
        $request->merge(['username' => $normalizedUsername]);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id|unique:app_users,employee_id',
            'username' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[a-z0-9_.\-]+$/', 'unique:app_users,username'],
            'password' => 'required|string|min:4',
            'role_id' => 'required|exists:app_roles,role_id',
        ], [
            'employee_id.required' => 'يرجى اختيار الكادر.',
            'employee_id.unique' => 'هذا الكادر لديه حساب مستخدم مسجل مسبقاً في النظام.',
            'username.required' => 'يرجى إدخال اسم المستخدم.',
            'username.min' => 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل.',
            'username.regex' => 'اسم المستخدم يقبل أحرفاً إنجليزية صغيرة وأرقاماً وشرطة سفلية فقط (بدون مسافات).',
            'username.unique' => 'اسم المستخدم مستخدم مسبقاً لشخص آخر، لا يمكن تكرار اسم المستخدم.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن 4 أحرف/أرقام.',
        ]);

        $emp = Employee::findOrFail($validated['employee_id']);

        $userId = DB::transaction(function () use ($validated, $emp) {
            $uid = (string) Str::uuid();
            DB::table('app_users')->insert([
                'user_id' => $uid,
                'employee_id' => $emp->employee_id,
                'username' => $validated['username'],
                'display_name_ar' => $emp->full_name_ar,
                'password_hash' => Hash::make(trim($validated['password'])),
                'is_active' => true,
                'allow_direct_password_change' => true,
                'require_confirm_sensitive_edit' => true,
                'created_at' => now(),
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $uid,
                'role_id' => $validated['role_id'],
            ]);

            return $uid;
        });

        AuditLogService::log('EMPLOYEE_ASSIGNED_USER', 'app_users', $userId, null, [
            'employee_id' => $emp->employee_id,
            'username' => $validated['username'],
        ]);

        return redirect()->route('security.roles.index', ['mode' => 'user', 'user_id' => $userId])
            ->with('success', "تم إنشاء حساب المستخدم ({$validated['username']}) لـ ({$emp->full_name_ar}) وربطه بالدور بنجاح.");
    }

    /**
     * حذف مستخدم فرعي (مع حماية المستخدم الرئيسي admin)
     */
    public function destroyUser($userId)
    {
        $user = DB::table('app_users')->where('user_id', $userId)->first();
        if (!$user) {
            return back()->with('error', 'المستخدم غير موجود.');
        }
        if (strtolower($user->username) === 'admin') {
            return back()->with('error', 'لا يمكن حذف حساب مدير النظام الرئيسي (admin).');
        }

        DB::table('app_users')->where('user_id', $userId)->delete();
        AuditLogService::log('USER_DELETED', 'app_users', $userId, ['username' => $user->username], null);

        return redirect()->route('security.roles.index', ['mode' => 'user'])
            ->with('success', "تم حذف حساب المستخدم ({$user->username}) بنجاح.");
    }

    /**
     * محاكاة تبديل الدور والمستخدم الفوري (Role Switcher)
     */
    public function switchRole(Request $request)
    {
        PermissionService::ensureSeeded();

        $userId = $request->input('user_id');
        $user = DB::table('app_users')->where('user_id', $userId)->first();

        if (!$user) {
            return back()->with('error', 'لم يتم العثور على المستخدم المطلوب.');
        }

        $role = DB::table('user_roles')
            ->join('app_roles', 'user_roles.role_id', '=', 'app_roles.role_id')
            ->where('user_roles.user_id', $user->user_id)
            ->select('app_roles.role_name', 'app_roles.role_id')
            ->first();

        $roleName = $role->role_name ?? 'إداري';
        $permissions = PermissionService::getEffectivePermissions($user->user_id);

        Session::put('logged_user_id', $user->user_id);
        Session::put('logged_username', $user->username);
        Session::put('logged_display_name', $user->display_name_ar ?? $user->username);
        Session::put('logged_role_name', $roleName);
        Session::put('logged_permissions', $permissions);

        $landing = PermissionService::getLandingRoute($permissions);
        return redirect($landing)->with('success', "تم التبديل إلى حساب ({$user->display_name_ar} — {$roleName}) بنجاح.");
    }
}
