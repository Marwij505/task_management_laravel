// ===============================
// FLOWLIST CREATE TASK PAGE JS
// Versi realistis untuk PHP backend
// ===============================

// Ambil form utama create task
const createTaskForm = document.getElementById('createTaskForm');

// Ambil semua input form
const taskTitleInput = document.getElementById('taskTitle');
const taskDescriptionInput = document.getElementById('taskDescription');
const taskStatusInput = document.getElementById('taskStatus');
const taskPriorityInput = document.getElementById('taskPriority');
const taskCategoryInput = document.getElementById('taskCategory');
const taskDeadlineInput = document.getElementById('taskDeadline');
const taskAssigneeInput = document.getElementById('taskAssignee');
const taskTagsInput = document.getElementById('taskTags');

// Ambil area pesan untuk sukses / error
const formMessage = document.getElementById('formMessage');

// ===============================
// DETEKSI MODE CREATE / EDIT
// Jika URL punya ?id=..., maka halaman masuk mode edit
// ===============================
const urlParams = new URLSearchParams(window.location.search);
const editTaskId = Number(urlParams.get('id'));
const isEditMode = editTaskId > 0;

// ===============================
// SET MINIMUM DATE = HARI INI
// User tidak bisa pilih tanggal lampau
// ===============================
if (taskDeadlineInput) {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');

    taskDeadlineInput.min = `${year}-${month}-${day}`;
}

// ===============================
// FUNGSI BERSIHKAN ERROR
// Menghapus tanda error lama
// ===============================
function clearErrors() {
    if (taskTitleInput) taskTitleInput.classList.remove('input-error');
    if (taskDescriptionInput) taskDescriptionInput.classList.remove('input-error');
    if (taskStatusInput) taskStatusInput.classList.remove('input-error');
    if (taskPriorityInput) taskPriorityInput.classList.remove('input-error');
    if (taskCategoryInput) taskCategoryInput.classList.remove('input-error');
    if (taskDeadlineInput) taskDeadlineInput.classList.remove('input-error');
    if (taskAssigneeInput) taskAssigneeInput.classList.remove('input-error');
    if (taskTagsInput) taskTagsInput.classList.remove('input-error');

    if (formMessage) {
        formMessage.textContent = '';
        formMessage.className = 'form-message';
    }
}

// ===============================
// FUNGSI TAMPILKAN PESAN
// Untuk pesan sukses / error
// ===============================
function showMessage(message, type) {
    if (!formMessage) return;

    formMessage.textContent = message;
    formMessage.className = `form-message ${type}`;
}

// ===============================
// UPDATE UI UNTUK EDIT MODE
// ===============================
function updatePageForEditMode() {
    if (!isEditMode) return;

    const pageTitle = document.querySelector('.page-header h2');
    const pageSubtitle = document.querySelector('.page-header p');
    const submitButton = createTaskForm
        ? createTaskForm.querySelector('.submit-btn')
        : null;

    if (pageTitle) pageTitle.textContent = 'Edit Task';
    if (pageSubtitle) pageSubtitle.textContent = 'Update task details and save changes';
    if (submitButton) submitButton.textContent = 'Update Task';
}

// ===============================
// ISI FORM SAAT EDIT MODE
// Data task diambil dari task-detail.php
// ===============================
function fillFormWithTask(task) {
    if (!task) return;

    if (taskTitleInput) taskTitleInput.value = task.title || '';
    if (taskDescriptionInput) taskDescriptionInput.value = task.description || '';
    if (taskStatusInput) taskStatusInput.value = task.status || 'pending';
    if (taskPriorityInput) taskPriorityInput.value = task.priority || 'medium';
    if (taskCategoryInput) taskCategoryInput.value = task.category || '';
    if (taskDeadlineInput) taskDeadlineInput.value = task.deadline || '';
    if (taskAssigneeInput) taskAssigneeInput.value = task.assignee || '';

    if (taskTagsInput) {
        taskTagsInput.value = Array.isArray(task.tags)
            ? task.tags.join(', ')
            : '';
    }
}

