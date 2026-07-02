<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Flowlist Dashboard</title>
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
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
          <a href="{{ route('dashboard') }}" class="menu-item active">
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

          <a href="{{ route('statistics') }}" class="menu-item">
            <i class="fa-solid fa-chart-column"></i>
            <span>Statistics</span>
          </a>

          <a href="{{ route('profile') }}" class="menu-item">
            <i class="fa-regular fa-user"></i>
            <span>Profile</span>
          </a>
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
          <h2>Dashboard</h2>
          <p>Overview of tasks, progress, and quick stats</p>
        </div>
        <!-- New Task Button -->
        <div class="topbar-right">
          <a href="{{ route('tasks.create') }}" class="new-task-btn">
            <span>New Task</span>
          </a>
        </div>
      </header>

      <!-- Stats Cards -->
      <section class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <h4>Total Tasks</h4>
            <h3 id="totalTasks">-</h3>
          </div>
          <div class="stat-icon blue">
            <i class="fa-regular fa-clock"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <h4>Completed</h4>
            <h3 id="completedTasks">-</h3>
          </div>
          <div class="stat-icon green">
            <i class="fa-regular fa-circle-check"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <h4>Due Today</h4>
            <h3 id="inProgressTasks">-</h3>
          </div>
          <div class="stat-icon orange">
            <i class="fa-regular fa-clock"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <h4>Overdue</h4>
            <h3 id="pendingTasks">-</h3>
          </div>
          <div class="stat-icon purple">
            <i class="fa-solid fa-arrow-trend-up"></i>
          </div>
        </div>
      </section>

      <!-- Dashboard Content -->
      <section class="dashboard-content">
        <!-- Recent Tasks -->
        <div class="recent-tasks-card">
          <div class="section-header">
            <h3>Recent Tasks</h3>
          </div>

          <div class="task-list" id="recentTaskList">
            <!-- Data task dari database akan dirender di sini -->
          </div>

          <div class="section-footer">
            <a href="{{ route('tasks.index') }}" class="view-btn">View All Tasks</a>
          </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="deadlines-card">
          <div class="section-header">
            <h3>Upcoming Deadlines</h3>
          </div>

          <div class="deadline-list" id="upcomingDeadlineList">
            <!-- Data deadline dari database akan dirender di sini -->
          </div>

          <div class="section-footer">
            <a href="{{ route('calendar') }}" class="view-btn">View Calendar</a>
          </div>
        </div>
      </section>
    </main>
  </div>

  @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
  <script src="{{ asset('assets/js/preferences.js') }}"></script>
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>

</body>
</html>