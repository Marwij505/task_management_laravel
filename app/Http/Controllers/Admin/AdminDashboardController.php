<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskProgressService;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private TaskProgressService $taskProgressService
    ) {
    }

    public function index(): View
    {
        /*
         * Load users and tasks once for dashboard summary.
         * This keeps the admin dashboard simple and easy to debug.
         */
        $users = User::query()
            ->withCount('tasks')
            ->latest('created_at')
            ->get();

        $tasks = Task::query()
            ->with('user')
            ->latest('created_at')
            ->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();

        /*
         * Effective status follows the realistic deadline logic.
         * This keeps admin numbers consistent with the user dashboard.
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

        $completionRate = $totalTasks > 0
            ? (int) round(($completedTasks / $totalTasks) * 100)
            : 0;

        /*
         * Count unique categories globally.
         * Category text is normalized to avoid duplicate values like Personal and personal.
         */
        $totalCategories = $tasks
            ->pluck('category')
            ->filter(fn ($category) => trim((string) $category) !== '')
            ->map(fn ($category) => strtolower(trim((string) $category)))
            ->unique()
            ->count();

        $priorityBreakdown = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];

        $recentUsers = $users
            ->take(5)
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name ?: $user->name ?: $user->username,
                    'username' => $user->username ?: '-',
                    'email' => $user->email,
                    'role' => $user->role,
                    'tasks_count' => $user->tasks_count,
                    'joined_at' => $user->created_at
                        ? Carbon::parse($user->created_at)->format('d M Y')
                        : '-',
                ];
            });

        $recentTasks = $tasks
            ->take(6)
            ->map(function (Task $task) {
                $effectiveStatus = $this->taskProgressService->effectiveStatus($task);

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'owner' => $task->user?->full_name
                        ?: $task->user?->name
                        ?: $task->user?->username
                        ?: 'Unknown User',
                    'status' => $effectiveStatus,
                    'status_label' => $this->taskProgressService->statusLabel($task),
                    'priority' => $task->priority,
                    'progress' => $this->taskProgressService->progress($task),
                    'deadline' => $task->deadline
                        ? Carbon::parse($task->deadline)->format('d M Y')
                        : 'No deadline',
                ];
            });

        return view('admin.dashboard', [
            'totalUsers' => $users->count(),
            'totalAdmins' => $users->where('role', User::ROLE_ADMIN)->count(),
            'totalRegularUsers' => $users->where('role', User::ROLE_USER)->count(),
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'pendingTasks' => $pendingTasks,
            'dueTodayTasks' => $dueTodayTasks,
            'overdueTasks' => $overdueTasks,
            'completionRate' => $completionRate,
            'totalCategories' => $totalCategories,
            'priorityBreakdown' => $priorityBreakdown,
            'recentUsers' => $recentUsers,
            'recentTasks' => $recentTasks,
        ]);
    }
}