// ===============================
// LOAD TASK SAAT EDIT MODE
// ===============================
function loadTaskForEdit() {
    if (!isEditMode) return;

    updatePageForEditMode();
    showMessage('Loading task data...', 'success');

    fetch(`${window.FlowlistRoutes.taskDetailApi}?id=${editTaskId}`)
        .then(function (response) {
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            if (response.status === 404) {
                throw new Error('Task not found.');
            }

            if (!response.ok) {
                throw new Error('Failed to load task for editing.');
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                throw new Error(data.message || 'Failed to load task.');
            }

            fillFormWithTask(data.task);
            showMessage('Edit mode: update the task details below.', 'success');
        })
        .catch(function (error) {
            console.error('Load edit task error:', error);
            showMessage(error.message || 'Failed to load task data.', 'error');
        });
}

// ===============================
// VALIDASI FORM
// Memastikan input masuk akal
// ===============================
function validateForm() {
    clearErrors();

    const titleValue = taskTitleInput.value.trim();
    const descriptionValue = taskDescriptionInput.value.trim();
    const statusValue = taskStatusInput.value;
    const priorityValue = taskPriorityInput.value;
    const categoryValue = taskCategoryInput.value.trim();
    const deadlineValue = taskDeadlineInput.value;
    const assigneeValue = taskAssigneeInput ? taskAssigneeInput.value.trim() : '';
    const tagsValue = taskTagsInput ? taskTagsInput.value.trim() : '';

    let isValid = true;

    // Judul task wajib diisi
    if (titleValue === '') {
        taskTitleInput.classList.add('input-error');
        isValid = false;
    }

    // Description boleh kosong, tapi kalau diisi terlalu pendek dianggap kurang layak
    if (descriptionValue !== '' && descriptionValue.length < 10) {
        taskDescriptionInput.classList.add('input-error');
        isValid = false;
    }

    // Status wajib ada
    if (statusValue === '') {
        taskStatusInput.classList.add('input-error');
        isValid = false;
    }

    // Priority wajib ada
    if (priorityValue === '') {
        taskPriorityInput.classList.add('input-error');
        isValid = false;
    }

    // Category boleh kosong, tapi kalau diisi minimal 2 huruf
    if (categoryValue !== '' && categoryValue.length < 2) {
        taskCategoryInput.classList.add('input-error');
        isValid = false;
    }

    // Deadline wajib diisi
    if (deadlineValue === '') {
        taskDeadlineInput.classList.add('input-error');
        isValid = false;
    }

    // Assignee boleh kosong, tapi kalau diisi minimal 2 huruf
    if (assigneeValue !== '' && assigneeValue.length < 2) {
        taskAssigneeInput.classList.add('input-error');
        isValid = false;
    }

    // Tags boleh kosong, tapi kalau diisi jangan terlalu pendek
    if (tagsValue !== '' && tagsValue.length < 2) {
        taskTagsInput.classList.add('input-error');
        isValid = false;
    }

    // Jika ada field yang salah, tampilkan pesan umum
    if (!isValid) {
        showMessage('Please check the form again. Some fields are still invalid.', 'error');
    }

    return isValid;
}

// ===============================
// BENTUK DATA TASK
// Mengambil nilai asli dari form
// ===============================
function buildTaskPayload() {
    return {
        title: taskTitleInput.value.trim(),
        description: taskDescriptionInput.value.trim(),
        status: taskStatusInput.value,
        priority: taskPriorityInput.value,
        category: taskCategoryInput.value.trim(),
        deadline: taskDeadlineInput.value,
        assignee: taskAssigneeInput ? taskAssigneeInput.value.trim() : '',
        tags: taskTagsInput ? taskTagsInput.value.trim() : ''
    };
}

