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
         * ============================================================
         * ADMIN DASHBOARD DATA
         * ============================================================
         *
         * User dan task diambil sekali untuk seluruh ringkasan dashboard.
         *
         * Flowlist MK2:
         * - task juga membawa jumlah subtask/checklist.
         * - Recent Tasks dapat menampilkan summary checklist.
         */
        $users = User::query()
            ->withCount('tasks')
            ->latest('created_at')
            ->get();

        $tasks = Task::query()
            ->with('user')
            ->withCount([
                /*
                 * Jumlah seluruh subtask pada task.
                 */
                'subtasks as subtask_total',

                /*
                 * Jumlah subtask yang sudah selesai.
                 */
                'subtasks as subtask_completed' => function ($query) {
                    $query->where('is_completed', true);
                },
            ])
            ->latest('created_at')
            ->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();

        /*
         * Effective status mengikuti deadline realistis.
         * Angka admin tetap konsisten dengan dashboard user.
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
         * Hitung kategori unik secara global.
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

        /*
         * ============================================================
         * RECENT USERS
         * ============================================================
         *
         * ID dan email ikut dikirim agar card Recent User dapat
         * diarahkan ke User Management dan langsung memfokuskan user.
         */
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

        /*
         * ============================================================
         * RECENT TASKS
         * ============================================================
         *
         * Flowlist MK2 Admin Synchronization:
         * - card dapat diklik menuju Admin Task Detail.
         * - jumlah checklist ikut ditampilkan.
         */
        $recentTasks = $tasks
            ->take(6)
            ->map(function (Task $task) {
                $effectiveStatus = $this->taskProgressService->effectiveStatus($task);

                $subtaskTotal = (int) ($task->subtask_total ?? 0);
                $subtaskCompleted = (int) ($task->subtask_completed ?? 0);

                $checklistPercentage = $subtaskTotal > 0
                    ? (int) round(($subtaskCompleted / $subtaskTotal) * 100)
                    : 0;

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

                    /*
                     * Checklist summary.
                     */
                    'subtask_total' => $subtaskTotal,
                    'subtask_completed' => $subtaskCompleted,
                    'checklist_percentage' => $checklistPercentage,
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
