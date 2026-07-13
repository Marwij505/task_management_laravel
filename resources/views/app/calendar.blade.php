<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Flowlist - Calendar</title>
    <link rel="stylesheet" href="{{ asset('assets/css/calendar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <div class="calendar-container">
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

                    <a href="{{ route('calendar') }}" class="menu-item active">
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
            <!-- Page Header -->
            <header class="page-header">
                <h2>Calendar View</h2>
                <p>Visualize tasks based on dates</p>
            </header>

            <!-- Calendar Card -->
            <section class="calendar-card">
                <!-- Calendar Top -->
                <div class="calendar-topbar">
                    <h3 id="calendarMonth">April 2026</h3>

                    <div class="calendar-nav">
                        <button type="button" class="today-btn" id="todayBtn">
                            Today
                        </button>

                        <button type="button" class="calendar-nav-btn" id="prevMonthBtn">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <button type="button" class="calendar-nav-btn" id="nextMonthBtn">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                </div>

                <!-- Day Names -->
                <div class="calendar-weekdays">
                    <div class="weekday">Sun</div>
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-grid" id="calendarGrid">
                    <!-- Calendar days from database will be rendered here -->
                </div>
                
                <!-- Legend -->
                <div class="calendar-legend">
                    <div class="legend-item">
                        <span class="legend-dot high"></span>
                        <span>High Priority</span>
                    </div>

                    <div class="legend-item">
                        <span class="legend-dot medium"></span>
                        <span>Medium Priority</span>
                    </div>

                    <div class="legend-item">
                        <span class="legend-dot low"></span>
                        <span>Low Priority</span>
                    </div>
                </div>
            </section>

            {{-- Footer Flowlist --}}
            @include('partials.app-footer')
        </main>
    </div>

    @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/calendar.js') }}"></script>

</body>
</html>