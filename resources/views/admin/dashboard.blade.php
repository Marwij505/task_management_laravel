<!DOCTYPE html>
<html lang="en" data-theme-setting="Light" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Flowlist Admin Dashboard</title>

    <!-- Reuse the regular user dashboard style as the main design base. -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />

    <!-- Admin-specific adjustment only. -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <div class="dashboard-container admin-dashboard-container">
        <!-- Admin Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="brand-logo">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </div>
                    <h1>Flowlist</h1>
                </div>

                <nav class="sidebar-menu">
                    <a href="{{ route('admin.dashboard') }}" class="menu-item active">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="menu-item">
                        <i class="fa-solid fa-users"></i>
                        <span>User Management</span>
                    </a>

                    <a href="{{ route('admin.tasks.index') }}" class="menu-item">
                        <i class="fa-solid fa-list-check"></i>
                        <span>All Tasks</span>
                    </a>

                    <a href="{{ route('admin.statistics.index') }}" class="menu-item">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Global Statistics</span>
                    </a>

                    <button type="button" class="menu-item menu-button js-coming-soon" data-feature="Activity Logs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Activity Logs</span>
                    </button>
                </nav>
            </div>

            <div class="sidebar-bottom">
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
                    @csrf
                </form>

                <a href="{{ route('logout') }}" class="logout-btn"
                   onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <h2>Admin Dashboard</h2>
                    <p>Monitor users, tasks, deadlines, and system activity from one place</p>
                </div>

                <div class="topbar-right">
                    <div class="admin-profile-card">
                        <div class="admin-avatar">
                            {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->username ?? 'A', 0, 1)) }}
                        </div>

                        <div>
                            <h4>{{ auth()->user()->full_name ?? auth()->user()->username }}</h4>
                            <p>{{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stats Cards -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Users</h4>
                        <h3>{{ $totalUsers }}</h3>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Admins</h4>
                        <h3>{{ $totalAdmins }}</h3>
                    </div>
                    <div class="stat-icon green">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Regular Users</h4>
                        <h3>{{ $totalRegularUsers }}</h3>
                    </div>
                    <div class="stat-icon purple">
                        <i class="fa-regular fa-user"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Tasks</h4>
                        <h3>{{ $totalTasks }}</h3>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Completed</h4>
                        <h3>{{ $completedTasks }}</h3>
                    </div>
                    <div class="stat-icon green">
                        <i class="fa-regular fa-circle-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>In Progress</h4>
                        <h3>{{ $inProgressTasks }}</h3>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Due Today</h4>
                        <h3>{{ $dueTodayTasks }}</h3>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Overdue</h4>
                        <h3>{{ $overdueTasks }}</h3>
                    </div>
                    <div class="stat-icon red">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </div>
            </section>

            <!-- Main Dashboard Content -->
            <section class="dashboard-content admin-dashboard-content">
                <div class="recent-tasks-card">
                    <div class="section-header admin-section-header">
                        <div>
                            <h3>Recent Tasks</h3>
                            <p>Latest tasks created by all users</p>
                        </div>

                        <a href="{{ route('admin.tasks.index') }}" class="new-task-btn admin-small-btn">
                            View All
                        </a>
                    </div>

                    <div class="task-list">
                        @forelse($recentTasks as $task)
                            @php
                                /*
                                 * User dashboard CSS uses "progress" for in-progress badge.
                                 * This keeps admin badge colors identical to the user dashboard.
                                 */
                                $statusClass = $task['status'] === 'in-progress'
                                    ? 'progress'
                                    : $task['status'];
                            @endphp

                            <div class="task-item">
                                <div class="task-top">
                                    <h4>{{ $task['title'] }}</h4>
                                </div>

                                <div class="task-tags">
                                    <span class="badge status {{ $statusClass }}">
                                        {{ $task['status_label'] }}
                                    </span>

                                    <span class="badge priority {{ $task['priority'] }}">
                                        {{ ucfirst($task['priority']) }}
                                    </span>

                                    <span class="badge category category-default">
                                        Owner: {{ $task['owner'] }}
                                    </span>
                                </div>

                                <div class="task-date">
                                    <i class="fa-regular fa-calendar"></i>
                                    <span>{{ $task['deadline'] }}</span>
                                </div>

                                <div class="task-progress">
                                    <div class="progress-header">
                                        <span>Progress</span>
                                        <span>{{ $task['progress'] }}%</span>
                                    </div>

                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ $task['progress'] }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">No recent tasks available</div>
                        @endforelse
                    </div>
                </div>

                <div class="admin-side-column">
                    <div class="deadlines-card">
                        <div class="section-header">
                            <h3>System Progress</h3>
                            <p>Global task completion rate</p>
                        </div>

                        <div class="admin-progress-box">
                            <h2>{{ $completionRate }}%</h2>
                            <p>Completion Rate</p>

                            <div class="progress-bar">
                                <div class="progress-fill accent" style="width: {{ $completionRate }}%;"></div>
                            </div>
                        </div>

                        <div class="admin-mini-list">
                            <div>
                                <span>High Priority</span>
                                <strong>{{ $priorityBreakdown['high'] }}</strong>
                            </div>

                            <div>
                                <span>Medium Priority</span>
                                <strong>{{ $priorityBreakdown['medium'] }}</strong>
                            </div>

                            <div>
                                <span>Low Priority</span>
                                <strong>{{ $priorityBreakdown['low'] }}</strong>
                            </div>

                            <div>
                                <span>Categories</span>
                                <strong>{{ $totalCategories }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="deadlines-card">
                        <div class="section-header admin-section-header">
                            <div>
                                <h3>Recent Users</h3>
                                <p>Newest registered accounts</p>
                            </div>

                            <a href="{{ route('admin.users.index') }}" class="admin-link-btn">
                                Manage
                            </a>
                        </div>

                        <div class="admin-user-list">
                            @forelse($recentUsers as $user)
                                <div class="admin-user-item">
                                    <div class="admin-user-avatar">
                                        {{ strtoupper(substr($user['name'] ?: $user['username'], 0, 1)) }}
                                    </div>

                                    <div class="admin-user-main">
                                        <h4>{{ $user['name'] }}</h4>
                                        <p>{{ $user['email'] }}</p>
                                    </div>

                                    <div class="admin-user-meta">
                                        <span class="role-badge {{ $user['role'] }}">{{ ucfirst($user['role']) }}</span>
                                        <small>{{ $user['tasks_count'] }} tasks</small>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">No users available</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-bottom-grid">
                <div class="recent-tasks-card">
                    <div class="section-header">
                        <h3>Quick Actions</h3>
                        <p>Admin shortcuts for upcoming modules</p>
                    </div>

                    <div class="admin-action-grid">
                        <a href="{{ route('admin.users.index') }}" class="admin-action-card">
                            <i class="fa-solid fa-users"></i>
                            <div>
                                <strong>User Management</strong>
                                <span>Manage roles and user accounts</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.tasks.index') }}" class="admin-action-card">
                            <i class="fa-solid fa-list-check"></i>
                            <div>
                                <strong>All Task Management</strong>
                                <span>Monitor every user task</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.statistics.index') }}" class="admin-action-card">
                            <i class="fa-solid fa-chart-column"></i>
                            <div>
                                <strong>Global Statistics</strong>
                                <span>View system-wide performance</span>
                            </div>
                        </a>

                        <button type="button" class="admin-action-card js-coming-soon" data-feature="Activity Logs">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <div>
                                <strong>Activity Logs</strong>
                                <span>Track user and admin activity</span>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="deadlines-card">
                    <div class="section-header">
                        <h3>Activity Preview</h3>
                        <p>System activity logs will appear here later</p>
                    </div>

                    <div class="admin-activity-box">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <strong>Coming in Stage 7</strong>
                        <span>Task changes, user updates, admin actions, and login events will be tracked here.</span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div class="admin-toast" id="adminToast">
        Feature will be available in the next admin stage.
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/admin-dashboard.js') }}"></script>
</body>
</html>