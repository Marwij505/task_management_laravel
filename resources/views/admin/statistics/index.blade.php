<!DOCTYPE html>
<html lang="en" data-theme-setting="Light" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Flowlist Global Statistics</title>

    <!-- Shared dashboard styles. -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />

    <!-- Admin statistics styles. -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-statistics.css') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
</head>

<body>
    <div class="dashboard-container">
        <!-- =========================================================
             ADMIN SIDEBAR
             ========================================================= -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="brand-logo">
                        <i class="fa-solid fa-table-cells-large"></i>
                    </div>

                    <h1>Flowlist</h1>
                </div>

                <nav class="sidebar-menu">
                    <a href="{{ route('admin.dashboard') }}" class="menu-item">
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

                    <a
                        href="{{ route('admin.statistics.index') }}"
                        class="menu-item active"
                    >
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Global Statistics</span>
                    </a>

                    <!-- Babak 7 Activity Logs -->
                    <a href="{{ route('admin.logs.index') }}" class="menu-item">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Activity Logs</span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="menu-item">
                        <i class="fa-solid fa-user"></i>
                        <span>User Page</span>
                    </a>
                </nav>
            </div>

            <!-- Logout -->
            <div class="sidebar-bottom">
                <form
                    id="logoutForm"
                    method="POST"
                    action="{{ route('logout') }}"
                    style="display:none;"
                >
                    @csrf
                </form>

                <a
                    href="{{ route('logout') }}"
                    class="logout-btn"
                    onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- =========================================================
             MAIN CONTENT
             ========================================================= -->
        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <h2>Global Statistics</h2>
                    <p>Analyze system-wide users, tasks, completion rate, deadlines, and productivity</p>
                </div>

                <div class="topbar-right">
                    <button
                        type="button"
                        class="new-task-btn js-copy-report"
                    >
                        <span>Copy Summary</span>
                    </button>
                </div>
            </header>

            <!-- =====================================================
                 GLOBAL STATISTICS SUMMARY
                 ===================================================== -->
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
                        <h4>Completion Rate</h4>
                        <h3>{{ $completionRate }}%</h3>
                    </div>

                    <div class="stat-icon purple">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 DETAILED GLOBAL STATISTICS
                 ===================================================== -->
            <section class="admin-statistics-grid">
                <!-- Status Breakdown -->
                <div class="recent-tasks-card">
                    <div class="section-header">
                        <h3>Status Breakdown</h3>
                        <p>Task condition based on realistic deadline calculation</p>
                    </div>

                    <div class="stat-bar-list">
                        @foreach($statusBreakdown as $status => $count)
                            @php
                                /*
                                 * Calculate status percentage safely.
                                 */
                                $percentage = $totalTasks > 0
                                    ? (int) round(($count / $totalTasks) * 100)
                                    : 0;

                                /*
                                 * Existing CSS uses "progress"
                                 * for the in-progress status.
                                 */
                                $statusClass = $status === 'in-progress'
                                    ? 'progress'
                                    : $status;
                            @endphp

                            <div class="stat-bar-item">
                                <div class="stat-bar-header">
                                    <span class="badge status {{ $statusClass }}">
                                        {{ ucwords(str_replace('-', ' ', $status)) }}
                                    </span>

                                    <strong>{{ $count }} tasks</strong>
                                </div>

                                <div class="stat-track">
                                    <div
                                        class="stat-fill"
                                        style="width: {{ $percentage }}%;"
                                    ></div>
                                </div>

                                <small>{{ $percentage }}%</small>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Priority Breakdown -->
                <div class="deadlines-card">
                    <div class="section-header">
                        <h3>Priority Breakdown</h3>
                        <p>Task priority distribution</p>
                    </div>

                    <div class="stat-bar-list">
                        @foreach($priorityBreakdown as $priority => $count)
                            @php
                                $percentage = $totalTasks > 0
                                    ? (int) round(($count / $totalTasks) * 100)
                                    : 0;
                            @endphp

                            <div class="stat-bar-item">
                                <div class="stat-bar-header">
                                    <span class="badge priority {{ $priority }}">
                                        {{ ucfirst($priority) }}
                                    </span>

                                    <strong>{{ $count }} tasks</strong>
                                </div>

                                <div class="stat-track">
                                    <div
                                        class="stat-fill"
                                        style="width: {{ $percentage }}%;"
                                    ></div>
                                </div>

                                <small>{{ $percentage }}%</small>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="recent-tasks-card">
                    <div class="section-header">
                        <h3>Top Categories</h3>
                        <p>Most used task categories across all users</p>
                    </div>

                    <div class="category-list">
                        @forelse($categoryRanking as $category)
                            <div class="category-row">
                                <div>
                                    <strong>{{ $category['name'] }}</strong>
                                    <span>{{ $category['count'] }} tasks</span>
                                </div>

                                <div class="category-progress">
                                    <div class="stat-track">
                                        <div
                                            class="stat-fill"
                                            style="width: {{ $category['percentage'] }}%;"
                                        ></div>
                                    </div>

                                    <small>{{ $category['percentage'] }}%</small>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                No category data available.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Deadline Risk -->
                <div class="deadlines-card">
                    <div class="section-header">
                        <h3>Deadline Risk</h3>
                        <p>Deadline health across all tasks</p>
                    </div>

                    <div class="deadline-risk-grid">
                        <div>
                            <span>No Deadline</span>
                            <strong>{{ $deadlineRisk['noDeadline'] }}</strong>
                        </div>

                        <div>
                            <span>Future Deadline</span>
                            <strong>{{ $deadlineRisk['futureDeadline'] }}</strong>
                        </div>

                        <div>
                            <span>Due Today</span>
                            <strong>{{ $deadlineRisk['dueToday'] }}</strong>
                        </div>

                        <div>
                            <span>Overdue</span>
                            <strong>{{ $deadlineRisk['overdue'] }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Top Productive Users -->
                <div class="recent-tasks-card">
                    <div class="section-header">
                        <h3>Top Productive Users</h3>
                        <p>Users with the highest task activity</p>
                    </div>

                    <div class="top-user-list">
                        @forelse($topUsers as $user)
                            <div class="top-user-row">
                                <div class="top-user-main">
                                    <div class="admin-user-avatar">
                                        {{
                                            strtoupper(
                                                substr(
                                                    $user['name'] ?: 'U',
                                                    0,
                                                    1
                                                )
                                            )
                                        }}
                                    </div>

                                    <div>
                                        <h4>{{ $user['name'] }}</h4>
                                        <p>{{ $user['email'] }}</p>
                                    </div>
                                </div>

                                <div class="top-user-meta">
                                    <span class="role-badge {{ $user['role'] }}">
                                        {{ ucfirst($user['role']) }}
                                    </span>

                                    <strong>
                                        {{ $user['completion_rate'] }}%
                                    </strong>

                                    <small>
                                        {{ $user['completed_count'] }}
                                        of {{ $user['tasks_count'] }}
                                        completed
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                No user productivity data available.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Monthly Trend -->
                <div class="deadlines-card">
                    <div class="section-header">
                        <h3>Monthly Trend</h3>
                        <p>Created and completed tasks in the last 6 months</p>
                    </div>

                    <div class="monthly-trend-list">
                        @foreach($monthlyTrend as $month)
                            @php
                                /*
                                 * Use a minimum value of 1
                                 * to prevent division by zero.
                                 */
                                $maxValue = max(
                                    $month['created'],
                                    $month['completed'],
                                    1
                                );

                                $createdWidth = (int) round(
                                    ($month['created'] / $maxValue) * 100
                                );

                                $completedWidth = (int) round(
                                    ($month['completed'] / $maxValue) * 100
                                );
                            @endphp

                            <div class="month-row">
                                <strong>{{ $month['label'] }}</strong>

                                <div class="month-bars">
                                    <div>
                                        <span>
                                            Created: {{ $month['created'] }}
                                        </span>

                                        <div class="stat-track">
                                            <div
                                                class="stat-fill"
                                                style="width: {{ $createdWidth }}%;"
                                            ></div>
                                        </div>
                                    </div>

                                    <div>
                                        <span>
                                            Completed: {{ $month['completed'] }}
                                        </span>

                                        <div class="stat-track muted">
                                            <div
                                                class="stat-fill muted"
                                                style="width: {{ $completedWidth }}%;"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!--
                Hidden text used by admin-statistics.js
                when the admin clicks Copy Summary.
            -->
            <textarea
                id="statisticsReportText"
                class="hidden-report"
                readonly
            >Flowlist Global Statistics Summary
                Total Users: {{ $totalUsers }}
                Admins: {{ $totalAdmins }}
                Regular Users: {{ $totalRegularUsers }}
                Total Tasks: {{ $totalTasks }}
                Completed Tasks: {{ $completedTasks }}
                Completion Rate: {{ $completionRate }}%
                Overdue Tasks: {{ $statusBreakdown['overdue'] }}
                Due Today Tasks: {{ $statusBreakdown['due-today'] }}</textarea>

                {{-- Footer Flowlist --}}
                @include('partials.app-footer')
        </main>
    </div>

    <!-- Copy Summary Toast -->
    <div class="admin-toast" id="adminToast">
        Summary copied successfully.
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/admin-statistics.js') }}"></script>
</body>

</html>