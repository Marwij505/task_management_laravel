// ===============================
// FLOWLIST CALENDAR PAGE JS
// Versi database PHP
// ===============================

// Ambil elemen judul bulan dan tahun
const calendarMonth = document.getElementById('calendarMonth');

// Ambil elemen grid kalender
const calendarGrid = document.getElementById('calendarGrid');

// Ambil tombol navigasi bulan
const prevMonthBtn = document.getElementById('prevMonthBtn');
const nextMonthBtn = document.getElementById('nextMonthBtn');
const todayBtn = document.getElementById('todayBtn');

// Ambil tanggal hari ini dari sistem
const today = new Date();

// Simpan bulan dan tahun yang sedang aktif
let currentMonth = today.getMonth();
let currentYear = today.getFullYear();

// Simpan task dari database untuk bulan aktif
let calendarTasks = [];

// Daftar nama bulan
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// ===============================
// UPDATE JUDUL BULAN
// ===============================
function updateCalendarTitle(month, year) {
    if (!calendarMonth) return;

    calendarMonth.textContent = `${monthNames[month]} ${year}`;
}

// ===============================
// STATE LOADING / ERROR
// ===============================
function renderCalendarState(iconClass, message) {
    if (!calendarGrid) return;

    calendarGrid.innerHTML = `
        <div class="calendar-state">
            <i class="${iconClass}"></i>
            <span>${message}</span>
        </div>
    `;
}

// ===============================
// AMBIL KEY TANGGAL
// Format dari database biasanya YYYY-MM-DD
// ===============================
function getDateKey(dateString) {
    if (!dateString) return '';

    return String(dateString).slice(0, 10);
}

// ===============================
// AMBIL SEMUA TASK UNTUK TANGGAL TERTENTU
// ===============================
function getTasksForDate(day, month, year) {
    const dateKey = [
        year,
        String(month + 1).padStart(2, '0'),
        String(day).padStart(2, '0')
    ].join('-');

    return calendarTasks.filter(function (task) {
        return getDateKey(task.deadline) === dateKey;
    });
}

// ===============================
// TENTUKAN CLASS PRIORITY
// ===============================
function getPriorityClass(priority) {
    if (priority === 'high') return 'high';
    if (priority === 'medium') return 'medium';
    if (priority === 'low') return 'low';
    return 'low';
}

// ===============================
// AMBIL STATUS TAMPILAN TASK
// ===============================
function getDisplayStatus(task) {
    // Pakai effective_status dari backend.
    // Fallback ke status asli jika backend belum mengirim data baru.
    return task.effective_status || task.status || 'pending';
}

// ===============================
// FORMAT STATUS UNTUK TOOLTIP KALENDER
// ===============================
function formatStatusLabel(status) {
    const labels = {
        'pending': 'Pending',
        'in-progress': 'In Progress',
        'completed': 'Completed',
        'due-today': 'Due Today',
        'overdue': 'Overdue'
    };

    return labels[status] || status;
}

// ===============================
// POTONG JUDUL BIAR RAPI DI CELL KALENDER
// ===============================
function shortenTitle(text, maxLength = 18) {
    const safeText = String(text || 'Untitled task');

    if (safeText.length <= maxLength) return safeText;

    return safeText.slice(0, maxLength) + '...';
}

// ===============================
// FORMAT TANGGAL UNTUK TAMPILAN
// ===============================  
function formatDate(dateString, fallback = '-') {
    if (window.FlowlistPreferences) {
        return window.FlowlistPreferences.formatDate(dateString, fallback);
    }

    return dateString || fallback;
}

// ===============================
// BUAT ELEMEN TASK KECIL DI DALAM KALENDER
// ===============================
function createTaskItem(task) {
    const taskItem = document.createElement('div');

    // Status tampilan untuk kalender.
    // Overdue dan due-today hanya status tampilan, bukan status asli database.
    const displayStatus = getDisplayStatus(task);

    taskItem.className = `calendar-task ${getPriorityClass(task.priority)} ${displayStatus}`;

    if (displayStatus === 'completed') {
        taskItem.classList.add('completed');
    }

    taskItem.title = `${task.title || 'Untitled task'} - ${formatStatusLabel(displayStatus)}`;

    // Saat task diklik, buka halaman detail
    taskItem.addEventListener('click', function (event) {
        event.stopPropagation();
        window.location.href = `${window.FlowlistRoutes.taskDetail}?id=${task.id}`;
    });

    const taskDot = document.createElement('span');
    taskDot.className = 'task-dot';

    const taskText = document.createElement('span');
    taskText.className = 'task-text';
    taskText.textContent = shortenTitle(task.title);

    taskItem.appendChild(taskDot);
    taskItem.appendChild(taskText);

    return taskItem;
}

