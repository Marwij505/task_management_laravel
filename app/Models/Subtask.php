<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtask extends Model
{
    use HasFactory;

    /**
     * Field yang boleh diisi menggunakan mass assignment.
     */
    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
        'position',
        'completed_at',
    ];


    /**
     * Cast database ke tipe PHP.
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'position' => 'integer',
            'completed_at' => 'datetime',
        ];
    }


    /**
     * ============================================================
     * RELATIONSHIP
     * ============================================================
     *
     * Setiap subtask dimiliki oleh tepat satu Task.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}