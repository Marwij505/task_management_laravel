// ======================================
// FLOWLIST TASK DETAIL PAGE JS
// ======================================

// Ambil id task dari URL
const urlParams = new URLSearchParams(window.location.search);
const taskId = Number(urlParams.get('id'));

// Ambil semua elemen yang akan diisi data
const taskTitle = document.getElementById('taskTitle');
const taskStatusBadge = document.getElementById('taskStatusBadge');
const taskPriorityBadge = document.getElementById('taskPriorityBadge');
const taskCategoryBadge = document.getElementById('taskCategoryBadge');
const progressPercent = document.getElementById('progressPercent');
const progressFill = document.getElementById('progressFill');
const taskDescription = document.getElementById('taskDescription');
const taskDeadline = document.getElementById('taskDeadline');
const taskPriority = document.getElementById('taskPriority');
const taskCreated = document.getElementById('taskCreated');
const taskAssignee = document.getElementById('taskAssignee');
const tagsList = document.getElementById('tagsList');
const markCompleteBtn = document.getElementById('markCompleteBtn');
const updateTaskBtn = document.getElementById('updateTaskBtn');
const deleteTaskBtn = document.getElementById('deleteTaskBtn');
const editTaskBtn = document.getElementById('editTaskBtn');

// Variabel global untuk menyimpan data task yang sedang ditampilkan
let currentTask = null;

// ======================================
// HELPER FORMAT
// Ubah value teknis jadi teks yang rapi
// ======================================
function formatStatusLabel(status) {
    // Status tampilan detail task.
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

// ======================================
// HELPER FORMAT TANGGAL
// ======================================
function formatDate(dateString, fallback = '-') {
    if (window.FlowlistPreferences) {
        return window.FlowlistPreferences.formatDate(dateString, fallback);
    }

    return dateString || fallback;
}

// ======================================
// HELPER NORMALISASI PROGRESS
// Supaya progress tidak kurang dari 0 atau lebih dari 100
// ======================================
function normalizeProgress(progress) {
    return Math.max(0, Math.min(100, Number(progress) || 0));
}

// ======================================
// HELPER SET TEXT AMAN
// Pakai textContent agar data database tidak dirender sebagai HTML
// ======================================
function setText(element, value, fallback = '-') {
    if (!element) return;
    element.textContent = value || fallback;
}

// ======================================
// LOADING STATE
// ======================================
function renderLoadingState() {
    setText(taskTitle, 'Loading task...');
    setText(taskDescription, 'Please wait while we load the task detail.');
    setText(progressPercent, '0%');

    if (progressFill) {
        progressFill.style.width = '0%';
    }

    setText(taskStatusBadge, '-');
    setText(taskPriorityBadge, '-');
    setText(taskCategoryBadge, '-');
    setText(taskDeadline, '-');
    setText(taskPriority, '-');
    setText(taskCreated, '-');
    setText(taskAssignee, '-');

    if (tagsList) {
        tagsList.innerHTML = '<span class="tag">Loading...</span>';
    }
}

// ======================================
// FUNGSI ISI DATA KE HALAMAN
// ======================================
function renderTaskDetail(task) {
    currentTask = task;
    const progress = normalizeProgress(task.progress);

    // Untuk tampilan, pakai effective_status dari backend.
    // Untuk tombol edit dan mark complete, tetap pakai task.status asli.
    const displayStatus = task.effective_status || task.status;
    const statusLabel = task.status_label || formatStatusLabel(displayStatus);

    // Isi judul task
    setText(taskTitle, task.title, 'Untitled Task');

    // Isi badge status
    if (taskStatusBadge) {
        taskStatusBadge.textContent = statusLabel;
        taskStatusBadge.className = `badge status ${displayStatus}`;
    }
    
    // Isi badge priority
    if (taskPriorityBadge) {
        taskPriorityBadge.textContent = formatPriorityLabel(task.priority);
        taskPriorityBadge.className = `badge priority ${task.priority}`;
    }

    // Isi badge category
    if (taskCategoryBadge) {
        taskCategoryBadge.textContent = task.category || 'Uncategorized';
        taskCategoryBadge.className = 'badge category';
    }

    // Isi progress
    setText(progressPercent, `${progress}%`);

    if (progressFill) {
        progressFill.style.width = `${progress}%`;
        progressFill.classList.remove(
            'is-completed',
            'is-pending',
            'is-overdue',
            'is-due-today'
        );

        if (displayStatus === 'completed') {
            progressFill.classList.add('is-completed');
        } else if (displayStatus === 'pending') {
            progressFill.classList.add('is-pending');
        } else if (displayStatus === 'overdue') {
            progressFill.classList.add('is-overdue');
        } else if (displayStatus === 'due-today') {
            progressFill.classList.add('is-due-today');
        }
    }

    // Isi deskripsi
    setText(taskDescription, task.description, 'No description available.');

    // Isi informasi bawah
    setText(taskDeadline, formatDate(task.deadline, 'No deadline'));
    setText(taskCreated, formatDate(task.created_at || task.created, '-'));
    setText(taskPriority, formatPriorityLabel(task.priority), '-');
    setText(taskAssignee, task.assignee, 'Unassigned');

    // Render tags
    if (tagsList) {
        tagsList.innerHTML = '';

        if (!task.tags || task.tags.length === 0) {
            const span = document.createElement('span');
            span.className = 'tag';
            span.textContent = 'No tags';
            tagsList.appendChild(span);
        } else {
            task.tags.forEach(function (tag) {
                const span = document.createElement('span');
                span.className = 'tag';
                span.textContent = tag;
                tagsList.appendChild(span);
            });
        }
    }

    updateMarkCompleteButton(task);
}

// ======================================
// UPDATE TOMBOL MARK COMPLETE
// ======================================
function updateMarkCompleteButton(task) {
    if (!markCompleteBtn || !task) return;

    if (task.status === 'completed') {
        markCompleteBtn.disabled = true;
        markCompleteBtn.textContent = 'Completed';
        markCompleteBtn.classList.add('is-disabled');
        return;
    }

    markCompleteBtn.disabled = false;
    markCompleteBtn.textContent = 'Mark as Complete';
    markCompleteBtn.classList.remove('is-disabled');
}

// ======================================
// FUNGSI JIKA TASK TIDAK DITEMUKAN
// ======================================
function renderTaskNotFound() {
    currentTask = null;

    setText(taskTitle, 'Task Not Found');
    setText(taskDescription, 'The task you are looking for does not exist or the id is invalid.');
    setText(progressPercent, '0%');

    if (progressFill) {
        progressFill.style.width = '0%';
        progressFill.classList.remove('is-completed', 'is-pending');
    }

    setText(taskStatusBadge, '-');
    setText(taskPriorityBadge, '-');
    setText(taskCategoryBadge, '-');
    setText(taskDeadline, '-');
    setText(taskPriority, '-');
    setText(taskCreated, '-');
    setText(taskAssignee, '-');

    if (tagsList) {
        tagsList.innerHTML = '<span class="tag">No Data</span>';
    }

    if (markCompleteBtn) {
        markCompleteBtn.disabled = true;
        markCompleteBtn.textContent = 'Unavailable';
        markCompleteBtn.classList.add('is-disabled');
    }
}

// ======================================
// LOAD DETAIL TASK DARI BACKEND PHP
// ======================================
function loadTaskDetail() {
    // Jika id di URL tidak valid, tampilkan not found
    if (!taskId || taskId <= 0) {
        renderTaskNotFound();
        return;
    }

    renderLoadingState();

    fetch(`${window.FlowlistRoutes.taskDetailApi}?id=${taskId}`)
        .then(function (response) {
            // Jika session habis atau belum login
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            // Jika task tidak ditemukan
            if (response.status === 404) {
                renderTaskNotFound();
                return null;
            }

            // Jika response error lain
            if (!response.ok) {
                throw new Error('Task detail request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                console.error('Task detail load failed:', data.message);
                renderTaskNotFound();
                return;
            }

            currentTask = data.task;
            renderTaskDetail(currentTask);
            updateMarkCompleteButton(currentTask);
        })
        .catch(function (error) {
            console.error('Task detail error:', error);
            renderTaskNotFound();
        });
}

// ======================================
// INISIALISASI LOAD DETAIL TASK
// ======================================
document.addEventListener('DOMContentLoaded', function () {
    loadTaskDetail();
});

// ======================================
// EVENT BUTTON
// ======================================

// Tombol mark as complete dengan database PHP (akan update status task jadi completed)
if (markCompleteBtn) {
    markCompleteBtn.addEventListener('click', function () {
        if (!taskId || taskId <= 0) return;

        if (currentTask && currentTask.status === 'completed') {
            return;
        }

        const confirmComplete = confirm('Mark this task as completed?');

        if (!confirmComplete) {
            return;
        }

        const formData = new FormData();
        formData.append('task_id', taskId);

        markCompleteBtn.disabled = true;
        markCompleteBtn.textContent = 'Completing...';

        fetch(window.FlowlistRoutes.taskCompleteApi, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = window.FlowlistRoutes.login;
                    return null;
                }

                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        status: response.status,
                        data: data
                    };
                });
            })
            .then(function (result) {
                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to complete task.');
                }

                loadTaskDetail();
            })
            .catch(function (error) {
                console.error('Mark complete error:', error);
                alert(error.message || 'Failed to mark task as complete.');

                if (!currentTask || currentTask.status !== 'completed') {
                    markCompleteBtn.disabled = false;
                    markCompleteBtn.textContent = 'Mark as Complete';
                }
            });
    });
}

