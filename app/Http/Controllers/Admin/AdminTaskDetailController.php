<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskProgressService;
use Illuminate\Contracts\View\View;

class AdminTaskDetailController extends Controller
{
    public function __construct(
        private TaskProgressService $taskProgressService
    ) {
    }

    /**
     * ============================================================
     * FLOWLIST MK2 - ADMIN TASK DETAIL
     * ============================================================
     *
     * Administrator dapat melihat task milik user mana pun.
     *
     * Halaman ini bersifat read-only untuk subtask/checklist.
     * Admin tetap mengelola task utama melalui halaman All Tasks.
     */
    public function show(Task $task): View
    {
        /*
         * Load semua relationship yang dipakai pada halaman detail.
         */
        $task->load([
            'user',
            'tags',
            'subtasks',
        ]);

        /*
         * ========================================================
         * TASK DISPLAY STATE
         * ========================================================
         *
         * Gunakan TaskProgressService agar status/progress admin
         * konsisten dengan user dashboard dan Admin All Tasks.
         */
        $effectiveStatus =
            $this->taskProgressService
                ->effectiveStatus($task);

        $statusLabel =
            $this->taskProgressService
                ->statusLabel($task);

        $taskProgress =
            $this->taskProgressService
                ->progress($task);

        /*
         * CSS Admin Dashboard memakai class "progress"
         * untuk status in-progress.
         */
        $statusClass =
            $effectiveStatus === 'in-progress'
                ? 'progress'
                : $effectiveStatus;

        /*
         * ========================================================
         * CHECKLIST STATISTICS
         * ========================================================
         */
        $totalSubtasks =
            $task->subtasks->count();

        $completedSubtasks =
            $task->subtasks
                ->where('is_completed', true)
                ->count();

        $remainingSubtasks =
            max(
                0,
                $totalSubtasks - $completedSubtasks
            );

        $checklistPercentage =
            $totalSubtasks > 0
                ? (int) round(
                    ($completedSubtasks / $totalSubtasks) * 100
                )
                : 0;

        return view(
            'admin.tasks-detail.index',
            [
                'task' => $task,
                'effectiveStatus' => $effectiveStatus,
                'statusLabel' => $statusLabel,
                'statusClass' => $statusClass,
                'taskProgress' => $taskProgress,
                'totalSubtasks' => $totalSubtasks,
                'completedSubtasks' => $completedSubtasks,
                'remainingSubtasks' => $remainingSubtasks,
                'checklistPercentage' => $checklistPercentage,
            ]
        );
    }
}
