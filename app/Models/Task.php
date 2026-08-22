<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'category',
        'deadline',
        'progress',
        'assignee',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date:Y-m-d',
            'progress' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(TaskTag::class)->orderBy('created_at');
    }

    /**
     * ============================================================
     * FLOWLIST MK2 - SUBTASK RELATIONSHIP
     * ============================================================
     *
     * Satu Task memiliki banyak Subtask.
     *
     * Subtask selalu diurutkan berdasarkan position,
     * kemudian id sebagai fallback.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)
            ->orderBy('position')
            ->orderBy('id');
    }
}
