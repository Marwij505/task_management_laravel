<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /**
     * Kolom yang boleh diisi massal.
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
     * properties otomatis dibaca sebagai array.
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * Actor adalah user yang melakukan aktivitas.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Target user adalah user yang terdampak aktivitas.
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}