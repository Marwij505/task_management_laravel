<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Mencatat aktivitas aplikasi.
     *
     * Error pencatatan log tidak boleh menghentikan fitur utama.
     */
    public static function log(
        string $module,
        string $action,
        string $description,
        array $properties = [],
        ?int $targetUserId = null,
        ?int $actorId = null
    ): void {
        try {
            ActivityLog::create([
                'user_id' => $actorId ?? Auth::id(),
                'target_user_id' => $targetUserId,
                'module' => mb_substr($module, 0, 80),
                'action' => mb_substr($action, 0, 80),
                'description' => $description,
                'properties' => self::sanitizeProperties($properties),
                'ip_address' => self::requestIp(),
                'user_agent' => self::requestUserAgent(),
            ]);
        } catch (\Throwable $exception) {
            /*
             * Simpan kegagalan pencatatan ke storage/logs/laravel.log.
             * Proses utama tetap berjalan.
             */
            Log::warning('Failed to write activity log.', [
                'module' => $module,
                'action' => $action,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Menghapus data sensitif sebelum masuk ke database.
     */
    private static function sanitizeProperties(array $properties): array
    {
        $blockedKeys = [
            '_token',
            'token',
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'confirm_password',
            'remember_token',
        ];

        return collect($properties)
            ->reject(function ($value, $key) use ($blockedKeys) {
                return in_array((string) $key, $blockedKeys, true);
            })
            ->map(function ($value) {
                if (is_array($value)) {
                    return self::sanitizeProperties($value);
                }

                if (is_string($value)) {
                    return mb_substr($value, 0, 700);
                }

                return $value;
            })
            ->all();
    }

    /**
     * Mengambil IP hanya ketika HTTP request tersedia.
     */
    private static function requestIp(): ?string
    {
        try {
            return app()->runningInConsole()
                ? null
                : request()->ip();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Mengambil browser atau user-agent.
     */
    private static function requestUserAgent(): ?string
    {
        try {
            return app()->runningInConsole()
                ? null
                : request()->userAgent();
        } catch (\Throwable) {
            return null;
        }
    }
}