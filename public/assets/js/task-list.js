// ===============================
// FLOWLIST TASK LIST PAGE JS
// Versi database PHP
// ===============================

// Ambil elemen penting dari HTML
const taskGrid = document.getElementById('taskGrid');
const searchTaskInput = document.getElementById('searchTask');
const statusFilter = document.getElementById('statusFilter');
const categoryFilter = document.getElementById('categoryFilter');
const tagFilter = document.getElementById('tagFilter');
const priorityFilter = document.getElementById('priorityFilter');
const taskCountText = document.getElementById('taskCountText');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');

// Array asli dari database
let allTasks = [];

// Array hasil filter yang sedang ditampilkan
let displayedTasks = [];

// ===============================
// HELPER FORMAT
// ===============================
function formatStatusLabel(status) {
    // Label status untuk tampilan card task.
    const labels = {
        'pending': 'Pending',
        'in-progress': 'In Progress',
        'completed': 'Completed',
        'due-today': 'Due Today',
        'overdue': 'Overdue'
    };

    return labels[status] || status;
}

function formatPriorityLabel(priority) {
    if (priority === 'high') return 'High';
    if (priority === 'medium') return 'Medium';
    if (priority === 'low') return 'Low';
    return priority;
}

// ===============================
// HELPER NORMALISASI FILTER
// ===============================
function normalizeFilterValue(value) {
    return String(value || '').trim().toLowerCase();
}

// ===============================
// ISI DROPDOWN CATEGORY DAN TAG DARI DATA TASK
// ===============================
function populateDynamicFilters(tasks) {
    const categorySet = new Set();
    const tagSet = new Set();

    tasks.forEach(function (task) {
        if (task.category && task.category.trim() !== '') {
            categorySet.add(task.category.trim());
        }

        if (Array.isArray(task.tags)) {
            task.tags.forEach(function (tag) {
                if (tag && tag.trim() !== '') {
                    tagSet.add(tag.trim());
                }
            });
        }
    });

    if (categoryFilter) {
        categoryFilter.innerHTML = '<option value="">All Categories</option>';

        Array.from(categorySet)
            .sort()
            .forEach(function (category) {
                categoryFilter.innerHTML += `
                    <option value="${escapeHTML(category)}">${escapeHTML(category)}</option>
                `;
            });
    }

    if (tagFilter) {
        tagFilter.innerHTML = '<option value="">All Tags</option>';

        Array.from(tagSet)
            .sort()
            .forEach(function (tag) {
                tagFilter.innerHTML += `
                    <option value="${escapeHTML(tag)}">#${escapeHTML(tag)}</option>
                `;
            });
    }
}

