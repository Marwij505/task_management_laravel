<!DOCTYPE html>
<html lang="en" data-theme-setting="Light" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Flowlist Admin - Task Detail</title>

    <!--
        Shared Flowlist dashboard styles are loaded first because
        sidebar, main-content, footer, badge, and theme structure
        are shared with the existing admin pages.
    -->
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />

    <!-- Flowlist MK2 Admin Task Detail styles. -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin-task-detail.css') }}" />

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

                    <a href="{{ route('admin.tasks.index') }}" class="menu-item active">
                        <i class="fa-solid fa-list-check"></i>
                        <span>All Tasks</span>
                    </a>

                    <a href="{{ route('admin.statistics.index') }}" class="menu-item">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Global Statistics</span>
                    </a>

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
        <main class="main-content admin-task-detail-page">
            <header class="admin-detail-page-header">
                <div>
                    <a
                        href="{{ route('admin.tasks.index') }}"
                        class="admin-detail-back-btn"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back to All Tasks</span>
                    </a>

                    <h2>Task Detail</h2>

                    <p>
                        Monitor task information and the owner's checklist.
                    </p>
                </div>

                <span class="admin-detail-readonly-badge">
                    <i class="fa-solid fa-eye"></i>
                    Checklist Read Only
                </span>
            </header>

            <!-- =====================================================
                 TASK INFORMATION
                 ===================================================== -->
            <section class="admin-task-detail-card">
                <div class="admin-task-detail-heading">
                    <div class="admin-task-heading-main">
                        <div class="admin-detail-eyebrow">
                            Task #{{ $task->id }}
                        </div>

                        <h1>{{ $task->title }}</h1>

                        <div class="admin-detail-badges">
                            <span class="admin-detail-badge status-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>

                            <span class="admin-detail-badge priority-{{ $task->priority }}">
                                {{ ucfirst($task->priority) }}
                            </span>

                            @if($task->category)
                                <span class="admin-detail-badge neutral">
                                    {{ $task->category }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!--
                        Owner is clickable and opens User Management with
                        search + focus so the exact account is immediately visible.
                    -->
                    @if($task->user)
                        <a
                            href="{{
                                route(
                                    'admin.users.index',
                                    [
                                        'search' => $task->user->email,
                                        'focus' => $task->user->id,
                                    ]
                                )
                            }}"
                            class="admin-detail-owner"
                            title="Open owner in User Management"
                        >
                            <span>Owner</span>

                            <strong>
                                {{
                                    $task->user->full_name
                                    ?: $task->user->name
                                    ?: $task->user->username
                                }}
                            </strong>

                            <small>{{ $task->user->email }}</small>

                            <em>
                                Open User Management
                                <i class="fa-solid fa-chevron-right"></i>
                            </em>
                        </a>
                    @else
                        <div class="admin-detail-owner">
                            <span>Owner</span>
                            <strong>Unknown User</strong>
                            <small>-</small>
                        </div>
                    @endif
                </div>

                <!-- =================================================
                     EFFECTIVE TASK PROGRESS
                     =================================================
                     Uses TaskProgressService from the controller.
                     This keeps the number consistent with user/admin
                     dashboard progress logic.
                ================================================== -->
                <div class="admin-main-progress">
                    <div class="admin-main-progress-label">
                        <span>Task Progress</span>
                        <strong>{{ $taskProgress }}%</strong>
                    </div>

                    <div
                        class="admin-main-progress-track"
                        role="progressbar"
                        aria-label="Task progress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $taskProgress }}"
                    >
                        <div
                            class="admin-main-progress-fill"
                            style="width: {{ $taskProgress }}%;"
                        ></div>
                    </div>
                </div>

                <!-- Description -->
                <div class="admin-detail-section">
                    <h3>Description</h3>

                    <p>
                        {{ $task->description ?: 'No description available.' }}
                    </p>
                </div>

                <!-- Information Grid -->
                <div class="admin-detail-info-grid">
                    <div class="admin-detail-info">
                        <i class="fa-regular fa-calendar"></i>

                        <div>
                            <span>Deadline</span>

                            <strong>
                                {{
                                    $task->deadline
                                        ? \Carbon\Carbon::parse($task->deadline)->format('d M Y')
                                        : 'No deadline'
                                }}
                            </strong>
                        </div>
                    </div>

                    <div class="admin-detail-info">
                        <i class="fa-solid fa-flag"></i>

                        <div>
                            <span>Priority</span>
                            <strong>{{ ucfirst($task->priority) }}</strong>
                        </div>
                    </div>

                    <div class="admin-detail-info">
                        <i class="fa-regular fa-folder"></i>

                        <div>
                            <span>Category</span>
                            <strong>{{ $task->category ?: '-' }}</strong>
                        </div>
                    </div>

                    <div class="admin-detail-info">
                        <i class="fa-regular fa-user"></i>

                        <div>
                            <span>Assignee</span>
                            <strong>{{ $task->assignee ?: 'No assignee' }}</strong>
                        </div>
                    </div>

                    <div class="admin-detail-info">
                        <i class="fa-regular fa-clock"></i>

                        <div>
                            <span>Created</span>

                            <strong>
                                {{ $task->created_at?->format('d M Y, H:i') ?? '-' }}
                            </strong>
                        </div>
                    </div>

                    <div class="admin-detail-info">
                        <i class="fa-solid fa-rotate"></i>

                        <div>
                            <span>Last Updated</span>

                            <strong>
                                {{ $task->updated_at?->format('d M Y, H:i') ?? '-' }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="admin-detail-tags-section">
                    <span class="admin-detail-tags-title">
                        <i class="fa-solid fa-tags"></i>
                        Tags
                    </span>

                    <div class="admin-detail-tags">
                        @forelse($task->tags as $tag)
                            <span>#{{ $tag->tag_name }}</span>
                        @empty
                            <small>No tags</small>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 FLOWLIST MK2 - SUBTASK / CHECKLIST SYNCHRONIZATION
                 =====================================================
                 Admin monitoring is intentionally read-only.
                 User owns checklist CRUD operations.
            ====================================================== -->
            <section class="admin-checklist-card">
                <div class="admin-checklist-header">
                    <div>
                        <h2>
                            <i class="fa-solid fa-list-check"></i>
                            Subtasks & Checklist
                        </h2>

                        <p>
                            Monitor checklist progress without modifying the owner's items.
                        </p>
                    </div>

                    <span class="admin-checklist-percent">
                        {{ $checklistPercentage }}%
                    </span>
                </div>

                <div class="admin-checklist-stats">
                    <div>
                        <strong>{{ $totalSubtasks }}</strong>
                        <span>Total</span>
                    </div>

                    <div>
                        <strong>{{ $completedSubtasks }}</strong>
                        <span>Completed</span>
                    </div>

                    <div>
                        <strong>{{ $remainingSubtasks }}</strong>
                        <span>Remaining</span>
                    </div>
                </div>

                <div class="admin-checklist-progress">
                    <div class="admin-checklist-progress-label">
                        <span>Checklist Progress</span>

                        <strong>
                            {{ $completedSubtasks }}
                            of
                            {{ $totalSubtasks }}
                            completed
                        </strong>
                    </div>

                    <div
                        class="admin-checklist-progress-track"
                        role="progressbar"
                        aria-label="Checklist progress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $checklistPercentage }}"
                    >
                        <div
                            class="admin-checklist-progress-fill"
                            style="width: {{ $checklistPercentage }}%;"
                        ></div>
                    </div>
                </div>

                <div class="admin-checklist-list">
                    @forelse($task->subtasks as $subtask)
                        <article
                            class="
                                admin-checklist-item
                                {{ $subtask->is_completed ? 'completed' : '' }}
                            "
                        >
                            <div class="admin-checklist-status" aria-hidden="true">
                                @if($subtask->is_completed)
                                    <i class="fa-solid fa-check"></i>
                                @endif
                            </div>

                            <div class="admin-checklist-content">
                                <strong>{{ $subtask->title }}</strong>

                                <span>
                                    {{
                                        $subtask->is_completed
                                            ? 'Completed'
                                            : 'Pending'
                                    }}

                                    @if($subtask->completed_at)
                                        · {{ $subtask->completed_at->format('d M Y, H:i') }}
                                    @endif
                                </span>
                            </div>

                            <div class="admin-checklist-position">
                                #{{ $subtask->position }}
                            </div>
                        </article>
                    @empty
                        <div class="admin-checklist-empty">
                            <i class="fa-regular fa-square-check"></i>

                            <strong>No subtasks</strong>

                            <span>
                                This task does not have checklist items yet.
                            </span>
                        </div>
                    @endforelse
                </div>

                <div class="admin-readonly-note">
                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Administrator monitoring is read-only for checklist items.
                        Create, edit, complete, reorder, and delete remain under the task owner.
                    </span>
                </div>
            </section>

            {{-- Shared Flowlist footer --}}
            @include('partials.app-footer')
        </main>
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/preferences.js') }}"></script>
</body>

</html>
