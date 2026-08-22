<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\TaskProgressService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTaskController extends Controller
{
    public function __construct(
        private TaskProgressService $taskProgressService
    ) {
    }

    public function index(Request $request): View
    {
        /*
         * Ambil filter dari URL.
         * Contoh:
         * /admin/tasks?search=APSI&status=completed&priority=high&user_id=3
         */
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
            'priority' => trim((string) $request->query('priority', '')),
            'user_id' => trim((string) $request->query('user_id', '')),
        ];

        /*
         * Daftar user dipakai oleh:
         * - filter owner;
         * - form create task;
         * - form edit task.
         */
        $users = User::query()
            ->orderBy('full_name')
            ->orderBy('name')
            ->orderBy('username')
            ->get();

        /*
         * ============================================================
         * FLOWLIST MK2 - ADMIN TASK QUERY
         * ============================================================
         *
         * Tetap mempertahankan seluruh relationship MK1:
         * - user
         * - tags
         *
         * Tambahan MK2:
         * - subtask_total
         * - subtask_completed
         *
         * Kita hanya menghitung subtask di halaman All Tasks,
         * bukan memuat seluruh row subtask, sehingga tetap ringan.
         */
        $allTasks = Task::query()
            ->with(['user', 'tags'])
            ->withCount([
                'subtasks as subtask_total',

                'subtasks as subtask_completed' => function ($query) {
                    $query->where('is_completed', true);
                },
            ])
            ->latest('created_at')
            ->get();

        /*
         * Statistik global.
         * Status overdue dan due today dihitung dari TaskProgressService.
         *
         * Subtask TIDAK ditambahkan ke total task karena subtask adalah
         * child checklist, bukan task utama.
         */
        $stats = [
            'totalTasks' => $allTasks->count(),
            'completedTasks' => $allTasks->where('status', 'completed')->count(),
            'inProgressTasks' => $allTasks->filter(function (Task $task) {
                return $task->status === 'in-progress'
                    && $this->taskProgressService->effectiveStatus($task) === 'in-progress';
            })->count(),
            'dueTodayTasks' => $allTasks->filter(function (Task $task) {
                return $this->taskProgressService->effectiveStatus($task) === 'due-today';
            })->count(),
            'overdueTasks' => $allTasks->filter(function (Task $task) {
                return $this->taskProgressService->effectiveStatus($task) === 'overdue';
            })->count(),
        ];

        /*
         * Filter dilakukan di collection agar effective status
         * seperti overdue dan due today tetap dapat difilter.
         */
        $filteredTasks = $allTasks->filter(function (Task $task) use ($filters) {
            $effectiveStatus = $this->taskProgressService->effectiveStatus($task);

            if ($filters['status'] !== '' && $effectiveStatus !== $filters['status']) {
                return false;
            }

            if ($filters['priority'] !== '' && $task->priority !== $filters['priority']) {
                return false;
            }

            if ($filters['user_id'] !== '' && (int) $task->user_id !== (int) $filters['user_id']) {
                return false;
            }

            if ($filters['search'] !== '') {
                $tags = $task->relationLoaded('tags')
                    ? $task->tags->pluck('tag_name')->implode(' ')
                    : '';

                $owner = trim(implode(' ', [
                    $task->user?->full_name,
                    $task->user?->name,
                    $task->user?->username,
                    $task->user?->email,
                ]));

                $haystack = mb_strtolower(trim(implode(' ', [
                    $task->title,
                    $task->description,
                    $task->category,
                    $task->assignee,
                    $owner,
                    $tags,
                ])));

                return str_contains(
                    $haystack,
                    mb_strtolower($filters['search'])
                );
            }

            return true;
        })->values();

        /*
         * Ubah model Task menjadi array siap pakai Blade.
         *
         * Struktur array ini dipertahankan karena halaman
         * resources/views/admin/tasks/index.blade.php
         * memang membaca $task['...'].
         */
        $preparedTasks = $filteredTasks
            ->map(fn (Task $task) => $this->taskViewPayload($task))
            ->values();

        $paginatedTasks = $this->paginateCollection(
            $preparedTasks,
            $request,
            6
        );

        return view('admin.tasks.index', [
            'tasks' => $paginatedTasks,
            'users' => $users,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /*
         * Admin dapat membuat task untuk user tertentu.
         */
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'in-progress', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'category' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignee' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        $task = new Task();

        $task->forceFill([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'description' => $this->nullableString($validated['description'] ?? null),
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'category' => $this->nullableString($validated['category'] ?? null),
            'deadline' => $validated['deadline'] ?? null,
            'progress' => $this->normalizeStoredProgress(
                $validated['status'],
                (int) ($validated['progress'] ?? 0)
            ),
            'assignee' => $this->nullableString($validated['assignee'] ?? null),
        ])->save();

        $this->replaceTags($task, $validated['tags'] ?? '');

        /*
         * Catat aktivitas create task oleh admin.
         */
        ActivityLogService::log(
            module: 'admin_tasks',
            action: 'create',
            description: 'Admin created task: '.$task->title,
            properties: [
                'task_id' => $task->id,
                'title' => $task->title,
                'owner_user_id' => $task->user_id,
                'status' => $task->status,
                'priority' => $task->priority,
                'deadline' => (string) $task->deadline,
            ],
            targetUserId: (int) $task->user_id
        );

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task has been created successfully.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        /*
         * Admin dapat mengubah task milik user mana pun.
         */
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'in-progress', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'category' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignee' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        $before = $task->only([
            'user_id',
            'title',
            'status',
            'priority',
            'category',
            'deadline',
            'progress',
            'assignee',
        ]);

        $task->forceFill([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'description' => $this->nullableString($validated['description'] ?? null),
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'category' => $this->nullableString($validated['category'] ?? null),
            'deadline' => $validated['deadline'] ?? null,
            'progress' => $this->normalizeStoredProgress(
                $validated['status'],
                (int) ($validated['progress'] ?? 0)
            ),
            'assignee' => $this->nullableString($validated['assignee'] ?? null),
        ])->save();

        $this->replaceTags($task, $validated['tags'] ?? '');

        /*
         * Catat aktivitas update task oleh admin.
         */
        ActivityLogService::log(
            module: 'admin_tasks',
            action: 'update',
            description: 'Admin updated task: '.$task->title,
            properties: [
                'task_id' => $task->id,
                'before' => $before,
                'after' => $task->only([
                    'user_id',
                    'title',
                    'status',
                    'priority',
                    'category',
                    'deadline',
                    'progress',
                    'assignee',
                ]),
            ],
            targetUserId: (int) $task->user_id
        );

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task has been updated successfully.');
    }

    public function complete(Task $task): RedirectResponse
    {
        /*
         * Shortcut admin untuk menyelesaikan task.
         */
        $task->forceFill([
            'status' => 'completed',
            'progress' => 100,
        ])->save();

        /*
         * Catat aktivitas mark completed.
         */
        ActivityLogService::log(
            module: 'admin_tasks',
            action: 'complete',
            description: 'Admin completed task: '.$task->title,
            properties: [
                'task_id' => $task->id,
                'title' => $task->title,
                'owner_user_id' => $task->user_id,
            ],
            targetUserId: (int) $task->user_id
        );

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task has been marked as completed.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        /*
         * Simpan seluruh data penting sebelum task dihapus.
         */
        $deletedTaskData = [
            'task_id' => $task->id,
            'title' => $task->title,
            'owner_user_id' => $task->user_id,
            'status' => $task->status,
            'priority' => $task->priority,
            'category' => $task->category,

            /*
             * Audit tambahan MK2.
             * Database akan menghapus subtask dengan cascade.
             */
            'subtasks_count' => $task->subtasks()->count(),
        ];

        $taskTitle = $task->title;
        $ownerUserId = (int) $task->user_id;

        /*
         * Foreign key subtasks.task_id memakai cascadeOnDelete(),
         * sehingga seluruh checklist anak ikut terhapus otomatis.
         */
        $task->delete();

        /*
         * Owner user masih ada, sehingga targetUserId tetap dapat digunakan.
         */
        ActivityLogService::log(
            module: 'admin_tasks',
            action: 'delete',
            description: 'Admin deleted task: '.$taskTitle,
            properties: $deletedTaskData,
            targetUserId: $ownerUserId
        );

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task has been deleted successfully.');
    }

    /**
     * Membuat payload untuk card/form pada halaman Admin All Tasks.
     */
    private function taskViewPayload(Task $task): array
    {
        $effectiveStatus = $this->taskProgressService->effectiveStatus($task);

        $subtaskTotal = (int) ($task->subtask_total ?? 0);
        $subtaskCompleted = (int) ($task->subtask_completed ?? 0);

        $checklistPercentage = $subtaskTotal > 0
            ? (int) round(($subtaskCompleted / $subtaskTotal) * 100)
            : 0;

        return [
            'id' => $task->id,
            'user_id' => $task->user_id,
            'title' => $task->title,
            'description' => $task->description ?? '',
            'status' => $task->status,
            'effective_status' => $effectiveStatus,
            'status_class' => $effectiveStatus === 'in-progress' ? 'progress' : $effectiveStatus,
            'status_label' => $this->taskProgressService->statusLabel($task),
            'priority' => $task->priority,
            'category' => $task->category ?? '',
            'deadline_input' => $task->deadline
                ? Carbon::parse($task->deadline)->format('Y-m-d')
                : '',
            'deadline_label' => $task->deadline
                ? Carbon::parse($task->deadline)->format('d M Y')
                : 'No deadline',
            'progress' => $this->taskProgressService->progress($task),
            'stored_progress' => (int) $task->progress,
            'assignee' => $task->assignee ?? '',
            'owner_name' => $task->user?->full_name
                ?: $task->user?->name
                ?: $task->user?->username
                ?: 'Unknown User',
            'owner_email' => $task->user?->email ?: '-',
            'tags' => $task->tags->pluck('tag_name')->values()->all(),
            'tags_text' => $task->tags->pluck('tag_name')->implode(', '),

            /*
             * ========================================================
             * FLOWLIST MK2 - CHECKLIST SUMMARY
             * ========================================================
             */
            'subtask_total' => $subtaskTotal,
            'subtask_completed' => $subtaskCompleted,
            'checklist_percentage' => $checklistPercentage,
        ];
    }

    private function paginateCollection(
        Collection $items,
        Request $request,
        int $perPage
    ): LengthAwarePaginator {
        /*
         * Pagination manual karena data sudah difilter melalui collection.
         */
        $page = LengthAwarePaginator::resolveCurrentPage();

        $itemsForPage = $items
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return new LengthAwarePaginator(
            $itemsForPage,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function normalizeStoredProgress(
        string $status,
        int $progress
    ): int {
        /*
         * Pending selalu 0.
         * Completed selalu 100.
         * In progress disimpan maksimal 99 agar tidak terlihat completed palsu.
         */
        return match ($status) {
            'pending' => 0,
            'completed' => 100,
            default => min(99, max(0, $progress)),
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === ''
            ? null
            : $value;
    }

    private function replaceTags(
        Task $task,
        string $rawTags
    ): void {
        /*
         * Format input tags:
         * urgent, frontend, revision
         *
         * Data lama dihapus lalu diganti tags baru.
         */
        $task->tags()->delete();

        $tags = collect(explode(',', $rawTags))
            ->map(fn (string $tag) => mb_substr(trim($tag), 0, 50))
            ->filter()
            ->unique()
            ->values();

        foreach ($tags as $tagName) {
            $task->tags()->create([
                'tag_name' => $tagName,
            ]);
        }
    }
}