// ===============================
// BUAT 1 CELL TANGGAL
// ===============================
function createDayCell(day, month, year) {
    const dayCell = document.createElement('div');
    dayCell.classList.add('calendar-day');

    // Tandai hari ini
    if (
        day === today.getDate() &&
        month === today.getMonth() &&
        year === today.getFullYear()
    ) {
        dayCell.classList.add('active-day');
    }

    const dayNumber = document.createElement('div');
    dayNumber.className = 'day-number';
    dayNumber.textContent = day;

    const dayTasks = document.createElement('div');
    dayTasks.className = 'day-tasks';

    const tasks = getTasksForDate(day, month, year);

    // Tampilkan maksimal 2 task agar cell tetap rapi
    const visibleTasks = tasks.slice(0, 2);

    visibleTasks.forEach(function (task) {
        dayTasks.appendChild(createTaskItem(task));
    });

    // Kalau task lebih dari 2, tampilkan jumlah sisanya
    if (tasks.length > 2) {
        const moreTask = document.createElement('div');
        moreTask.className = 'more-task-count';
        moreTask.textContent = `+${tasks.length - 2} more`;

        moreTask.addEventListener('click', function () {
            window.location.href = window.FlowlistRoutes.taskList;
        });

        dayTasks.appendChild(moreTask);
    }

    dayCell.appendChild(dayNumber);
    dayCell.appendChild(dayTasks);

    return dayCell;
}

// ===============================
// RENDER KALENDER
// ===============================
function renderCalendar(month, year) {
    if (!calendarGrid) return;

    calendarGrid.innerHTML = '';
    updateCalendarTitle(month, year);

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Cell kosong sebelum tanggal 1
    for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.classList.add('calendar-day', 'empty');
        calendarGrid.appendChild(emptyCell);
    }

    // Cell tanggal
    for (let day = 1; day <= daysInMonth; day++) {
        calendarGrid.appendChild(createDayCell(day, month, year));
    }
}

// ===============================
// LOAD DATA DARI BACKEND PHP
// ===============================
function loadCalendarTasks() {
    updateCalendarTitle(currentMonth, currentYear);
    renderCalendarState('fa-solid fa-spinner fa-spin', 'Loading calendar tasks...');

    const apiMonth = currentMonth + 1;

    fetch(`${window.FlowlistRoutes.calendarApi}?year=${currentYear}&month=${apiMonth}`)
        .then(function (response) {
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            if (!response.ok) {
                throw new Error('Calendar request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                throw new Error(data.message || 'Failed to load calendar tasks.');
            }

            calendarTasks = data.tasks || [];

            renderCalendar(currentMonth, currentYear);
        })
        .catch(function (error) {
            console.error('Calendar error:', error);
            calendarTasks = [];
            renderCalendarState('fa-regular fa-calendar-xmark', 'Failed to load calendar data.');
        });
}

// ===============================
// EVENT: BULAN SEBELUMNYA
// ===============================
if (prevMonthBtn) {
    prevMonthBtn.addEventListener('click', function () {
        currentMonth--;

        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }

        loadCalendarTasks();
    });
}

// ===============================
// EVENT: BULAN BERIKUTNYA
// ===============================
if (nextMonthBtn) {
    nextMonthBtn.addEventListener('click', function () {
        currentMonth++;

        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }

        loadCalendarTasks();
    });
}

// ===============================
// EVENT: KEMBALI KE BULAN HARI INI
// ===============================
if (todayBtn) {
    todayBtn.addEventListener('click', function () {
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();

        loadCalendarTasks();
    });
}

// ===============================
// RENDER PERTAMA SAAT HALAMAN DIBUKA
// ===============================
document.addEventListener('DOMContentLoaded', function () {
    loadCalendarTasks();
});
