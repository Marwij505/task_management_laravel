<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTaskController;
use App\Http\Controllers\Admin\AdminStatisticsController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureLoginSessionStillValid;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserCanAccessUserPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home Route
|--------------------------------------------------------------------------
| The first page follows the authenticated user's role.
*/
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()?->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
| Only guests can access login, register, and forgot password pages.
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
| All routes inside this group require login and valid session.
*/
Route::middleware(['auth', EnsureLoginSessionStillValid::class])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Regular User Area
    |--------------------------------------------------------------------------
    | Only users with role "user" can access these pages and APIs.
    */
    Route::middleware(EnsureUserCanAccessUserPage::class)->group(function () {
        Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
        Route::get('/tasks', [PageController::class, 'taskList'])->name('tasks.index');
        Route::get('/tasks/create', [PageController::class, 'createTask'])->name('tasks.create');
        Route::get('/task-detail', [PageController::class, 'taskDetail'])->name('tasks.detail');
        Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
        Route::get('/statistics', [PageController::class, 'statistics'])->name('statistics');
        Route::get('/profile', [PageController::class, 'profile'])->name('profile');

        /*
         * Flowlist internal API for regular users.
         * Admin API will be separated later.
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
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Area
    |--------------------------------------------------------------------------
    | Only users with role "admin" can access these pages.
    */
    Route::prefix('admin')
    ->name('admin.')
    ->middleware(EnsureUserIsAdmin::class)
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
         * User Management Routes.
         * Semua route ini hanya boleh diakses admin.
         */
        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::post('/users', [AdminUserController::class, 'store'])
            ->name('users.store');

        Route::patch('/users/{user}', [AdminUserController::class, 'update'])
            ->name('users.update');

        Route::patch('/users/{user}/password', [AdminUserController::class, 'resetPassword'])
            ->name('users.password');

        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])
            ->name('users.destroy');

        /*
        * All Task Management Routes.
        * Semua route ini hanya untuk admin.
        */
        Route::get('/tasks', [AdminTaskController::class, 'index'])
            ->name('tasks.index');

        Route::post('/tasks', [AdminTaskController::class, 'store'])
            ->name('tasks.store');

        Route::patch('/tasks/{task}', [AdminTaskController::class, 'update'])
            ->name('tasks.update');

        Route::patch('/tasks/{task}/complete', [AdminTaskController::class, 'complete'])
            ->name('tasks.complete');

        Route::delete('/tasks/{task}', [AdminTaskController::class, 'destroy'])
            ->name('tasks.destroy');

        /*
        * Global Statistics Route.
        * Halaman ini hanya untuk admin.
        */
        Route::get('/statistics', [AdminStatisticsController::class, 'index'])
            ->name('statistics.index');  
        
        /*
        * Activity Logs Route.
        * Halaman ini hanya untuk admin.
        */
        Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])
            ->name('logs.index');
        });
    /*
     * Logout is available for both admin and regular user.
     */
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});