// ===============================
// RENDER TASK CARD
// ===============================
function createTaskCard(task) {
    const description = task.description
        ? escapeHTML(task.description)
        : 'No description available.';
    
    const tags = Array.isArray(task.tags) ? task.tags : [];
    const tagHtml = tags.length
        ? `
            <div class="task-tags">
                ${tags.slice(0, 3).map(function (tag) {
                    return `<span class="task-tag">#${escapeHTML(tag)}</span>`;
                }).join('')}
                ${tags.length > 3 ? `<span class="task-tag muted">+${tags.length - 3}</span>` : ''}
            </div>
        `
        : '';
    // Status asli tetap ada di task.status.
    // Status tampilan memakai effective_status agar overdue dan due today muncul.
    const displayStatus = task.effective_status || task.status;
    const statusLabel = task.status_label || formatStatusLabel(displayStatus);

    return `
        <article class="task-card" data-id="${Number(task.id)}">
            <div class="task-card-top">
                <div class="task-card-title-row">
                    <h3>${escapeHTML(task.title)}</h3>
                    <span class="task-menu-dot">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                </div>

                <p>${description}</p>

                <div class="task-badges">
                    <span class="badge status ${escapeHTML(displayStatus)}">
                        ${escapeHTML(statusLabel)}
                    </span>
                    <span class="badge priority ${escapeHTML(task.priority)}">
                        ${escapeHTML(formatPriorityLabel(task.priority))}
                    </span>
                </div>
                
                ${tagHtml}
            </div>

            <div class="task-card-bottom">
                <span class="task-category">
                    <i class="fa-regular fa-folder"></i>
                    ${task.category ? escapeHTML(task.category) : 'Uncategorized'}
                </span>
                <span class="task-date">
                    <i class="fa-regular fa-calendar"></i>
                    ${formatDate(task.deadline)}
                </span>
            </div>
        </article>
    `;
}

// ===============================
// RENDER ALL TASKS
// ===============================
function renderTasks(tasks) {
    if (!taskGrid) return;

    updateTaskCountText(tasks.length, allTasks.length);

    // Kalau tidak ada task
    if (!tasks.length) {
        taskGrid.innerHTML = `
            <div class="empty-state">
                <i class="fa-regular fa-clipboard"></i>
                <h3>No tasks found</h3>
                <p>Try adjusting your search or create a new task to get started.</p>
                <a href="${window.FlowlistRoutes.taskCreate}" class="empty-action">Create New Task</a>
            </div>
        `;
        return;
    }

    // Render semua card
    taskGrid.innerHTML = tasks.map(createTaskCard).join('');

    // Tambah event klik ke setiap card
    const taskCards = document.querySelectorAll('.task-card');
    taskCards.forEach(function (card) {
        card.addEventListener('click', function () {
            const taskId = card.getAttribute('data-id');
            window.location.href = `${window.FlowlistRoutes.taskDetail}?id=${taskId}`;
        });
    });
}

// ===============================
// FILTER + SEARCH
// ===============================
function filterTasks() {
    const searchValue = searchTaskInput ? normalizeFilterValue(searchTaskInput.value) : '';
    const selectedStatus = statusFilter ? statusFilter.value : '';
    const selectedPriority = priorityFilter ? priorityFilter.value : '';
    const selectedCategory = categoryFilter ? normalizeFilterValue(categoryFilter.value) : '';
    const selectedTag = tagFilter ? normalizeFilterValue(tagFilter.value) : '';

    displayedTasks = allTasks.filter(function (task) {
        const title = normalizeFilterValue(task.title);
        const description = normalizeFilterValue(task.description);
        const category = normalizeFilterValue(task.category);
        const assignee = normalizeFilterValue(task.assignee);
        const tags = Array.isArray(task.tags) ? task.tags : [];
        const tagsText = tags.map(normalizeFilterValue).join(' ');

        const displayStatus = normalizeFilterValue(task.effective_status || task.status);

        const matchesSearch =
            searchValue === '' ||
            title.includes(searchValue) ||
            description.includes(searchValue) ||
            category.includes(searchValue) ||
            assignee.includes(searchValue) ||
            tagsText.includes(searchValue);

        const matchesStatus =
            selectedStatus === '' || displayStatus === selectedStatus;

        const matchesPriority =
            selectedPriority === '' || task.priority === selectedPriority;

        const matchesCategory =
            selectedCategory === '' || category === selectedCategory;

        const matchesTag =
            selectedTag === '' || tags.some(function (tag) {
                return normalizeFilterValue(tag) === selectedTag;
            });

        return matchesSearch &&
            matchesStatus &&
            matchesPriority &&
            matchesCategory &&
            matchesTag;
    });

    renderTasks(displayedTasks);
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

// ===============================
// FORMAT TANGGAL
// ===============================
function formatDate(dateString) {
    if (window.FlowlistPreferences) {
        return window.FlowlistPreferences.formatDate(dateString, 'No deadline');
    }

    return dateString || 'No deadline';
}

// ===============================
// LOADING STATE
// ===============================
function renderLoadingState() {
    if (!taskGrid) return;

    taskGrid.innerHTML = `
        <div class="empty-state">
            <i class="fa-solid fa-spinner fa-spin"></i>
            <span>Loading tasks...</span>
        </div>
    `;
}

// ===============================
// UPDATE TASK COUNT TEXT
// ===============================
function updateTaskCountText(currentCount, totalCount) {
    if (!taskCountText) return;

    taskCountText.textContent = `Showing ${currentCount} of ${totalCount} tasks`;
}

// ===============================
// LOAD TASKS DARI BACKEND PHP
// ===============================
function loadTasksFromDatabase() {
    renderLoadingState();
    fetch(window.FlowlistRoutes.taskListApi)
        .then(function (response) {
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            if (!response.ok) {
                throw new Error('Task list request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                console.error('Task list load failed:', data.message);
                renderTasks([]);
                return;
            }

            // Simpan semua task dari database
            allTasks = data.tasks || [];

            // Isi dropdown category dan tag berdasarkan data yang baru di-load
            populateDynamicFilters(allTasks);

            // Awal render tampilkan semua
            displayedTasks = [...allTasks];

            // Render task yang tampil
            renderTasks(displayedTasks);
        })
        .catch(function (error) {
            console.error('Task list error:', error);
            renderTasks([]);
        });
}

// ===============================
// INITIAL LOAD
// ===============================
document.addEventListener('DOMContentLoaded', function () {
    loadTasksFromDatabase();
});

// ===============================
// EVENTS
// ===============================
if (searchTaskInput) {
    searchTaskInput.addEventListener('input', filterTasks);
}

if (statusFilter) {
    statusFilter.addEventListener('change', filterTasks);
}

if (priorityFilter) {
    priorityFilter.addEventListener('change', filterTasks);
}

if (categoryFilter) {
    categoryFilter.addEventListener('change', filterTasks);
}

if (tagFilter) {
    tagFilter.addEventListener('change', filterTasks);
}

if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', function () {
        if (searchTaskInput) searchTaskInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (priorityFilter) priorityFilter.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (tagFilter) tagFilter.value = '';

        filterTasks();
    });
}