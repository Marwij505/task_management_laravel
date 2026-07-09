<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskProgressService;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminStatisticsController extends Controller
{
    public function __construct(
        private TaskProgressService $taskProgressService
    ) {
    }

    public function index(): View
    {
        /*
         * Ambil semua data utama.
         * with('user') dipakai agar statistik owner tidak membuat query berulang.
         */
        $users = User::query()
            ->withCount('tasks')
            ->get();

        $tasks = Task::query()
            ->with('user')
            ->get();

        $totalUsers = $users->count();
        $totalAdmins = $users->where('role', User::ROLE_ADMIN)->count();
        $totalRegularUsers = $users->where('role', User::ROLE_USER)->count();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();

        /*
         * Completion rate dihitung dari total task yang sudah completed.
         */
        $completionRate = $totalTasks > 0
            ? (int) round(($completedTasks / $totalTasks) * 100)
            : 0;

        /*
         * Status realistis memakai TaskProgressService.
         * Jadi overdue dan due today tetap dihitung walau tidak disimpan di database.
         */
        $statusBreakdown = [
            'pending' => 0,
            'in-progress' => 0,
            'completed' => 0,
            'due-today' => 0,
            'overdue' => 0,
        ];

        foreach ($tasks as $task) {
            $effectiveStatus = $this->taskProgressService->effectiveStatus($task);

            if (array_key_exists($effectiveStatus, $statusBreakdown)) {
                $statusBreakdown[$effectiveStatus]++;
            }
        }

        /*
         * Statistik priority global.
         */
        $priorityBreakdown = [
            'high' => $tasks->where('priority', 'high')->count(),
            'medium' => $tasks->where('priority', 'medium')->count(),
            'low' => $tasks->where('priority', 'low')->count(),
        ];

        /*
         * Category ranking.
         * Category dinormalisasi agar Personal dan personal tidak dianggap beda.
         */
        $categoryRanking = $tasks
            ->pluck('category')
            ->filter(fn ($category) => trim((string) $category) !== '')
            ->map(fn ($category) => strtolower(trim((string) $category)))
            ->countBy()
            ->sortDesc()
            ->take(8)
            ->map(function ($count, $category) use ($totalTasks) {
                return [
                    'name' => ucfirst($category),
                    'count' => $count,
                    'percentage' => $totalTasks > 0
                        ? (int) round(($count / $totalTasks) * 100)
                        : 0,
                ];
            })
            ->values();

        /*
         * Top users berdasarkan jumlah task dan jumlah completed task.
         */
        $topUsers = $users
            ->sortByDesc('tasks_count')
            ->take(6)
            ->map(function (User $user) {
                $completedCount = $user->tasks()
                    ->where('status', 'completed')
                    ->count();

                $completionRate = $user->tasks_count > 0
                    ? (int) round(($completedCount / $user->tasks_count) * 100)
                    : 0;

                return [
                    'name' => $user->full_name ?: $user->name ?: $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'tasks_count' => $user->tasks_count,
                    'completed_count' => $completedCount,
                    'completion_rate' => $completionRate,
                ];
            })
            ->values();

        /*
         * Trend 6 bulan terakhir.
         * Created task memakai created_at.
         * Completed task memakai updated_at karena tabel belum punya completed_at.
         */
        $monthlyTrend = collect(range(5, 0))
            ->map(function ($monthOffset) use ($tasks) {
                $month = now()->subMonths($monthOffset);

                $createdCount = $tasks
                    ->filter(function (Task $task) use ($month) {
                        return $task->created_at
                            && Carbon::parse($task->created_at)->format('Y-m') === $month->format('Y-m');
                    })
                    ->count();

                $completedCount = $tasks
                    ->filter(function (Task $task) use ($month) {
                        return $task->status === 'completed'
                            && $task->updated_at
                            && Carbon::parse($task->updated_at)->format('Y-m') === $month->format('Y-m');
                    })
                    ->count();

                return [
                    'label' => $month->format('M Y'),
                    'created' => $createdCount,
                    'completed' => $completedCount,
                ];
            });

        /*
         * Deadline risk.
         * Ini membantu admin melihat risiko keterlambatan secara cepat.
         */
        $deadlineRisk = [
            'noDeadline' => $tasks->filter(fn (Task $task) => empty($task->deadline))->count(),
            'dueToday' => $statusBreakdown['due-today'],
            'overdue' => $statusBreakdown['overdue'],
            'futureDeadline' => $tasks->filter(function (Task $task) {
                return $task->deadline
                    && Carbon::parse($task->deadline)->isFuture()
                    && $this->taskProgressService->effectiveStatus($task) !== 'due-today';
            })->count(),
        ];

        return view('admin.statistics.index', [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalRegularUsers' => $totalRegularUsers,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'completionRate' => $completionRate,
            'statusBreakdown' => $statusBreakdown,
            'priorityBreakdown' => $priorityBreakdown,
            'categoryRanking' => $categoryRanking,
            'topUsers' => $topUsers,
            'monthlyTrend' => $monthlyTrend,
            'deadlineRisk' => $deadlineRisk,
        ]);
    }
}