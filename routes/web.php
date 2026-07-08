<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureLoginSessionStillValid;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /*
     * Halaman awal juga mengikuti role.
     */
    return Auth::user()?->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
| Hanya untuk pengunjung yang belum login.
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'resetPasswordDirect'])
        ->name('password.update.direct');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| Semua halaman di dalam group ini wajib login.
*/
Route::middleware(['auth', EnsureLoginSessionStillValid::class])->group(function () {
    /*
     * Halaman user biasa.
     */
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks', [PageController::class, 'taskList'])->name('tasks.index');
    Route::get('/tasks/create', [PageController::class, 'createTask'])->name('tasks.create');
    Route::get('/task-detail', [PageController::class, 'taskDetail'])->name('tasks.detail');
    Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
    Route::get('/statistics', [PageController::class, 'statistics'])->name('statistics');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');

    /*
     * API internal Flowlist untuk user yang sudah login.
     */
    Route::prefix('flowlist-api')->name('flowlist.api.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'data'])->name('dashboard');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/task-detail', [TaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/store', [TaskController::class, 'store'])->name('tasks.store');
        Route::post('/tasks/update', [TaskController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/complete', [TaskController::class, 'complete'])->name('tasks.complete');
        Route::post('/tasks/delete', [TaskController::class, 'destroy'])->name('tasks.destroy');

        Route::get('/calendar', [TaskController::class, 'calendar'])->name('calendar');
        Route::get('/statistics', [StatisticsController::class, 'data'])->name('statistics');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    /*
     * Halaman admin.
     * Semua route admin wajib login dan wajib role admin.
     */
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(EnsureUserIsAdmin::class)
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');
        });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});