<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        /*
         * Data awal untuk admin.
         * Babak 1 hanya memastikan role dan akses admin berjalan.
         */
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('role', User::ROLE_ADMIN)->count(),
            'totalRegularUsers' => User::where('role', User::ROLE_USER)->count(),
            'totalTasks' => Task::count(),
        ]);
    }
}