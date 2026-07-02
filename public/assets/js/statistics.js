// ===============================
// FLOWLIST STATISTICS PAGE JS
// Versi database PHP
// ===============================

// Ambil elemen summary card dari HTML
const completionRateEl = document.getElementById('completionRate');
const completionRateNoteEl = document.getElementById('completionRateNote');

const avgTasksDayEl = document.getElementById('avgTasksDay');
const avgTasksDayNoteEl = document.getElementById('avgTasksDayNote');

const tasksCompletedEl = document.getElementById('tasksCompleted');
const tasksCompletedNoteEl = document.getElementById('tasksCompletedNote');

const avgCompletionTimeEl = document.getElementById('avgCompletionTime');
const avgCompletionTimeNoteEl = document.getElementById('avgCompletionTimeNote');

// Ambil canvas chart dari HTML
const weeklyCanvas = document.getElementById('weeklyChart');
const monthlyCanvas = document.getElementById('monthlyChart');
const priorityCanvas = document.getElementById('priorityChart');
const categoryCanvas = document.getElementById('categoryChart');

// Ambil message state
const statisticsMessage = document.getElementById('statisticsMessage');

// ===============================
// DATA DARI BACKEND
// ===============================
let weeklyData = {
    labels: [],
    completed: [],
    pending: []
};

let monthlyData = {
    labels: [],
    completed: []
};

let priorityData = [];
let categoryData = [];

// ===============================
// MESSAGE STATE
// ===============================
function showStatisticsMessage(message, type = '') {
    if (!statisticsMessage) return;

    statisticsMessage.textContent = message;
    statisticsMessage.className = `statistics-message show ${type}`.trim();
}

function hideStatisticsMessage() {
    if (!statisticsMessage) return;

    statisticsMessage.textContent = '';
    statisticsMessage.className = 'statistics-message';
}

// ===============================
// HELPER TEXT
// ===============================
function setText(element, value, fallback = '-') {
    if (!element) return;
    element.textContent = value ?? fallback;
}

// ===============================
// ESCAPE HTML
// Dipakai untuk teks yang masuk ke tooltip innerHTML
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
// RENDER SUMMARY CARDS
// ===============================
function renderSummary(summary) {
    const completionRate = Number(summary.completionRate) || 0;
    const avgTasksPerDay = Number(summary.avgTasksPerDay) || 0;
    const completedThisMonth = Number(summary.tasksCompletedThisMonth) || 0;
    const avgCompletionTime = Number(summary.avgCompletionTime) || 0;

    setText(completionRateEl, `${completionRate}%`);
    setText(completionRateNoteEl, `${summary.completedTasks || 0} of ${summary.totalTasks || 0} tasks completed`);

    setText(avgTasksDayEl, avgTasksPerDay);
    setText(avgTasksDayNoteEl, 'Created in the last 7 days');

    setText(tasksCompletedEl, completedThisMonth);
    setText(tasksCompletedNoteEl, 'Completed this month');

    setText(avgCompletionTimeEl, `${avgCompletionTime}d`);
    setText(avgCompletionTimeNoteEl, 'Average completion time');
}

// ===============================
// TOOLTIP
// ===============================
let activeTooltip = null;

function removeTooltip() {
    if (activeTooltip) {
        activeTooltip.remove();
        activeTooltip = null;
    }
}

function showTooltip(canvas, x, y, lines) {
    removeTooltip();

    const tooltip = document.createElement('div');

    tooltip.style.position = 'absolute';
    tooltip.style.background = '#ffffff';
    tooltip.style.border = '1px solid #e4e8ee';
    tooltip.style.borderRadius = '10px';
    tooltip.style.padding = '12px 14px';
    tooltip.style.boxShadow = '0 10px 22px rgba(22, 41, 56, 0.10)';
    tooltip.style.pointerEvents = 'none';
    tooltip.style.zIndex = '20';
    tooltip.style.minWidth = '140px';
    tooltip.style.maxWidth = '220px';

    tooltip.innerHTML = lines
        .map(function (line, index) {
            const margin = index === lines.length - 1 ? '0' : '6px';
            return `<div style="margin-bottom:${margin}; font-size:0.95rem; color:#162938; white-space:nowrap;">${line}</div>`;
        })
        .join('');

    const parent = canvas.parentElement;
    parent.style.position = 'relative';

    parent.appendChild(tooltip);

    const tooltipRect = tooltip.getBoundingClientRect();

    let finalX = x;
    let finalY = y;

    if (finalX + tooltipRect.width > parent.clientWidth - 12) {
        finalX = parent.clientWidth - tooltipRect.width - 12;
    }

    if (finalX < 12) {
        finalX = 12;
    }

    if (finalY + tooltipRect.height > parent.clientHeight - 12) {
        finalY = parent.clientHeight - tooltipRect.height - 12;
    }

    if (finalY < 12) {
        finalY = 12;
    }

    tooltip.style.left = `${finalX}px`;
    tooltip.style.top = `${finalY}px`;

    activeTooltip = tooltip;
}

