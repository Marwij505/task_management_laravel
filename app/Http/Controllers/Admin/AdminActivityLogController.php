<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityLogController extends Controller
{
    /**
     * Menampilkan daftar aktivitas sistem.
     */
    public function index(Request $request): View
    {
        /*
         * Filter dari query string.
         */
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'module' => trim((string) $request->query('module', '')),
            'action' => trim((string) $request->query('action', '')),
            'user_id' => trim((string) $request->query('user_id', '')),
            'date' => trim((string) $request->query('date', '')),
        ];

        /*
         * Daftar user untuk filter actor.
         */
        $users = User::query()
            ->orderBy('full_name')
            ->orderBy('username')
            ->get();

        /*
         * Module dan action diambil dari log yang tersedia.
         */
        $modules = ActivityLog::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        /*
         * Query utama activity log.
         */
        $logs = ActivityLog::query()
            ->with(['actor', 'targetUser'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('actor', function ($actorQuery) use ($search) {
                            $actorQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('targetUser', function ($targetQuery) use ($search) {
                            $targetQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['module'] !== '', function ($query) use ($filters) {
                $query->where('module', $filters['module']);
            })
            ->when($filters['action'] !== '', function ($query) use ($filters) {
                $query->where('action', $filters['action']);
            })
            ->when($filters['user_id'] !== '', function ($query) use ($filters) {
                $query->where('user_id', (int) $filters['user_id']);
            })
            ->when($filters['date'] !== '', function ($query) use ($filters) {
                $query->whereDate('created_at', $filters['date']);
            })
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        /*
         * Ringkasan statistik.
         */
        $stats = [
            'totalLogs' => ActivityLog::count(),

            'todayLogs' => ActivityLog::query()
                ->whereDate('created_at', today())
                ->count(),

            'adminLogs' => ActivityLog::query()
                ->where('module', 'like', 'admin_%')
                ->count(),

            'userLogs' => ActivityLog::query()
                ->whereIn('module', ['user_tasks', 'profile'])
                ->count(),

            'authLogs' => ActivityLog::query()
                ->where('module', 'auth')
                ->count(),
        ];

        return view('admin.logs.index', [
            'logs' => $logs,
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }
}