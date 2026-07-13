<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Flowlist - Statistics</title>
    <link rel="stylesheet" href="{{ asset('assets/css/statistics.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"rel="stylesheet"/>
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="brand-logo">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </div>
                    <h1>Flowlist</h1>
                </div>

                <nav class="sidebar-menu">
                    <a href="{{ route('dashboard') }}" class="menu-item">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('tasks.index') }}" class="menu-item">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Task List</span>
                    </a>

                    <a href="{{ route('tasks.create') }}" class="menu-item">
                        <i class="fa-solid fa-circle-plus"></i>
                        <span>Create Task</span>
                    </a>

                    <a href="{{ route('calendar') }}" class="menu-item">
                        <i class="fa-regular fa-calendar"></i>
                        <span>Calendar</span>
                    </a>

                    <a href="{{ route('statistics') }}" class="menu-item active">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Statistics</span>
                    </a>

                    <a href="{{ route('profile') }}" class="menu-item">
                        <i class="fa-regular fa-user"></i>
                        <span>Profile</span>
                    </a>

                    @if(auth()->user()?->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="menu-item admin-return-menu">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Admin Page</span>
                        </a>
                    @endif
                </nav>
            </div>

            <div class="sidebar-bottom">
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
                    @csrf
                </form>
                <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <h2>Statistics &amp; Analytics</h2>
                    <p>Charts of productivity, completed vs pending tasks</p>
                </div>
            </header>

            <!-- Stats Cards -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Completion Rate</h4>
                        <h3 id="completionRate">-</h3>
                        <small id="completionRateNote">- from last week</small>
                    </div>
                    <div class="stat-icon blue">
                        <span>◎</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Avg. Tasks/Day</h4>
                        <h3 id="avgTasksDay">-</h3>
                        <small id="avgTasksDayNote">- from last week</small>
                    </div>
                    <div class="stat-icon green">
                        <span>↗</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Tasks Completed</h4>
                        <h3 id="tasksCompleted">-</h3>
                        <small id="tasksCompletedNote">This month</small>
                    </div>
                    <div class="stat-icon purple">
                        <span>✓</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Avg. Completion Time</h4>
                        <h3 id="avgCompletionTime">-</h3>
                        <small id="avgCompletionTimeNote">- improvement</small>
                    </div>
                    <div class="stat-icon orange">
                        <span>◔</span>
                    </div>
                </div>
            </section>

            <div id="statisticsMessage" class="statistics-message">
                Loading statistics...
            </div>

            <!-- Charts Row 1 -->
            <section class="charts-grid">
                <article class="chart-card">
                    <div class="section-header">
                        <h3>Weekly Tasks: Completed vs Pending</h3>
                    </div>
                    <div class="chart-box">
                        <canvas id="weeklyChart" width="700" height="380"></canvas>
                    </div>
                </article>

                <article class="chart-card">
                    <div class="section-header">
                        <h3>Monthly Productivity Trend</h3>
                    </div>
                    <div class="chart-box">
                        <canvas id="monthlyChart" width="700" height="380"></canvas>
                    </div>
                </article>
            </section>

            <!-- Charts Row 2 -->
            <section class="charts-grid">
                <article class="chart-card">
                    <div class="section-header">
                        <h3>Tasks by Priority</h3>
                    </div>
                    <div class="chart-box">
                        <canvas id="priorityChart" width="700" height="380"></canvas>
                    </div>
                </article>

                <article class="chart-card">
                    <div class="section-header">
                        <h3>Tasks by Category</h3>
                    </div>
                    <div class="chart-box">
                        <canvas id="categoryChart" width="700" height="380"></canvas>
                    </div>
                </article>
            </section>
        </main>
    </div>

    @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/statistics.js') }}"></script>

</body>
</html>