// ===============================
// COMMON HELPERS
// ===============================
function drawGrid(ctx, chartWidth, chartHeight, left, top, rows, cols) {
    ctx.save();
    ctx.strokeStyle = '#d1d5db';
    ctx.lineWidth = 1;
    ctx.setLineDash([4, 4]);

    for (let i = 0; i <= rows; i++) {
        const y = top + (chartHeight / rows) * i;
        ctx.beginPath();
        ctx.moveTo(left, y);
        ctx.lineTo(left + chartWidth, y);
        ctx.stroke();
    }

    for (let i = 0; i <= cols; i++) {
        const x = left + (chartWidth / Math.max(cols, 1)) * i;
        ctx.beginPath();
        ctx.moveTo(x, top);
        ctx.lineTo(x, top + chartHeight);
        ctx.stroke();
    }

    ctx.restore();
}

function animateChart(drawFn, duration) {
    const start = performance.now();

    function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);

        drawFn(eased);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}

// ===============================
// WEEKLY BAR CHART
// ===============================
function drawWeeklyChart(animationProgress = 1) {
    if (!weeklyCanvas) return;

    const ctx = weeklyCanvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const cssWidth = weeklyCanvas.clientWidth || 700;
    const cssHeight = 380;

    weeklyCanvas.width = cssWidth * dpr;
    weeklyCanvas.height = cssHeight * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssWidth, cssHeight);

    const labels = weeklyData.labels || [];
    const completed = weeklyData.completed || [];
    const pending = weeklyData.pending || [];

    const maxDataValue = Math.max(...completed, ...pending, 1);
    const maxValue = Math.max(4, Math.ceil(maxDataValue / 2) * 2);

    const paddingLeft = 58;
    const paddingRight = 18;
    const paddingTop = 28;
    const paddingBottom = 64;

    const chartWidth = cssWidth - paddingLeft - paddingRight;
    const chartHeight = cssHeight - paddingTop - paddingBottom;
    const rows = 4;
    const cols = labels.length || 1;

    drawGrid(ctx, chartWidth, chartHeight, paddingLeft, paddingTop, rows, cols);

    ctx.fillStyle = '#6b7280';
    ctx.font = '14px Poppins';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';

    for (let i = 0; i <= rows; i++) {
        const value = maxValue - (maxValue / rows) * i;
        const y = paddingTop + (chartHeight / rows) * i;
        ctx.fillText(Math.round(value), paddingLeft - 10, y);
    }

    const groupWidth = chartWidth / cols;
    const barWidth = 14;
    const gap = 6;

    weeklyCanvas._hoverAreas = [];

    labels.forEach(function (label, index) {
        const completedValue = Number(completed[index]) || 0;
        const pendingValue = Number(pending[index]) || 0;

        const xCenter = paddingLeft + groupWidth * index + groupWidth / 2;

        const completedHeight = ((completedValue / maxValue) * chartHeight) * animationProgress;
        const pendingHeight = ((pendingValue / maxValue) * chartHeight) * animationProgress;

        const completedX = xCenter - barWidth - gap / 2;
        const pendingX = xCenter + gap / 2;

        const completedY = paddingTop + chartHeight - completedHeight;
        const pendingY = paddingTop + chartHeight - pendingHeight;

        if (weeklyCanvas._activeIndex === index) {
            ctx.fillStyle = 'rgba(17, 24, 39, 0.12)';
            ctx.fillRect(xCenter - groupWidth * 0.36, paddingTop, groupWidth * 0.72, chartHeight);
        }

        ctx.fillStyle = '#22c55e';
        ctx.fillRect(completedX, completedY, barWidth, completedHeight);

        ctx.fillStyle = '#f59e0b';
        ctx.fillRect(pendingX, pendingY, barWidth, pendingHeight);

        ctx.fillStyle = '#6b7280';
        ctx.font = '14px Poppins';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        ctx.fillText(label, xCenter, paddingTop + chartHeight + 10);

        weeklyCanvas._hoverAreas.push({
            label,
            completed: completedValue,
            pending: pendingValue,
            index,
            x: xCenter - groupWidth * 0.36,
            y: paddingTop,
            width: groupWidth * 0.72,
            height: chartHeight
        });
    });

    const legendY = cssHeight - 22;

    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    ctx.font = '14px Poppins';

    ctx.fillStyle = '#22c55e';
    ctx.fillRect(cssWidth / 2 - 80, legendY - 7, 14, 14);
    ctx.fillStyle = '#22c55e';
    ctx.fillText('Completed', cssWidth / 2 - 60, legendY);

    ctx.fillStyle = '#f59e0b';
    ctx.fillRect(cssWidth / 2 + 25, legendY - 7, 14, 14);
    ctx.fillStyle = '#f59e0b';
    ctx.fillText('Pending', cssWidth / 2 + 45, legendY);
}

