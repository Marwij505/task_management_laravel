<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;

class TaskProgressService
{
    /**
     * Menentukan status tampilan berdasarkan deadline.
     *
     * Status asli task tetap disimpan di database:
     * pending, in-progress, completed.
     *
     * Status tambahan seperti overdue dan due-today hanya dipakai untuk tampilan.
     * Jadi kita tidak perlu mengubah enum status di database.
     */
    public function effectiveStatus(Task $task): string
    {
        if ($task->status === 'completed') {
            return 'completed';
        }

        $deadline = $this->deadlineDate($task);

        if (! $deadline) {
            return $task->status;
        }

        $today = today();

        if ($deadline->lt($today)) {
            return 'overdue';
        }

        if ($deadline->isSameDay($today)) {
            return 'due-today';
        }

        return $task->status;
    }

    /**
     * Menghitung progress berdasarkan timeline deadline.
     *
     * Completed selalu 100%.
     * Overdue dan Due Today juga 100% karena waktu pengerjaan sudah habis.
     * Task yang masih punya waktu akan dihitung dari created_at sampai deadline.
     */
    public function progress(Task $task): int
    {
        if ($task->status === 'completed') {
            return 100;
        }

        $deadline = $this->deadlineDate($task);

        if (! $deadline) {
            return $this->fallbackProgress($task);
        }

        $today = today();

        if ($deadline->lte($today)) {
            return 100;
        }

        $createdAt = $task->created_at
            ? Carbon::parse($task->created_at)->startOfDay()
            : $today->copy();

        if ($createdAt->gt($today)) {
            return $this->fallbackProgress($task);
        }

        $totalDays = max(1, $createdAt->diffInDays($deadline));
        $elapsedDays = max(0, $createdAt->diffInDays($today));

        $deadlineProgress = (int) round(($elapsedDays / $totalDays) * 100);

        /**
         * Progress belum completed tidak boleh 100 sebelum hari deadline.
         * Maka kita batasi maksimal 99%.
         */
        return min(99, max($this->fallbackProgress($task), $deadlineProgress));
    }

    /**
     * Label yang ditampilkan di card.
     */
    public function statusLabel(Task $task): string
    {
        return match ($this->effectiveStatus($task)) {
            'completed' => 'Completed',
            'in-progress' => 'In Progress',
            'pending' => 'Pending',
            'overdue' => 'Overdue',
            'due-today' => 'Due Today',
            default => 'Pending',
        };
    }

    /**
     * Payload standar agar controller tidak mengulang logika progress.
     */
    public function payload(Task $task, bool $includeTags = true): array
    {
        $payload = [
            'id' => (int) $task->id,
            'title' => $task->title,
            'description' => $task->description ?? '',

            // Status asli dari database.
            // Ini tetap dipakai untuk edit form dan filter.
            'status' => $task->status,

            // Status realistis untuk tampilan.
            'effective_status' => $this->effectiveStatus($task),
            'status_label' => $this->statusLabel($task),

            'priority' => $task->priority,
            'category' => $task->category ?? '',
            'deadline' => $this->deadlineDate($task)?->format('Y-m-d') ?? '',

            // Progress sekarang dihitung otomatis dari deadline.
            'progress' => $this->progress($task),

            'assignee' => $task->assignee ?? '',
            'created_at' => $task->created_at?->format('Y-m-d H:i:s') ?? '',
            'updated_at' => $task->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];

        if ($includeTags) {
            $payload['tags'] = $task->relationLoaded('tags')
                ? $task->tags->pluck('tag_name')->values()->all()
                : $task->tags()->pluck('tag_name')->values()->all();
        }

        return $payload;
    }

    private function deadlineDate(Task $task): ?Carbon
    {
        if (empty($task->deadline)) {
            return null;
        }

        return Carbon::parse($task->deadline)->startOfDay();
    }

    /**
     * Progress dasar hanya dipakai jika task tidak punya deadline.
     */
    private function fallbackProgress(Task $task): int
    {
        return match ($task->status) {
            'completed' => 100,
            'in-progress' => 10,
            default => 0,
        };
    }
}