<!DOCTYPE html>
<html lang="en" data-theme-setting="Light" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Flowlist Activity Logs</title>

    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-logs.css') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <div class="dashboard-container">
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

                    <a href="{{ route('admin.statistics.index') }}" class="menu-item">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Global Statistics</span>
                    </a>

                    <a href="{{ route('admin.logs.index') }}" class="menu-item active">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Activity Logs</span>
                    </a>
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

        <main class="main-content">
            <header class="topbar">
                <div class="topbar-left">
                    <h2>Activity Logs</h2>
                    <p>Track authentication, admin actions, user changes, and task management activity</p>
                </div>

                <div class="topbar-right">
                    <button
                        type="button"
                        class="new-task-btn js-clear-filters"
                        data-reset-url="{{ route('admin.logs.index') }}"
                    >
                        <span>Clear Filters</span>
                    </button>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Logs</h4>
                        <h3>{{ $stats['totalLogs'] }}</h3>
                    </div>

                    <div class="stat-icon blue">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Today</h4>
                        <h3>{{ $stats['todayLogs'] }}</h3>
                    </div>

                    <div class="stat-icon green">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Admin Actions</h4>
                        <h3>{{ $stats['adminLogs'] }}</h3>
                    </div>

                    <div class="stat-icon purple">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>User/Auth Events</h4>
                        <h3>{{ $stats['userLogs'] + $stats['authLogs'] }}</h3>
                    </div>

                    <div class="stat-icon orange">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                </div>
            </section>

            <section class="recent-tasks-card">
                <div class="section-header">
                    <h3>System Activity</h3>
                    <p>Search and filter recorded system activity</p>
                </div>

                <form method="GET" action="{{ route('admin.logs.index') }}" class="admin-log-filter">
                    <div class="form-field">
                        <label>Search</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Search description, actor, module, or action"
                        >
                    </div>

                    <div class="form-field">
                        <label>Module</label>
                        <select name="module">
                            <option value="">All Modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" @selected($filters['module'] === $module)>
                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Action</label>
                        <select name="action">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" @selected($filters['action'] === $action)>
                                    {{ ucwords(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Actor</label>
                        <select name="user_id">
                            <option value="">All Actors</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>
                                    {{ $user->full_name ?: $user->name ?: $user->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Date</label>
                        <input type="date" name="date" value="{{ $filters['date'] }}">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="admin-primary-btn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Search
                        </button>

                        <a href="{{ route('admin.logs.index') }}" class="admin-secondary-btn">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="admin-log-list">
                    @forelse($logs as $log)
                        <article class="admin-log-row">
                            <div class="admin-log-icon {{ $log->module }}">
                                @if($log->module === 'auth')
                                    <i class="fa-solid fa-right-to-bracket"></i>
                                @elseif($log->module === 'admin_users')
                                    <i class="fa-solid fa-user-shield"></i>
                                @elseif($log->module === 'admin_tasks')
                                    <i class="fa-solid fa-list-check"></i>
                                @elseif($log->module === 'user_tasks')
                                    <i class="fa-regular fa-square-check"></i>
                                @elseif($log->module === 'profile')
                                    <i class="fa-regular fa-user"></i>
                                @else
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                @endif
                            </div>

                            <div class="admin-log-main">
                                <div class="admin-log-header">
                                    <div>
                                        <h4>{{ $log->description }}</h4>

                                        <p>
                                            Actor:
                                            <strong>
                                                {{ $log->actor?->full_name
                                                ?: $log->actor?->name
                                                ?: $log->actor?->username
                                                ?: 'System / Guest' }}
                                            </strong>
                                        </p>
                                    </div>

                                    <span class="admin-log-time">
                                        {{ $log->created_at?->format('d M Y, H:i') }}
                                    </span>
                                </div>

                                <div class="admin-log-tags">
                                    <span class="log-badge module">
                                        {{ ucwords(str_replace('_', ' ', $log->module)) }}
                                    </span>

                                    <span class="log-badge action">
                                        {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                    </span>

                                    @if($log->targetUser)
                                        <span class="log-badge target">
                                            Target: {{ $log->targetUser->email }}
                                        </span>
                                    @endif

                                    @if($log->ip_address)
                                        <span class="log-badge ip">
                                            IP: {{ $log->ip_address }}
                                        </span>
                                    @endif
                                </div>

                                <details class="admin-log-details">
                                    <summary>
                                        <i class="fa-solid fa-code"></i>
                                        View Details
                                    </summary>

                                    <div class="admin-log-json">
                                        <strong>Properties</strong>
                                        <pre>{{ json_encode(
                                            $log->properties ?? [],
                                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                                        ) }}</pre>

                                        <strong>User Agent</strong>
                                        <p>{{ $log->user_agent ?: 'No user agent recorded.' }}</p>
                                    </div>
                                </details>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            No activity logs found.
                        </div>
                    @endforelse
                </div>

                @if($logs->hasPages())
                    <div class="admin-pagination">
                        @if($logs->previousPageUrl())
                            <a href="{{ $logs->previousPageUrl() }}">Previous</a>
                        @else
                            <span>Previous</span>
                        @endif

                        <strong>
                            Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                        </strong>

                        @if($logs->nextPageUrl())
                            <a href="{{ $logs->nextPageUrl() }}">Next</a>
                        @else
                            <span>Next</span>
                        @endif
                    </div>
                @endif
            </section>
        </main>
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/admin-logs.js') }}"></script>
</body>
</html>