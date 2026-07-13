<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Flowlist - Task List</title>
    <link rel="stylesheet" href="{{ asset('assets/css/task-list.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>

<body>
    <div class="tasklist-container">
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

                    <a href="{{ route('tasks.index') }}" class="menu-item active">
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

                    <a href="{{ route('statistics') }}" class="menu-item">
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
            <!-- Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <h2>Task List</h2>
                    <p>Display all tasks (filter, search, categories)</p>
                </div>
                <!-- New Task Button -->
                <div class="topbar-right">
                    <a href="{{ route('tasks.create') }}" class="view-btn">
                        <span>New Task</span>
                    </a>
                </div>
            </header>

            <!-- Filter Section -->
            <section class="filter-section">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="text"
                        id="searchTask"
                        name="searchTask"
                        placeholder="Search tasks..."
                    />
                </div>

                <div class="filter-box">
                    <select id="statusFilter" name="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="filter-box">
                    <select id="priorityFilter" name="priorityFilter">
                        <option value="">All Priority</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <div class="filter-box">
                    <select id="categoryFilter" name="categoryFilter">
                        <option value="">All Categories</option>
                    </select>
                </div>

                <div class="filter-box">
                    <select id="tagFilter" name="tagFilter">
                        <option value="">All Tags</option>
                    </select>
                </div>
            </section>

            <section class="list-summary">
                <div>
                    <h3>All Tasks</h3>
                    <p id="taskCountText">Loading tasks...</p>
                </div>

                <button type="button" id="clearFiltersBtn" class="clear-filter-btn">
                    Clear Filters
                </button>
            </section>

            <!-- Task Cards -->
            <section class="task-grid" id="taskGrid">
                <!-- 
                    Card task dari database akan dirender di sini.
                    Bisa lewat PHP, Flask/Jinja, atau JavaScript fetch API.
                -->
            </section>

            {{-- Footer Flowlist --}}
            @include('partials.app-footer')
        </main>
    </div>

    @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/task-list.js') }}"></script>
    
</body>
</html>