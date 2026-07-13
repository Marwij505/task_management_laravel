<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Flowlist - Task Detail</title>
    <link rel="stylesheet" href="{{ asset('assets/css/task-detail.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"rel="stylesheet" />
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
            <!-- Back Button -->
            <div class="back-link-wrapper">
                <a href="{{ route('tasks.index') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Tasks</span>
                </a>
            </div>

            <!-- Detail Card -->
            <section class="detail-card">
                <div class="detail-top">
                    <div class="detail-title-area">
                        <h2 id="taskTitle"></h2>

                        <div class="task-badges">
                            <span class="badge status" id="taskStatusBadge"></span>
                            <span class="badge priority" id="taskPriorityBadge"></span>
                            <span class="badge category" id="taskCategoryBadge"></span>
                        </div>
                    </div>

                    <div class="detail-actions">
                        <button type="button" class="action-btn edit-btn" id="editTaskBtn">
                            <i class="fa-regular fa-pen-to-square"></i>
                            <span>Edit</span>
                        </button>

                        <button type="button" class="action-btn delete-btn" id="deleteTaskBtn">
                            <i class="fa-regular fa-trash-can"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>

                <!-- Progress -->
                <div class="progress-section">
                    <div class="progress-header">
                        <span>Progress</span>
                        <span id="progressPercent"></span>
                    </div>

                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="description-section">
                    <h3>Description</h3>
                    <p id="taskDescription"></p>
                </div>

                <!-- Info Grid -->
                <div class="task-info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                        <div class="info-text">
                            <span>Deadline</span>
                            <strong id="taskDeadline"></strong>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fa-regular fa-circle-dot"></i>
                        </div>
                        <div class="info-text">
                            <span>Priority</span>
                            <strong id="taskPriority"></strong>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <div class="info-text">
                            <span>Created</span>
                            <strong id="taskCreated"></strong>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fa-regular fa-circle-check"></i>
                        </div>
                        <div class="info-text">
                            <span>Assignee</span>
                            <strong id="taskAssignee"></strong>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="tags-section">
                    <h3>Tags</h3>
                    <div class="tags-list" id="tagsList"></div>
                </div>
            </section>

            <!-- Bottom Actions -->
            <div class="bottom-actions">
                <button type="button" class="primary-btn" id="markCompleteBtn">
                    Mark as Complete
                </button>

                <button type="button" class="secondary-btn" id="updateTaskBtn">
                    Update Task
                </button>
            </div>

            {{-- Footer Flowlist --}}
            @include('partials.app-footer')
        </main>
    </div>

    @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
    <script src="{{ asset('assets/js/task-detail.js') }}"></script>
    
</body>
</html>