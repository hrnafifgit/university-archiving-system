<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class AuditLogService
{
    /**
     * تسجيل حركة تدقيق في سجل الرقابة الرقمي audit_logs
     */
    public static function log(string $actionCode, string $entityName, ?string $entityId = null, ?array $oldValues = null, ?array $newValues = null): ?AuditLog
    {
        try {
            $userId = Session::get('logged_user_id');

            // التحقق من صحة وجود معرف المستخدم في جدول app_users لمنع كسر القيد المرجعي في PostgreSQL
            if ($userId) {
                if (!\Illuminate\Support\Str::isUuid($userId) || !\Illuminate\Support\Facades\DB::table('app_users')->where('user_id', $userId)->exists()) {
                    $userId = null;
                }
            }

            // التحقق من صلاحية UUID لمعرف الكيان
            if ($entityId && !\Illuminate\Support\Str::isUuid($entityId)) {
                $entityId = null;
            }

            return AuditLog::create([
                'user_id' => $userId,
                'action_code' => strtoupper($actionCode),
                'entity_name' => strtolower($entityName),
                'entity_id' => $entityId,
                'occurred_at' => now(),
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);
        } catch (\Exception $e) {
            // سجل التدقيق لا ينبغي أن يعطل العملية الرئيسية في حال حدوث استثناء عابر
            \Illuminate\Support\Facades\Log::warning("فشل تسجيل حركة التدقيق: " . $e->getMessage());
            return null;
        }
    }
}
