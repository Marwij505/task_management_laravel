// ===============================
// FLOWLIST DASHBOARD PAGE JS
// ===============================

// Ambil elemen statistik
const totalTasksEl = document.getElementById('totalTasks');
const completedTasksEl = document.getElementById('completedTasks');
const inProgressTasksEl = document.getElementById('inProgressTasks');
const pendingTasksEl = document.getElementById('pendingTasks');

// Ambil container daftar task dan deadline
const recentTaskListEl = document.getElementById('recentTaskList');
const upcomingDeadlineListEl = document.getElementById('upcomingDeadlineList');

// ===============================
// FORMAT STATUS UNTUK TAMPILAN
// ===============================
function formatStatus(status) {
    // Status tampilan dari backend:
    // pending, in-progress, completed, due-today, overdue
    const labels = {
        'pending': 'Pending',
        'in-progress': 'In Progress',
        'completed': 'Completed',
        'due-today': 'Due Today',
        'overdue': 'Overdue'
    };

    return labels[status] || 'Pending';
}

// ===============================
// FORMAT PRIORITY UNTUK TAMPILAN
// ===============================
function formatPriority(priority) {
    if (priority === 'high') return 'High';
    if (priority === 'medium') return 'Medium';
    return 'Low';
}

// ===============================
// FORMAT TANGGAL UNTUK TAMPILAN
// ===============================
function formatDate(dateString) {
    if (window.FlowlistPreferences) {
        return window.FlowlistPreferences.formatDate(dateString, '-');
    }

    return dateString || '-';
}

// ===============================
// CLASS STATUS BADGE
// ===============================
function getStatusClass(status) {
    // Class ini dipakai oleh CSS untuk memberi warna badge status.
    const classes = {
        'pending': 'pending',
        'in-progress': 'progress',
        'completed': 'completed',
        'due-today': 'due-today',
        'overdue': 'overdue'
    };

    return classes[status] || 'pending';
}

// ===============================
// CLASS PRIORITY BADGE
// ===============================
function getPriorityClass(priority) {
    if (priority === 'high') return 'high';
    if (priority === 'medium') return 'medium';
    return 'low';
}

// ===============================
// CLASS CATEGORY BADGE
// ===============================
function getCategoryClass(category) {
    const normalized = String(category || '').trim().toLowerCase();

    const knownCategories = {
        personal: 'category-personal',
        yolo: 'category-yolo',
        work: 'category-work',
        study: 'category-study',
        health: 'category-health',
        finance: 'category-finance'
    };

    return knownCategories[normalized] || 'category-default';
}

// ===============================
// CLASS WIDTH PROGRESS BAR
// ===============================
function getProgressWidthClass(progress) {
    const safeProgress = Math.max(0, Math.min(100, Number(progress) || 0));

    if (safeProgress >= 100) return 'w-100';
    if (safeProgress >= 90) return 'w-90';
    if (safeProgress >= 80) return 'w-80';
    if (safeProgress >= 75) return 'w-75';
    if (safeProgress >= 70) return 'w-70';
    if (safeProgress >= 60) return 'w-60';
    if (safeProgress >= 50) return 'w-50';
    if (safeProgress >= 40) return 'w-40';
    if (safeProgress >= 30) return 'w-30';
    if (safeProgress >= 25) return 'w-25';
    if (safeProgress >= 20) return 'w-20';
    if (safeProgress >= 10) return 'w-10';
    return 'w-0';
}