// ===============================
// MONTHLY LINE CHART
// ===============================
function drawMonthlyChart(animationProgress = 1) {
    if (!monthlyCanvas) return;

    const ctx = monthlyCanvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const cssWidth = monthlyCanvas.clientWidth || 700;
    const cssHeight = 380;

    monthlyCanvas.width = cssWidth * dpr;
    monthlyCanvas.height = cssHeight * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssWidth, cssHeight);

    const labels = monthlyData.labels || [];
    const completed = monthlyData.completed || [];

    const maxDataValue = Math.max(...completed, 1);
    const maxValue = Math.max(4, Math.ceil(maxDataValue / 2) * 2);

    const paddingLeft = 58;
    const paddingRight = 22;
    const paddingTop = 26;
    const paddingBottom = 64;

    const chartWidth = cssWidth - paddingLeft - paddingRight;
    const chartHeight = cssHeight - paddingTop - paddingBottom;
    const rows = 4;
    const cols = Math.max(labels.length - 1, 1);

    drawGrid(ctx, chartWidth, chartHeight, paddingLeft, paddingTop, rows, cols);

    ctx.fillStyle = '#6b7280';
    ctx.font = '14px Poppins';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';

    for (let i = 0; i <= rows; i++) {
        const value = maxValue - (maxValue / rows) * i;
        const y = paddingTop + (chartHeight / rows) * i;
        ctx.fillText(Math.round(value), paddingLeft - 10, y);
    }

    const points = labels.map(function (label, index) {
        const value = Number(completed[index]) || 0;
        const x = labels.length === 1
            ? paddingLeft + chartWidth / 2
            : paddingLeft + (chartWidth / cols) * index;

        const targetY = paddingTop + chartHeight - (value / maxValue) * chartHeight;
        const startY = paddingTop + chartHeight;
        const y = startY + (targetY - startY) * animationProgress;

        return { label, value, x, y };
    });

    monthlyCanvas._hoverBands = points.map(function (point, index) {
        const left = index === 0 ? paddingLeft : (points[index - 1].x + point.x) / 2;
        const right = index === points.length - 1 ? paddingLeft + chartWidth : (point.x + points[index + 1].x) / 2;

        return {
            label: point.label,
            value: point.value,
            x: point.x,
            y: point.y,
            left,
            right,
            centerX: point.x
        };
    });

    function getControlPoint(current, previous, next, reverse) {
        const p = previous || current;
        const n = next || current;

        const smoothing = 0.22;
        const dx = n.x - p.x;
        const dy = n.y - p.y;

        const angle = Math.atan2(dy, dx) + (reverse ? Math.PI : 0);
        const length = Math.sqrt(dx * dx + dy * dy) * smoothing;

        return {
            x: current.x + Math.cos(angle) * length,
            y: current.y + Math.sin(angle) * length
        };
    }

    if (points.length > 0) {
        ctx.beginPath();
        ctx.moveTo(points[0].x, paddingTop + chartHeight);
        ctx.lineTo(points[0].x, points[0].y);

        for (let i = 0; i < points.length - 1; i++) {
            const current = points[i];
            const next = points[i + 1];
            const cp1 = getControlPoint(current, points[i - 1], next, false);
            const cp2 = getControlPoint(next, current, points[i + 2], true);
            ctx.bezierCurveTo(cp1.x, cp1.y, cp2.x, cp2.y, next.x, next.y);
        }

        ctx.lineTo(points[points.length - 1].x, paddingTop + chartHeight);
        ctx.closePath();

        const gradient = ctx.createLinearGradient(0, paddingTop, 0, paddingTop + chartHeight);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.18)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.03)');
        ctx.fillStyle = gradient;
        ctx.fill();

        ctx.beginPath();
        ctx.moveTo(points[0].x, points[0].y);

        for (let i = 0; i < points.length - 1; i++) {
            const current = points[i];
            const next = points[i + 1];
            const cp1 = getControlPoint(current, points[i - 1], next, false);
            const cp2 = getControlPoint(next, current, points[i + 2], true);
            ctx.bezierCurveTo(cp1.x, cp1.y, cp2.x, cp2.y, next.x, next.y);
        }

        ctx.strokeStyle = '#3b82f6';
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();

        points.forEach(function (point, index) {
            const isActive = monthlyCanvas._activeIndex === index;

            if (isActive) {
                ctx.beginPath();
                ctx.fillStyle = 'rgba(59, 130, 246, 0.18)';
                ctx.arc(point.x, point.y, 9, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.beginPath();
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = '#3b82f6';
            ctx.lineWidth = 2;
            ctx.arc(point.x, point.y, 4, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
        });
    }

    ctx.fillStyle = '#6b7280';
    ctx.font = '14px Poppins';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';

    points.forEach(function (point) {
        ctx.fillText(point.label, point.x, paddingTop + chartHeight + 10);
    });

    const legendY = cssHeight - 22;

    ctx.beginPath();
    ctx.strokeStyle = '#3b82f6';
    ctx.lineWidth = 2;
    ctx.moveTo(cssWidth / 2 - 40, legendY);
    ctx.lineTo(cssWidth / 2 - 20, legendY);
    ctx.stroke();

    ctx.beginPath();
    ctx.fillStyle = '#ffffff';
    ctx.strokeStyle = '#3b82f6';
    ctx.lineWidth = 2;
    ctx.arc(cssWidth / 2 - 30, legendY, 3, 0, Math.PI * 2);
    ctx.fill();
    ctx.stroke();

    ctx.fillStyle = '#3b82f6';
    ctx.textAlign = 'left';
    ctx.textBaseline = 'middle';
    ctx.fillText('Tasks Completed', cssWidth / 2 - 12, legendY);
}

// ===============================
// PIE CHART GENERIC
// ===============================
function drawPieChart(canvas, data, animationProgress = 1) {
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const cssWidth = canvas.clientWidth || 700;
    const cssHeight = 380;

    canvas.width = cssWidth * dpr;
    canvas.height = cssHeight * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssWidth, cssHeight);

    const total = data.reduce(function (sum, item) {
        return sum + Number(item.value || 0);
    }, 0);

    if (total <= 0) {
        ctx.fillStyle = '#6b7280';
        ctx.font = '16px Poppins';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('No data available', cssWidth / 2, cssHeight / 2);
        canvas._hoverAreas = [];
        return;
    }

    const centerX = cssWidth / 2;
    const centerY = cssHeight / 2 + 20;
    const radius = Math.min(cssWidth, cssHeight) * 0.28;

    let startAngle = 0;
    canvas._hoverAreas = [];

    data.forEach(function (item) {
        const value = Number(item.value || 0);

        if (value <= 0) return;

        const fullSliceAngle = (value / total) * Math.PI * 2;
        const sliceAngle = fullSliceAngle * animationProgress;
        const endAngle = startAngle + sliceAngle;
        const midAngle = startAngle + fullSliceAngle / 2;

        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fillStyle = item.color;
        ctx.fill();
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 1;
        ctx.stroke();

        const percent = Math.round((value / total) * 100);

        if (animationProgress > 0.9) {
            const labelRadius = radius + 38;
            const labelX = centerX + Math.cos(midAngle) * labelRadius;
            const labelY = centerY + Math.sin(midAngle) * labelRadius;

            ctx.fillStyle = item.color;
            ctx.font = '14px Poppins';
            ctx.textAlign = midAngle > Math.PI / 2 && midAngle < (Math.PI * 1.5) ? 'right' : 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(`${item.label}: ${percent}%`, labelX, labelY);
        }

        canvas._hoverAreas.push({
            label: item.label,
            value,
            startAngle,
            endAngle: startAngle + fullSliceAngle,
            centerX,
            centerY,
            radius
        });

        startAngle += fullSliceAngle;
    });
}

