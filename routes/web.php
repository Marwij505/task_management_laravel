<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\EnsureLoginSessionStillValid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

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

Route::middleware(['auth', EnsureLoginSessionStillValid::class])->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/tasks', [PageController::class, 'taskList'])->name('tasks.index');
    Route::get('/tasks/create', [PageController::class, 'createTask'])->name('tasks.create');
    Route::get('/task-detail', [PageController::class, 'taskDetail'])->name('tasks.detail');
    Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
    Route::get('/statistics', [PageController::class, 'statistics'])->name('statistics');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');

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

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});