// ===============================
// RENDER RECENT TASKS
// ===============================
function renderRecentTasks(tasks) {
    if (!recentTaskListEl) {
        return;
    }

    /*
     * Bersihkan data sebelumnya.
     */
    recentTaskListEl.innerHTML = '';

    /*
     * Empty state.
     */
    if (
        !tasks ||
        tasks.length === 0
    ) {
        renderEmptyState(
            recentTaskListEl,
            'No recent tasks available'
        );

        return;
    }


    tasks.forEach(function (task) {

        /*
         * =====================================================
         * CLICKABLE TASK CARD
         * =====================================================
         *
         * Sebelumnya menggunakan:
         *
         * document.createElement('div')
         *
         * Sekarang menggunakan:
         *
         * document.createElement('a')
         *
         * Dengan begitu seluruh card menjadi link yang valid,
         * accessible, dan bisa dibuka melalui:
         *
         * - klik kiri
         * - Ctrl + Click
         * - klik tengah mouse
         * - Open Link in New Tab
         */
        const taskItem =
            document.createElement('a');


        /*
         * Pertahankan class task-item agar desain MK1
         * tidak berubah.
         *
         * dashboard-task-link merupakan class tambahan
         * untuk efek klik/hover.
         */
        taskItem.className =
            'task-item dashboard-task-link';


        /*
         * URL menuju Task Detail.
         */
        taskItem.href =
            buildTaskDetailUrl(
                task.id
            );


        /*
         * Tooltip browser.
         */
        taskItem.title =
            `Open task: ${task.title || 'Task'}`;


        /*
         * Accessibility untuk screen reader.
         */
        taskItem.setAttribute(
            'aria-label',
            `Open task detail: ${task.title || 'Task'}`
        );


        /*
         * Status realistis dari backend.
         *
         * effective_status dapat berupa:
         *
         * pending
         * in-progress
         * completed
         * due-today
         * overdue
         */
        const displayStatus =
            task.effective_status
            || task.status;


        const statusLabel =
            task.status_label
            || formatStatus(
                displayStatus
            );


        /*
         * Render isi card.
         *
         * escapeHTML tetap digunakan untuk mencegah
         * title/category dari menghasilkan HTML berbahaya.
         */
        taskItem.innerHTML = `
            <div class="task-top">
                <h4>
                    ${escapeHTML(task.title)}
                </h4>

                <span
                    class="dashboard-open-icon"
                    aria-hidden="true"
                >
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </span>
            </div>

            <div class="task-tags">

                <span
                    class="badge status ${getStatusClass(displayStatus)}"
                >
                    ${escapeHTML(statusLabel)}
                </span>

                <span
                    class="badge priority ${getPriorityClass(task.priority)}"
                >
                    ${formatPriority(task.priority)}
                </span>

                ${
                    task.category
                        ? `
                            <span
                                class="badge category ${getCategoryClass(task.category)}"
                            >
                                ${escapeHTML(task.category)}
                            </span>
                        `
                        : ''
                }

            </div>

            <div class="task-date">

                <i class="fa-regular fa-calendar"></i>

                <span>
                    ${formatDate(task.deadline)}
                </span>

            </div>

            <div class="task-progress">

                <div class="progress-header">

                    <span>
                        Progress
                    </span>

                    <span>
                        ${normalizeProgress(task.progress)}%
                    </span>

                </div>

                <div class="progress-bar">

                    <div
                        class="progress-fill"
                        style="width: ${normalizeProgress(task.progress)}%;"
                    ></div>

                </div>

            </div>
        `;


        /*
         * Masukkan card ke Recent Tasks.
         */
        recentTaskListEl.appendChild(
            taskItem
        );
    });
}

// ============================================================
// FLOWLIST - BUILD TASK DETAIL URL
// ============================================================
//
// Membuat URL menuju halaman Task Detail berdasarkan ID task.
//
// Contoh:
//
// taskId = 12
//
// menghasilkan:
//
// /task-detail?id=12
//
// Kita menggunakan FlowlistRoutes.taskDetail agar URL tetap
// mengikuti route Laravel dan tidak di-hardcode.
//
function buildTaskDetailUrl(taskId) {
    /*
     * Pastikan ID benar-benar angka positif.
     */
    const safeTaskId =
        Number(taskId);

    if (
        !Number.isInteger(safeTaskId) ||
        safeTaskId <= 0
    ) {
        return '#';
    }

    /*
     * Gunakan URL() agar query string dibuat
     * secara aman dan konsisten.
     */
    const url =
        new URL(
            window.FlowlistRoutes.taskDetail,
            window.location.origin
        );

    url.searchParams.set(
        'id',
        String(safeTaskId)
    );

    return url.toString();
}

