<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $base = Task::query()->where('user_id', $userId);

        $totalTasks = (clone $base)->count();
        $completedTasks = (clone $base)->where('status', 'completed')->count();
        $pendingTasks = (clone $base)->where('status', 'pending')->count();
        $inProgressTasks = (clone $base)->where('status', 'in-progress')->count();
        $completionRate = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $completedThisMonth = (clone $base)
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(COALESCE(updated_at, created_at))'), [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->count();

        $tasksLastSevenDays = (clone $base)
            ->whereDate('created_at', '>=', today()->subDays(6))
            ->whereDate('created_at', '<=', today())
            ->count();
        $avgTasksPerDay = round($tasksLastSevenDays / 7, 1);

        $avgCompletionRaw = Task::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->selectRaw('AVG(DATEDIFF(COALESCE(updated_at, created_at), created_at)) AS average_days')
            ->value('average_days');
        $avgCompletionTime = $avgCompletionRaw !== null ? round((float) $avgCompletionRaw, 1) : 0;

        $weeklyLabels = [];
        $weeklyCompleted = [];
        $weeklyPending = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $weeklyLabels[] = $date->format('D');

            $dayQuery = Task::query()
                ->where('user_id', $userId)
                ->whereDate(DB::raw('COALESCE(updated_at, created_at)'), $date->toDateString());

            $weeklyCompleted[] = (clone $dayQuery)->where('status', 'completed')->count();
            $weeklyPending[] = (clone $dayQuery)->where('status', '!=', 'completed')->count();
        }

        $monthlyLabels = [];
        $monthlyCompleted = [];

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::now()->subMonthsNoOverflow($monthsAgo);
            $monthlyLabels[] = $month->format('M');
            $monthlyCompleted[] = Task::query()
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->whereBetween(DB::raw('DATE(COALESCE(updated_at, created_at))'), [
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString(),
                ])
                ->count();
        }

        $priorityCounts = Task::query()
            ->where('user_id', $userId)
            ->select('priority', DB::raw('COUNT(*) AS total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $priorityData = [
            ['label' => 'High', 'value' => (int) ($priorityCounts['high'] ?? 0), 'color' => '#ef4444'],
            ['label' => 'Medium', 'value' => (int) ($priorityCounts['medium'] ?? 0), 'color' => '#eab308'],
            ['label' => 'Low', 'value' => (int) ($priorityCounts['low'] ?? 0), 'color' => '#22c55e'],
        ];

        $categoryColors = ['#3b82f6', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316'];
        $categoryData = Task::query()
            ->where('user_id', $userId)
            ->selectRaw("COALESCE(NULLIF(category, ''), 'Uncategorized') AS category_name, COUNT(*) AS total")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->values()
            ->map(fn ($row, int $index) => [
                'label' => $row->category_name,
                'value' => (int) $row->total,
                'color' => $categoryColors[$index % count($categoryColors)],
            ]);

        return response()->json([
            'success' => true,
            'summary' => [
                'completionRate' => $completionRate,
                'avgTasksPerDay' => $avgTasksPerDay,
                'tasksCompletedThisMonth' => $completedThisMonth,
                'avgCompletionTime' => $avgCompletionTime,
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'pendingTasks' => $pendingTasks,
                'inProgressTasks' => $inProgressTasks,
            ],
            'weekly' => [
                'labels' => $weeklyLabels,
                'completed' => $weeklyCompleted,
                'pending' => $weeklyPending,
            ],
            'monthly' => [
                'labels' => $monthlyLabels,
                'completed' => $monthlyCompleted,
            ],
            'priority' => $priorityData,
            'category' => $categoryData,
        ]);
    }
}