// ===============================
// HOVER EVENTS
// ===============================
function setupHoverEvents() {
    if (weeklyCanvas) {
        weeklyCanvas.addEventListener('mousemove', function (event) {
            const rect = weeklyCanvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            let found = null;
            weeklyCanvas._activeIndex = null;

            if (weeklyCanvas._hoverAreas) {
                weeklyCanvas._hoverAreas.forEach(function (area) {
                    if (x >= area.x && x <= area.x + area.width && y >= area.y && y <= area.y + area.height) {
                        found = area;
                    }
                });
            }

            if (found) {
                weeklyCanvas._activeIndex = found.index;
                drawWeeklyChart(1);

                showTooltip(weeklyCanvas, found.x + found.width - 10, 40, [
                    `<strong>${escapeHTML(found.label)}</strong>`,
                    `<span style="color:#22c55e;">Completed : ${escapeHTML(found.completed)}</span>`,
                    `<span style="color:#f59e0b;">Pending : ${escapeHTML(found.pending)}</span>`
                ]);
            } else {
                drawWeeklyChart(1);
                removeTooltip();
            }
        });

        weeklyCanvas.addEventListener('mouseleave', function () {
            weeklyCanvas._activeIndex = null;
            drawWeeklyChart(1);
            removeTooltip();
        });
    }

    if (monthlyCanvas) {
        monthlyCanvas.addEventListener('mousemove', function (event) {
            const rect = monthlyCanvas.getBoundingClientRect();
            const x = event.clientX - rect.left;

            let found = null;
            monthlyCanvas._activeIndex = null;

            if (monthlyCanvas._hoverBands) {
                monthlyCanvas._hoverBands.forEach(function (band, index) {
                    if (x >= band.left && x <= band.right) {
                        found = band;
                        monthlyCanvas._activeIndex = index;
                    }
                });
            }

            if (found) {
                drawMonthlyChart(1);

                showTooltip(monthlyCanvas, found.centerX + 14, found.y + 18, [
                    `<strong>${escapeHTML(found.label)}</strong>`,
                    `<span style="color:#3b82f6;">Tasks Completed : ${escapeHTML(found.value)}</span>`
                ]);
            } else {
                monthlyCanvas._activeIndex = null;
                drawMonthlyChart(1);
                removeTooltip();
            }
        });

        monthlyCanvas.addEventListener('mouseleave', function () {
            monthlyCanvas._activeIndex = null;
            drawMonthlyChart(1);
            removeTooltip();
        });
    }

    addPieHover(priorityCanvas);
    addPieHover(categoryCanvas);
}