// ===============================
// KIRIM DATA KE BACKEND PHP
// Backend akan insert ke database
// ===============================
function submitTaskToBackend(taskData) {
    const formData = new FormData();

    // Samakan key dengan yang dipakai di create-task.php
    formData.append('title', taskData.title);
    formData.append('description', taskData.description);
    formData.append('status', taskData.status);
    formData.append('priority', taskData.priority);
    formData.append('category', taskData.category);
    formData.append('deadline', taskData.deadline);
    formData.append('assignee', taskData.assignee);
    formData.append('tags', taskData.tags);

    if (isEditMode) {
        formData.append('task_id', editTaskId);
    }

    const endpoint = isEditMode
    ? window.FlowlistRoutes.taskUpdateApi
    : window.FlowlistRoutes.taskStoreApi;

    return fetch(endpoint, {
        method: 'POST',
        body: formData
    }).then(function (response) {
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
    });
}

// ===============================
// SUBMIT FORM
// Alur utama saat user klik tombol Create Task
// ===============================
if (createTaskForm) {
    createTaskForm.addEventListener('submit', function (event) {
        // Cegah submit default browser
        event.preventDefault();

        // Validasi dulu sebelum kirim
        const isFormValid = validateForm();

        // Kalau form belum valid, hentikan
        if (!isFormValid) {
            return;
        }

        // Ambil data dari form
        const taskData = buildTaskPayload();

        // Konfirmasi agar user yakin data sudah benar
        const userConfirmed = confirm(
            isEditMode
                ? 'Save changes to this task?'
                : 'Are you sure all task details are correct and there are no typos?'
        );

        // Kalau batal, hentikan proses
        if (!userConfirmed) {
            return;
        }

        // Ambil tombol submit
        const submitButton = createTaskForm.querySelector('.submit-btn');

        // Ubah tombol jadi loading
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = isEditMode ? 'Updating...' : 'Creating...';
        }

        // Tampilkan info proses
        showMessage(isEditMode ? 'Updating task...' : 'Creating task...', 'success');

        // Kirim ke backend PHP
        submitTaskToBackend(taskData)
            .then(function (result) {
                console.log('Task backend result:', result);

                if (!result) return;

                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Failed to save task.');
                }

                // Kalau berhasil, tampilkan pesan sukses
                showMessage(
                    isEditMode
                        ? 'Task updated successfully. Redirecting to task detail...'
                        : 'Task created successfully. Redirecting to task detail...',
                    'success'
                );

                // Reset form kalau mode create, biar siap buat task baru lagi
                if (!isEditMode) {
                    createTaskForm.reset();
                }

                // Pindah ke dashboard agar task baru langsung terlihat
                setTimeout(function () {
                    const targetTaskId = isEditMode ? editTaskId : result.data.task_id;

                    if (targetTaskId) {
                        window.location.href = `${window.FlowlistRoutes.taskDetail}?id=${targetTaskId}`;
                    } else {
                        window.location.href = window.FlowlistRoutes.taskList;
                    }
                }, 1000);;
            })
            .catch(function (error) {
                // Kalau ada error, tampilkan pesan
                console.error('Create task error:', error);
                showMessage(error.message || 'Unable to connect to the PHP server. Please try again later.', 'error');

                // Aktifkan lagi tombol submit
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = isEditMode ? 'Update Task' : 'Create Task';
                }
            });
    });
}

// ===============================
// HAPUS ERROR SAAT USER MENGETIK
// Input kembali normal setelah diperbaiki
// ===============================
const allInputs = [
    taskTitleInput,
    taskDescriptionInput,
    taskStatusInput,
    taskPriorityInput,
    taskCategoryInput,
    taskDeadlineInput,
    taskAssigneeInput,
    taskTagsInput
];

allInputs.forEach(function (input) {
    if (!input) return;

    input.addEventListener('input', function () {
        input.classList.remove('input-error');

        // Hapus pesan error lama saat user mulai memperbaiki input
        if (formMessage && formMessage.classList.contains('error')) {
            formMessage.textContent = '';
            formMessage.className = 'form-message';
        }
    });

    input.addEventListener('change', function () {
        input.classList.remove('input-error');

        // Hapus pesan error lama saat user mengganti select / date
        if (formMessage && formMessage.classList.contains('error')) {
            formMessage.textContent = '';
            formMessage.className = 'form-message';
        }
    });
});

// ===============================
// INIT EDIT MODE
// ===============================
document.addEventListener('DOMContentLoaded', function () {
    loadTaskForEdit();
});