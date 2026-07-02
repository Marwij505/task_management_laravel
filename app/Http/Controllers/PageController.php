<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function dashboard(): View
    {
        return view('app.dashboard');
    }

    public function taskList(): View
    {
        return view('app.task-list');
    }

    public function createTask(): View
    {
        return view('app.create-task');
    }

    public function taskDetail(): View
    {
        return view('app.task-detail');
    }

    public function calendar(): View
    {
        return view('app.calendar');
    }

    public function statistics(): View
    {
        return view('app.statistics');
    }

    public function profile(): View
    {
        return view('app.profile');
    }
}
