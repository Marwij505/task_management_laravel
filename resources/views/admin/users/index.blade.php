<!DOCTYPE html>
<html lang="en" data-theme-setting="Light" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Flowlist User Management</title>

    <!-- Reuse regular dashboard style so admin pages stay consistent. -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />

    <!-- Admin-specific styling. -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-users.css') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <div class="dashboard-container">
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
                    <a href="{{ route('admin.dashboard') }}" class="menu-item">
                        <i class="fa-solid fa-table-columns"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="menu-item active">
                        <i class="fa-solid fa-users"></i>
                        <span>User Management</span>
                    </a>

                    <a href="{{ route('admin.tasks.index') }}" class="menu-item">
                        <i class="fa-solid fa-list-check"></i>
                        <span>All Tasks</span>
                    </a>

                    <!-- These modules will be connected in the next stages. -->
                    <button type="button" class="menu-item menu-button js-coming-soon" data-feature="Global Statistics">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Global Statistics</span>
                    </button>

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
                    <h2>User Management</h2>
                    <p>Manage accounts, roles, access level, and user credentials</p>
                </div>

                <div class="topbar-right">
                    <a href="#createUserForm" class="new-task-btn">
                        <span>Create User</span>
                    </a>
                </div>
            </header>

            <!-- Success message after create, update, reset password, or delete. -->
            @if(session('success'))
                <div class="admin-alert success">
                    <i class="fa-regular fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error message for protected actions. -->
            @if(session('error'))
                <div class="admin-alert error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Validation error message. -->
            @if($errors->any())
                <div class="admin-alert error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Please check your input.</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <!-- Summary Cards -->
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
                        <h4>Current Page</h4>
                        <h3>{{ $users->count() }}</h3>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                </div>
            </section>

            <section class="admin-users-layout">
                <!-- User List Card -->
                <div class="recent-tasks-card admin-users-card">
                    <div class="section-header admin-users-header">
                        <div>
                            <h3>All Users</h3>
                            <p>Search, filter, edit, reset password, or remove users</p>
                        </div>
                    </div>

                    <!-- Search and Role Filter -->
                    <form method="GET" action="{{ route('admin.users.index') }}" class="admin-filter-form">
                        <div class="form-field">
                            <label for="search">Search</label>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search by name, username, or email"
                            />
                        </div>

                        <div class="form-field">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="admin" @selected($selectedRole === 'admin')>Admin</option>
                                <option value="user" @selected($selectedRole === 'user')>User</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="admin-primary-btn">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Search
                            </button>

                            <a href="{{ route('admin.users.index') }}" class="admin-secondary-btn">
                                Reset
                            </a>
                        </div>
                    </form>

                    <!-- Users -->
                    <div class="admin-user-list">
                        @forelse($users as $user)
                            <article class="admin-user-row">
                                <div class="admin-user-summary">
                                    <div class="admin-user-avatar">
                                        {{ strtoupper(substr($user->full_name ?: $user->name ?: $user->username ?: 'U', 0, 1)) }}
                                    </div>

                                    <div>
                                        <h4>{{ $user->full_name ?: $user->name ?: $user->username }}</h4>
                                        <p>{{ $user->email }}</p>

                                        <div class="admin-user-tags">
                                            <span class="role-badge {{ $user->role }}">
                                                {{ ucfirst($user->role) }}
                                            </span>

                                            <span class="badge category category-default">
                                                {{ $user->username ?: 'No username' }}
                                            </span>

                                            <span class="badge priority low">
                                                {{ $user->tasks_count }} tasks
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Manage panel. Details keeps the page clean until admin needs to edit. -->
                                <details class="admin-user-details">
                                    <summary>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Manage
                                    </summary>

                                    <div class="admin-user-panel">
                                        <!-- Edit User -->
                                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form-block">
                                            @csrf
                                            @method('PATCH')

                                            <h5>Edit User Information</h5>

                                            <div class="admin-form-grid">
                                                <div class="form-field">
                                                    <label>Full Name</label>
                                                    <input
                                                        type="text"
                                                        name="full_name"
                                                        value="{{ old('full_name', $user->full_name ?: $user->name) }}"
                                                        required
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Username</label>
                                                    <input
                                                        type="text"
                                                        name="username"
                                                        value="{{ old('username', $user->username) }}"
                                                        required
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Email</label>
                                                    <input
                                                        type="email"
                                                        name="email"
                                                        value="{{ old('email', $user->email) }}"
                                                        required
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Role</label>
                                                    <select name="role" required>
                                                        <option value="user" @selected($user->role === 'user')>User</option>
                                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <button type="submit" class="admin-primary-btn">
                                                Save Changes
                                            </button>
                                        </form>

                                        <!-- Reset Password -->
                                        <form method="POST" action="{{ route('admin.users.password', $user) }}" class="admin-form-block">
                                            @csrf
                                            @method('PATCH')

                                            <h5>Reset Password</h5>

                                            <div class="admin-form-grid">
                                                <div class="form-field">
                                                    <label>New Password</label>
                                                    <input
                                                        type="password"
                                                        name="password"
                                                        placeholder="New password"
                                                        required
                                                    >
                                                </div>

                                                <div class="form-field">
                                                    <label>Confirm Password</label>
                                                    <input
                                                        type="password"
                                                        name="password_confirmation"
                                                        placeholder="Confirm password"
                                                        required
                                                    >
                                                </div>
                                            </div>

                                            <button type="submit" class="admin-secondary-btn">
                                                Reset Password
                                            </button>
                                        </form>

                                        <!-- Delete User -->
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.destroy', $user) }}"
                                            class="admin-delete-form"
                                            data-confirm="Delete this user? All related tasks will also be removed."
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="admin-danger-btn">
                                                <i class="fa-solid fa-trash"></i>
                                                Delete User
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </article>
                        @empty
                            <div class="empty-state">
                                No users found.
                            </div>
                        @endforelse
                    </div>

                    <!-- Simple Pagination -->
                    @if($users->hasPages())
                        <div class="admin-pagination">
                            @if($users->previousPageUrl())
                                <a href="{{ $users->previousPageUrl() }}">Previous</a>
                            @else
                                <span>Previous</span>
                            @endif

                            <strong>
                                Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                            </strong>

                            @if($users->nextPageUrl())
                                <a href="{{ $users->nextPageUrl() }}">Next</a>
                            @else
                                <span>Next</span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Create User Card -->
                <aside class="deadlines-card admin-create-card" id="createUserForm">
                    <div class="section-header">
                        <h3>Create User</h3>
                        <p>Create a new admin or regular user account</p>
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="admin-create-form">
                        @csrf

                        <div class="form-field">
                            <label>Full Name</label>
                            <input
                                type="text"
                                name="full_name"
                                value="{{ old('full_name') }}"
                                placeholder="Full name"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label>Username</label>
                            <input
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="Username"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label>Email</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Email address"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="user" @selected(old('role') === 'user')>User</option>
                                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Password</label>
                            <input
                                type="password"
                                name="password"
                                placeholder="Password"
                                required
                            >
                        </div>

                        <div class="form-field">
                            <label>Confirm Password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                placeholder="Confirm password"
                                required
                            >
                        </div>

                        <button type="submit" class="new-task-btn admin-create-btn">
                            Create User
                        </button>
                    </form>
                </aside>
            </section>
        </main>
    </div>

    <div class="admin-toast" id="adminToast">
        Feature will be available in the next admin stage.
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/admin-users.js') }}"></script>
</body>
</html>