function addPieHover(canvas) {
    if (!canvas) return;

    canvas.addEventListener('mousemove', function (event) {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        let found = null;

        if (canvas._hoverAreas) {
            canvas._hoverAreas.forEach(function (slice) {
                const dx = x - slice.centerX;
                const dy = y - slice.centerY;
                const distance = Math.sqrt(dx * dx + dy * dy);

                let angle = Math.atan2(dy, dx);
                if (angle < 0) angle += Math.PI * 2;

                if (distance <= slice.radius && angle >= slice.startAngle && angle <= slice.endAngle) {
                    found = slice;
                }
            });
        }

        if (found) {
            showTooltip(canvas, x + 16, y - 10, [
                `<strong>${escapeHTML(found.label)}</strong>`,
                `${Number(found.value) || 0}`
            ]);
        } else {
            removeTooltip();
        }
    });

    canvas.addEventListener('mouseleave', removeTooltip);
}

// ===============================
// RENDER FUNCTIONS
// ===============================
function renderAllCharts() {
    drawWeeklyChart(1);
    drawMonthlyChart(1);
    drawPieChart(priorityCanvas, priorityData, 1);
    drawPieChart(categoryCanvas, categoryData, 1);
}

function animateAllCharts() {
    animateChart(function (progress) {
        drawWeeklyChart(progress);
        drawMonthlyChart(progress);
        drawPieChart(priorityCanvas, priorityData, progress);
        drawPieChart(categoryCanvas, categoryData, progress);
    }, 900);
}

// ===============================
// LOAD STATISTICS DARI BACKEND PHP
// ===============================
function loadStatistics() {
    showStatisticsMessage('Loading statistics...');

    fetch(window.FlowlistRoutes.statisticsApi)
        .then(function (response) {
            if (response.status === 401) {
                window.location.href = window.FlowlistRoutes.login;
                return null;
            }

            if (!response.ok) {
                throw new Error('Statistics request failed with status ' + response.status);
            }

            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (!data.success) {
                throw new Error(data.message || 'Failed to load statistics.');
            }

            renderSummary(data.summary || {});

            weeklyData = data.weekly || weeklyData;
            monthlyData = data.monthly || monthlyData;
            priorityData = data.priority || [];
            categoryData = data.category || [];

            hideStatisticsMessage();
            animateAllCharts();
        })
        .catch(function (error) {
            console.error('Statistics error:', error);
            showStatisticsMessage('Failed to load statistics data.', 'error');
        });
}

// ===============================
// INITIALIZE
// ===============================
document.addEventListener('DOMContentLoaded', function () {
    setupHoverEvents();
    loadStatistics();
});

window.addEventListener('resize', function () {
    removeTooltip();
    renderAllCharts();
});