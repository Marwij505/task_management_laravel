<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Method utama untuk mencatat aktivitas.
     *
     * Contoh penggunaan:
     * ActivityLogService::log(
     *     module: 'admin_users',
     *     action: 'update',
     *     description: 'Updated user account',
     *     properties: ['email' => 'user@gmail.com'],
     *     targetUserId: 5
     * );
     */
    public static function log(
        string $module,
        string $action,
        string $description,
        array $properties = [],
        ?int $targetUserId = null
    ): void {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'target_user_id' => $targetUserId,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable $exception) {
            /*
             * Log aktivitas tidak boleh membuat fitur utama error.
             * Jika gagal mencatat log, sistem tetap berjalan.
             */
            Log::warning('Failed to write activity log.', [
                'module' => $module,
                'action' => $action,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}