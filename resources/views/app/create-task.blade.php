<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Flowlist - Create Task</title>
    <link rel="stylesheet" href="{{ asset('assets/css/create-task.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>

<body>
    <div class="create-task-container">
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

                    <a href="{{ route('tasks.create') }}" class="menu-item active">
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

            <!-- Header -->
            <header class="page-header">
                <h2>Create New Task</h2>
                <p>Form to add a new task</p>
            </header>

            <!-- Form Card -->
            <section class="form-card">
                <form action="#" method="POST" id="createTaskForm">
                    <!-- Task Title -->
                    <div class="form-group full-width">
                        <label for="taskTitle">Task Title <span>*</span></label>
                        <input
                            type="text"
                            id="taskTitle"
                            name="taskTitle"
                            placeholder="Enter task title"
                            required
                        />
                    </div>
                    <!-- Description -->
                    <div class="form-group full-width">
                        <label for="taskDescription">Description</label>
                        <textarea
                            id="taskDescription"
                            name="taskDescription"
                            placeholder="Enter task description"
                            rows="6"
                        ></textarea>
                    </div>
                    <!-- Form Row Status -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="taskStatus">Status</label>
                            <select id="taskStatus" name="taskStatus">
                                <option value="pending">Pending</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    <!-- Form Row Priority -->
                        <div class="form-group">
                            <label for="taskPriority">Priority</label>
                            <select id="taskPriority" name="taskPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <!-- Form Row Category -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="taskCategory">Category</label>
                            <input
                                type="text"
                                id="taskCategory"
                                name="taskCategory"
                                placeholder="e.g., Work, Personal"
                            />
                        </div>
                        <!-- Deadline -->
                        <div class="form-group">
                            <label for="taskDeadline">Deadline</label>
                            <input
                                type="date"
                                id="taskDeadline"
                                name="taskDeadline"
                            />
                        </div>
                    </div>
                    <!-- Form Row Assignee and Tags -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="taskAssignee">Assignee</label>
                            <input
                                type="text"
                                id="taskAssignee"
                                name="taskAssignee"
                                placeholder="e.g., John Doe"
                            />
                        </div>

                        <div class="form-group">
                            <label for="taskTags">Tags</label>
                            <input
                                type="text"
                                id="taskTags"
                                name="taskTags"
                                placeholder="e.g., design, urgent, frontend"
                            />
                            <small class="field-help">Separate tags with commas.</small>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <p id="formMessage" class="form-message"></p>
                        <button type="submit" class="submit-btn">
                            Create Task
                        </button>
                        <a href="{{ route('tasks.index') }}" class="cancel-btn">
                            Cancel
                        </a>
                    </div>
                </form>
            </section>
        </main>
    </div>

    @include('partials.flowlist-routes')
  <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/create-task.js') }}"></script>

</body>
</html>