// ===============================
// RENDER UPCOMING DEADLINES
// ===============================
function renderUpcomingDeadlines(deadlines) {
    if (!upcomingDeadlineListEl) {
        return;
    }


    /*
     * Bersihkan isi sebelumnya.
     */
    upcomingDeadlineListEl.innerHTML = '';


    /*
     * Empty state.
     */
    if (
        !deadlines ||
        deadlines.length === 0
    ) {
        renderEmptyState(
            upcomingDeadlineListEl,
            'No upcoming deadlines available'
        );

        return;
    }


    deadlines.forEach(function (task) {

        /*
         * =====================================================
         * CLICKABLE DEADLINE CARD
         * =====================================================
         *
         * Gunakan anchor agar seluruh card dapat
         * membuka Task Detail.
         */
        const deadlineItem =
            document.createElement('a');


        /*
         * Pertahankan class deadline-item lama.
         */
        deadlineItem.className =
            'deadline-item dashboard-deadline-link';


        /*
         * Arahkan langsung ke Task Detail.
         */
        deadlineItem.href =
            buildTaskDetailUrl(
                task.id
            );


        /*
         * Tooltip browser.
         */
        deadlineItem.title =
            `Open task: ${task.title || 'Task'}`;


        /*
         * Accessibility.
         */
        deadlineItem.setAttribute(
            'aria-label',
            `Open task detail: ${task.title || 'Task'}`
        );


        /*
         * Isi card.
         */
        deadlineItem.innerHTML = `
            <div class="deadline-info">

                <h4>
                    ${escapeHTML(task.title)}
                </h4>

                <p>
                    <i class="fa-regular fa-calendar"></i>

                    <span>
                        ${formatDate(task.deadline)}
                    </span>
                </p>

            </div>


            <div class="deadline-right">

                <span
                    class="badge priority ${getPriorityClass(task.priority)}"
                >
                    ${formatPriority(task.priority)}
                </span>

                <span
                    class="dashboard-open-icon"
                    aria-hidden="true"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </span>

            </div>
        `;


        upcomingDeadlineListEl.appendChild(
            deadlineItem
        );
    });
}

// ===============================
// LOAD DASHBOARD DATA DARI PHP
// ===============================
function loadDashboardData() {
    fetch(window.FlowlistRoutes.dashboardApi)
        .then(function (response) {
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            if (!response.ok) {
                throw new Error('Dashboard request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                console.error('Dashboard load failed:', data.message);
                return;
            }

            // Isi statistik
            if (totalTasksEl) totalTasksEl.textContent = data.stats.totalTasks;
            if (completedTasksEl) completedTasksEl.textContent = data.stats.completedTasks;
            if (inProgressTasksEl) inProgressTasksEl.textContent = data.stats.dueTodayTasks;
            if (pendingTasksEl) pendingTasksEl.textContent = data.stats.overdueTasks;

            // Render task terbaru dan deadline
            renderRecentTasks(data.recentTasks || []);
            renderUpcomingDeadlines(data.upcomingDeadlines || []);
        })
        .catch(function (error) {
            console.error('Dashboard error:', error);

            if (recentTaskListEl) {
                recentTaskListEl.innerHTML = '<div class="empty-state">Failed to load dashboard data.</div>';
            }

            if (upcomingDeadlineListEl) {
                upcomingDeadlineListEl.innerHTML = '<div class="empty-state">Failed to load deadlines.</div>';
            }
        });
}

// ======================================
// RENDER EMPTY STATE JIKA TIDAK ADA DATA
// ======================================
function renderEmptyState(container, message) {
    if (!container) return;

    container.innerHTML = `
        <div class="empty-state">
            ${escapeHTML(message)}
        </div>
    `;
}

// ===============================
// ESCAPE HTML UNTUK MENCEGAH XSS
// ===============================
function escapeHTML(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// =================================================
// NORMALISASI PROGRESS UNTUK MENCEGAH NILAI INVALID
// ==================================================
function normalizeProgress(progress) {
    return Math.max(0, Math.min(100, Number(progress) || 0));
}

// ===============================
// JALANKAN SAAT HALAMAN DIBUKA
// ===============================
document.addEventListener('DOMContentLoaded', function () {
    loadDashboardData();
});

// ============================================================
// FLOWLIST MK2 - REFRESH SETELAH QUICK ADD
// ============================================================
//
// Setelah Quick Add berhasil, quick-add-task.js akan
// mengirim event "flowlist:quick-task-created".
//
// Dashboard mengambil ulang statistik, recent tasks,
// dan upcoming deadlines.
//
window.addEventListener(
    'flowlist:quick-task-created',
    function () {
        loadDashboardData();
    }
);