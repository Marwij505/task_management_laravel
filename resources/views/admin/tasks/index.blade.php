<!DOCTYPE html>
<html lang="en" data-theme-setting="Light" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Flowlist All Task Management</title>

    <!-- Shared dashboard styles. -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />

    <!-- Admin task styles. -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-tasks.css') }}" />

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

                    <a
                        href="{{ route('admin.tasks.index') }}"
                        class="menu-item active"
                    >
                        <i class="fa-solid fa-list-check"></i>
                        <span>All Tasks</span>
                    </a>

                    <a
                        href="{{ route('admin.statistics.index') }}"
                        class="menu-item"
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
                    <h2>All Task Management</h2>
                    <p>Monitor, create, edit, complete, and delete tasks from all users</p>
                </div>

                <div class="topbar-right">
                    <a href="#createTaskForm" class="new-task-btn">
                        <span>Create Task</span>
                    </a>
                </div>
            </header>

            <!-- Success Message -->
            @if(session('success'))
                <div class="admin-alert success">
                    <i class="fa-regular fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Validation Error -->
            @if($errors->any())
                <div class="admin-alert error">
                    <i class="fa-solid fa-triangle-exclamation"></i>

                    <div>
                        <strong>Please check your input.</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <!-- =====================================================
                 TASK SUMMARY
                 ===================================================== -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Tasks</h4>
                        <h3>{{ $stats['totalTasks'] }}</h3>
                    </div>

                    <div class="stat-icon blue">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Completed</h4>
                        <h3>{{ $stats['completedTasks'] }}</h3>
                    </div>

                    <div class="stat-icon green">
                        <i class="fa-regular fa-circle-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Due Today</h4>
                        <h3>{{ $stats['dueTodayTasks'] }}</h3>
                    </div>

                    <div class="stat-icon orange">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Overdue</h4>
                        <h3>{{ $stats['overdueTasks'] }}</h3>
                    </div>

                    <div class="stat-icon red">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 TASK MANAGEMENT LAYOUT
                 ===================================================== -->
            <section class="admin-tasks-layout">
                <!-- Task List -->
                <div class="recent-tasks-card admin-tasks-card">
                    <div class="section-header">
                        <h3>All Tasks</h3>
                        <p>Search, filter, edit, complete, or remove tasks from every user</p>
                    </div>

                    <!-- Task Filters -->
                    <form
                        method="GET"
                        action="{{ route('admin.tasks.index') }}"
                        class="admin-task-filter"
                    >
                        <div class="form-field">
                            <label>Search</label>

                            <input
                                type="text"
                                name="search"
                                value="{{ $filters['search'] }}"
                                placeholder="Search by title, owner, category, assignee, or tag"
                            >
                        </div>

                        <div class="form-field">
                            <label>Owner</label>

                            <select name="user_id">
                                <option value="">All Users</option>

                                @foreach($users as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        @selected(
                                            (string) $filters['user_id']
                                            === (string) $user->id
                                        )
                                    >
                                        {{
                                            $user->full_name
                                                ?: $user->name
                                                ?: $user->username
                                        }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Status</label>

                            <select name="status">
                                <option value="">All Status</option>

                                <option
                                    value="pending"
                                    @selected($filters['status'] === 'pending')
                                >
                                    Pending
                                </option>

                                <option
                                    value="in-progress"
                                    @selected($filters['status'] === 'in-progress')
                                >
                                    In Progress
                                </option>

                                <option
                                    value="completed"
                                    @selected($filters['status'] === 'completed')
                                >
                                    Completed
                                </option>

                                <option
                                    value="due-today"
                                    @selected($filters['status'] === 'due-today')
                                >
                                    Due Today
                                </option>

                                <option
                                    value="overdue"
                                    @selected($filters['status'] === 'overdue')
                                >
                                    Overdue
                                </option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Priority</label>

                            <select name="priority">
                                <option value="">All Priority</option>

                                <option
                                    value="high"
                                    @selected($filters['priority'] === 'high')
                                >
                                    High
                                </option>

                                <option
                                    value="medium"
                                    @selected($filters['priority'] === 'medium')
                                >
                                    Medium
                                </option>

                                <option
                                    value="low"
                                    @selected($filters['priority'] === 'low')
                                >
                                    Low
                                </option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="admin-primary-btn">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Search
                            </button>

                            <a
                                href="{{ route('admin.tasks.index') }}"
                                class="admin-secondary-btn"
                            >
                                Reset
                            </a>
                        </div>
                    </form>

                    <!-- Task Records -->
                    <div class="admin-task-list">
                        @forelse($tasks as $task)
                            <article class="admin-task-row">
                                <div class="task-top">
                                    <h4>{{ $task['title'] }}</h4>
                                </div>

                                <div class="task-tags">
                                    <span
                                        class="badge status {{ $task['status_class'] }}"
                                    >
                                        {{ $task['status_label'] }}
                                    </span>

                                    <span
                                        class="badge priority {{ $task['priority'] }}"
                                    >
                                        {{ ucfirst($task['priority']) }}
                                    </span>

                                    <span class="badge category category-default">
                                        Owner: {{ $task['owner_name'] }}
                                    </span>

                                    @if($task['category'])
                                        <span class="badge category category-default">
                                            {{ $task['category'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="admin-task-meta">
                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $task['deadline_label'] }}
                                    </span>

                                    <span>
                                        <i class="fa-regular fa-user"></i>
                                        {{ $task['assignee'] ?: 'No assignee' }}
                                    </span>
                                </div>

                                <div class="task-progress">
                                    <div class="progress-header">
                                        <span>Progress</span>
                                        <span>{{ $task['progress'] }}%</span>
                                    </div>

                                    <div class="progress-bar">
                                        <div
                                            class="progress-fill"
                                            style="width: {{ $task['progress'] }}%;"
                                        ></div>
                                    </div>
                                </div>

                                @if(count($task['tags']))
                                    <div class="admin-tag-list">
                                        @foreach($task['tags'] as $tag)
                                            <span>#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Task Management Panel -->
                                <details class="admin-task-details">
                                    <summary>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Manage
                                    </summary>

                                    <div class="admin-task-panel">
                                        <!-- Edit Task -->
                                        <form
                                            method="POST"
                                            action="{{ route('admin.tasks.update', $task['id']) }}"
                                            class="admin-form-block admin-task-form"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <h5>Edit Task Information</h5>

                                            <div class="admin-form-grid">
                                                <div class="form-field">
                                                    <label>Owner</label>

                                                    <select name="user_id" required>
                                                        @foreach($users as $user)
                                                            <option
                                                                value="{{ $user->id }}"
                                                                @selected(
                                                                    (int) $task['user_id']
                                                                    === (int) $user->id
                                                                )
                                                            >
                                                                {{
                                                                    $user->full_name
                                                                        ?: $user->name
                                                                        ?: $user->username
                                                                }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-field">
                                                    <label>Title</label>

                                                    <input
                                                        type="text"
                                                        name="title"
                                                        value="{{ $task['title'] }}"
                                                        required
                                                    >
                                                </div>

                                                <div class="form-field full">
                                                    <label>Description</label>

                                                    <textarea
                                                        name="description"
                                                        rows="3"
                                                    >{{ $task['description'] }}</textarea>
                                                </div>

                                                <div class="form-field">
                                                    <label>Status</label>

                                                    <select name="status" required>
                                                        <option
                                                            value="pending"
                                                            @selected($task['status'] === 'pending')
                                                        >
                                                            Pending
                                                        </option>

                                                        <option
                                                            value="in-progress"
                                                            @selected($task['status'] === 'in-progress')
                                                        >
                                                            In Progress
                                                        </option>

                                                        <option
                                                            value="completed"
                                                            @selected($task['status'] === 'completed')
                                                        >
                                                            Completed
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="form-field">
                                                    <label>Priority</label>

                                                    <select name="priority" required>
                                                        <option
                                                            value="low"
                                                            @selected($task['priority'] === 'low')
                                                        >
                                                            Low
                                                        </option>

                                                        <option
                                                            value="medium"
                                                            @selected($task['priority'] === 'medium')
                                                        >
                                                            Medium
                                                        </option>

                                                        <option
                                                            value="high"
                                                            @selected($task['priority'] === 'high')
                                                        >
                                                            High
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="form-field">
                                                    <label>Category</label>

                                                    <input
                                                        type="text"
                                                        name="category"
                                                        value="{{ $task['category'] }}"
                                                        placeholder="Category"
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Deadline</label>

                                                    <input
                                                        type="date"
                                                        name="deadline"
                                                        value="{{ $task['deadline_input'] }}"
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Manual Progress</label>

                                                    <input
                                                        type="number"
                                                        name="progress"
                                                        value="{{ $task['stored_progress'] }}"
                                                        min="0"
                                                        max="100"
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Assignee</label>

                                                    <input
                                                        type="text"
                                                        name="assignee"
                                                        value="{{ $task['assignee'] }}"
                                                        placeholder="Assignee"
                                                    >
                                                </div>

                                                <div class="form-field full">
                                                    <label>Tags</label>

                                                    <input
                                                        type="text"
                                                        name="tags"
                                                        value="{{ $task['tags_text'] }}"
                                                        placeholder="urgent, frontend, revision"
                                                    >
                                                </div>
                                            </div>

                                            <button
                                                type="submit"
                                                class="admin-primary-btn"
                                            >
                                                Save Changes
                                            </button>
                                        </form>

                                        <div class="admin-task-actions">
                                            <!-- Complete Task -->
                                            <form
                                                method="POST"
                                                action="{{ route('admin.tasks.complete', $task['id']) }}"
                                                class="admin-complete-form"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="admin-secondary-btn"
                                                >
                                                    <i class="fa-regular fa-circle-check"></i>
                                                    Mark Completed
                                                </button>
                                            </form>

                                            <!-- Delete Task -->
                                            <form
                                                method="POST"
                                                action="{{ route('admin.tasks.destroy', $task['id']) }}"
                                                class="admin-delete-form"
                                                data-confirm="Delete this task? This action cannot be undone."
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="admin-danger-btn"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                    Delete Task
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </details>
                            </article>
                        @empty
                            <div class="empty-state">
                                No tasks found.
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($tasks->hasPages())
                        <div class="admin-pagination">
                            @if($tasks->previousPageUrl())
                                <a href="{{ $tasks->previousPageUrl() }}">
                                    Previous
                                </a>
                            @else
                                <span>Previous</span>
                            @endif

                            <strong>
                                Page {{ $tasks->currentPage() }}
                                of {{ $tasks->lastPage() }}
                            </strong>

                            @if($tasks->nextPageUrl())
                                <a href="{{ $tasks->nextPageUrl() }}">
                                    Next
                                </a>
                            @else
                                <span>Next</span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- =================================================
                     CREATE TASK FORM
                     ================================================= -->
                <aside
                    class="deadlines-card admin-create-task-card"
                    id="createTaskForm"
                >
                    <div class="section-header">
                        <h3>Create Task</h3>
                        <p>Create a task and assign it to any user</p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.tasks.store') }}"
                        class="admin-create-task-form"
                    >
                        @csrf

                        <div class="form-field">
                            <label>Owner</label>

                            <select name="user_id" required>
                                <option value="">Select user</option>

                                @foreach($users as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        @selected(old('user_id') == $user->id)
                                    >
                                        {{
                                            $user->full_name
                                                ?: $user->name
                                                ?: $user->username
                                        }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Title</label>

                            <input
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="Task title"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label>Description</label>

                            <textarea
                                name="description"
                                rows="3"
                                placeholder="Task description"
                            >{{ old('description') }}</textarea>
                        </div>

                        <div class="form-field">
                            <label>Status</label>

                            <select name="status" required>
                                <option
                                    value="pending"
                                    @selected(old('status') === 'pending')
                                >
                                    Pending
                                </option>

                                <option
                                    value="in-progress"
                                    @selected(old('status') === 'in-progress')
                                >
                                    In Progress
                                </option>

                                <option
                                    value="completed"
                                    @selected(old('status') === 'completed')
                                >
                                    Completed
                                </option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Priority</label>

                            <select name="priority" required>
                                <option
                                    value="medium"
                                    @selected(old('priority') === 'medium')
                                >
                                    Medium
                                </option>

                                <option
                                    value="low"
                                    @selected(old('priority') === 'low')
                                >
                                    Low
                                </option>

                                <option
                                    value="high"
                                    @selected(old('priority') === 'high')
                                >
                                    High
                                </option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Category</label>

                            <input
                                type="text"
                                name="category"
                                value="{{ old('category') }}"
                                placeholder="Category"
                            >
                        </div>

                        <div class="form-field">
                            <label>Deadline</label>

                            <input
                                type="date"
                                name="deadline"
                                value="{{ old('deadline') }}"
                            >
                        </div>

                        <div class="form-field">
                            <label>Manual Progress</label>

                            <input
                                type="number"
                                name="progress"
                                value="{{ old('progress', 0) }}"
                                min="0"
                                max="100"
                            >
                        </div>

                        <div class="form-field">
                            <label>Assignee</label>

                            <input
                                type="text"
                                name="assignee"
                                value="{{ old('assignee') }}"
                                placeholder="Assignee"
                            >
                        </div>

                        <div class="form-field">
                            <label>Tags</label>

                            <input
                                type="text"
                                name="tags"
                                value="{{ old('tags') }}"
                                placeholder="urgent, frontend, revision"
                            >
                        </div>

                        <button
                            type="submit"
                            class="new-task-btn admin-create-task-btn"
                        >
                            Create Task
                        </button>
                    </form>
                </aside>
            </section>
        </main>
    </div>

    <!-- Toast is retained for future admin modules. -->
    <div class="admin-toast" id="adminToast">
        Feature will be available in the next admin stage.
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/admin-tasks.js') }}"></script>
</body>

</html>