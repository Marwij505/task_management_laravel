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
    if (!recentTaskListEl) return;

    recentTaskListEl.innerHTML = '';

    if (!tasks || tasks.length === 0) {
        renderEmptyState(recentTaskListEl, 'No recent tasks available');
        return;
    }

    tasks.forEach(function (task) {
        const taskItem = document.createElement('div');
        taskItem.className = 'task-item';

        // Pakai status realistis dari backend.
        // Jika backend belum mengirim effective_status, fallback ke status asli database.
        const displayStatus = task.effective_status || task.status;
        const statusLabel = task.status_label || formatStatus(displayStatus);

        taskItem.innerHTML = `
            <div class="task-top">
                <h4>${escapeHTML(task.title)}</h4>
            </div>

            <div class="task-tags">
                <span class="badge status ${getStatusClass(displayStatus)}">
                    ${statusLabel}
                </span>

                <span class="badge priority ${getPriorityClass(task.priority)}">
                    ${formatPriority(task.priority)}
                </span>

                ${task.category ? `<span class="badge category ${getCategoryClass(task.category)}">${escapeHTML(task.category)}</span>` : ''}
            </div>

            <div class="task-date">
                <i class="fa-regular fa-calendar"></i>
                <span>${formatDate(task.deadline)}</span>
            </div>

            <div class="task-progress">
                <div class="progress-header">
                    <span>Progress</span>
                    <span>${normalizeProgress(task.progress)}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${normalizeProgress(task.progress)}%;"></div>
                </div>
            </div>
        `;

        recentTaskListEl.appendChild(taskItem);
    });
}

// ===============================
// RENDER UPCOMING DEADLINES
// ===============================
function renderUpcomingDeadlines(deadlines) {
    if (!upcomingDeadlineListEl) return;

    upcomingDeadlineListEl.innerHTML = '';

    if (!deadlines || deadlines.length === 0) {
        renderEmptyState(upcomingDeadlineListEl, 'No upcoming deadlines available');
        return;
    }

    deadlines.forEach(function (task) {
        const deadlineItem = document.createElement('div');
        deadlineItem.className = 'deadline-item';

        deadlineItem.innerHTML = `
            <div class="deadline-info">
                <h4>${escapeHTML(task.title)}</h4>
                <p>
                    <i class="fa-regular fa-calendar"></i>
                    <span>${formatDate(task.deadline)}</span>
                </p>
            </div>

            <span class="badge priority ${getPriorityClass(task.priority)}">
                ${formatPriority(task.priority)}
            </span>
        `;

        upcomingDeadlineListEl.appendChild(deadlineItem);
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