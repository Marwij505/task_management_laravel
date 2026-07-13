<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /**
     * Kolom yang boleh diisi melalui ActivityLog::create().
     */
    protected $fillable = [
        'user_id',
        'target_user_id',
        'module',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * Membaca properties JSON sebagai array.
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * User yang melakukan aktivitas.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User yang terdampak aktivitas.
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}