// Tombol update task
if (updateTaskBtn) {
    updateTaskBtn.addEventListener('click', function () {
        window.location.href = `${window.FlowlistRoutes.taskCreate}?id=${taskId}`;
    });
}

// Tombol edit
if (editTaskBtn) {
    editTaskBtn.addEventListener('click', function () {
        window.location.href = `${window.FlowlistRoutes.taskCreate}?id=${taskId}`;
    });
}

// Tombol delete dengan database PHP (akan menghapus task dari database)
if (deleteTaskBtn) {
    deleteTaskBtn.addEventListener('click', function () {
        if (!taskId || taskId <= 0) return;

        const confirmDelete = confirm('Are you sure you want to delete this task? This action cannot be undone.');

        if (!confirmDelete) {
            return;
        }

        const formData = new FormData();
        formData.append('task_id', taskId);

        deleteTaskBtn.disabled = true;
        deleteTaskBtn.innerHTML = '<i class="fa-regular fa-trash-can"></i><span>Deleting...</span>';

        fetch(window.FlowlistRoutes.taskDeleteApi, {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = window.FlowlistRoutes.login;
                    return null;
                }

                return response.json().then(function (data) {
                    return {
                        ok: response.ok,
                        status: response.status,
                        data: data
                    };
                });
            })
            .then(function (result) {
                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to delete task.');
                }

                window.location.href = window.FlowlistRoutes.taskList;
            })
            .catch(function (error) {
                console.error('Delete task error:', error);
                alert(error.message || 'Failed to delete task.');

                deleteTaskBtn.disabled = false;
                deleteTaskBtn.innerHTML = '<i class="fa-regular fa-trash-can"></i><span>Delete</span>';
            });
    });
}