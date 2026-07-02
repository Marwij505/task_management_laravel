<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private TaskProgressService $taskProgressService
    ) {
    }

    public function data(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $tasks = Task::query()
            ->where('user_id', $userId)
            ->with('tags')
            ->latest('created_at')
            ->get();

        $totalTasks = $tasks->count();

        $completedTasks = $tasks
            ->where('status', 'completed')
            ->count();

        /**
         * In progress sekarang hanya menghitung task aktif
         * yang belum due today dan belum overdue.
         */
        $inProgressTasks = $tasks
            ->filter(function (Task $task) {
                return $task->status === 'in-progress'
                    && $this->taskProgressService->effectiveStatus($task) === 'in-progress';
            })
            ->count();

        $pendingTasks = $tasks
            ->filter(function (Task $task) {
                return $task->status === 'pending'
                    && $this->taskProgressService->effectiveStatus($task) === 'pending';
            })
            ->count();

        $dueTodayTasks = $tasks
            ->filter(function (Task $task) {
                return $this->taskProgressService->effectiveStatus($task) === 'due-today';
            })
            ->count();

        $overdueTasks = $tasks
            ->filter(function (Task $task) {
                return $this->taskProgressService->effectiveStatus($task) === 'overdue';
            })
            ->count();

        $recentTasks = $tasks
            ->take(5)
            ->map(function (Task $task) {
                return $this->taskProgressService->payload($task, false);
            })
            ->values();

        $upcomingDeadlines = Task::query()
            ->where('user_id', $userId)
            ->whereNotNull('deadline')
            ->whereDate('deadline', '>=', today())
            ->where('status', '!=', 'completed')
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(function (Task $task) {
                return $this->taskProgressService->payload($task, false);
            })
            ->values();

        return response()->json([
            'success' => true,
            'stats' => [
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'inProgressTasks' => $inProgressTasks,
                'pendingTasks' => $pendingTasks,
                'dueTodayTasks' => $dueTodayTasks,
                'overdueTasks' => $overdueTasks,
            ],
            'recentTasks' => $recentTasks,
            'upcomingDeadlines' => $upcomingDeadlines,
        ]);
    }
}