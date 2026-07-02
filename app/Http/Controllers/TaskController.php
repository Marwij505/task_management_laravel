<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\TaskProgressService;

class TaskController extends Controller
{
    private TaskProgressService $taskProgressService;

    public function __construct(TaskProgressService $taskProgressService)
    {
        $this->taskProgressService = $taskProgressService;
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where('user_id', $request->user()->id)
            ->with('tags')
            ->orderByRaw('CASE WHEN deadline IS NULL THEN 1 ELSE 0 END')
            ->orderBy('deadline')
            ->latest('created_at')
            ->get()
            ->map(fn (Task $task) => $this->taskPayload($task))
            ->values();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $taskId = (int) $request->query('id', 0);

        if ($taskId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task id.',
            ], 400);
        }

        $task = Task::query()
            ->where('id', $taskId)
            ->where('user_id', $request->user()->id)
            ->with('tags')
            ->first();

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'task' => $this->taskPayload($task),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->taskValidator($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $task = DB::transaction(function () use ($request): Task {
            $status = (string) $request->input('status');

            $task = Task::create([
                'user_id' => $request->user()->id,
                'title' => trim((string) $request->input('title')),
                'description' => $this->nullableString($request->input('description')),
                'status' => $status,
                'priority' => (string) $request->input('priority'),
                'category' => $this->nullableString($request->input('category')),
                'deadline' => $request->input('deadline'),
                'progress' => $this->progressForStatus($status),
                'assignee' => $this->nullableString($request->input('assignee')),
            ]);

            $this->replaceTags($task, (string) $request->input('tags', ''));

            return $task;
        });

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
            'task_id' => $task->id,
        ], 201);
    }

    public function update(Request $request): JsonResponse
    {
        $taskId = (int) $request->input('task_id', 0);

        if ($taskId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task id.',
            ], 400);
        }

        $validator = $this->taskValidator($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $task = Task::query()
            ->where('id', $taskId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or you do not have permission to update it.',
            ], 404);
        }

        DB::transaction(function () use ($request, $task): void {
            $status = (string) $request->input('status');

            $task->update([
                'title' => trim((string) $request->input('title')),
                'description' => $this->nullableString($request->input('description')),
                'status' => $status,
                'priority' => (string) $request->input('priority'),
                'category' => $this->nullableString($request->input('category')),
                'deadline' => $request->input('deadline'),
                'progress' => $this->progressForStatus($status),
                'assignee' => $this->nullableString($request->input('assignee')),
            ]);

            $this->replaceTags($task, (string) $request->input('tags', ''));
        });

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.',
            'task_id' => $task->id,
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $taskId = (int) $request->input('task_id', 0);

        if ($taskId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task id.',
            ], 400);
        }

        $task = Task::query()
            ->where('id', $taskId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $task || $task->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or already completed.',
            ], 404);
        }

        $task->update([
            'status' => 'completed',
            'progress' => 100,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task marked as completed successfully.',
            'task' => [
                'id' => $task->id,
                'status' => 'completed',
                'progress' => 100,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $taskId = (int) $request->input('task_id', 0);

        if ($taskId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task id.',
            ], 400);
        }

        $task = Task::query()
            ->where('id', $taskId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found or you do not have permission to delete it.',
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.',
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid year or month.',
            ], 400);
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $tasks = Task::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('deadline')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->latest('created_at')
            ->get()
            ->map(fn (Task $task) => $this->taskPayload($task, includeTags: false))
            ->values();

        return response()->json([
            'success' => true,
            'year' => $year,
            'month' => $month,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'tasks' => $tasks,
        ]);
    }

    private function taskValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['pending', 'in-progress', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'category' => ['nullable', 'string', 'max:100'],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'assignee' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function progressForStatus(string $status): int
    {
        /**
         * Progress tidak lagi dibuat 50% hanya karena status in-progress.
         * Progress utama sekarang dihitung otomatis dari deadline.
         *
         * Nilai ini hanya menjadi nilai awal saat task dibuat atau diupdate.
         */
        return match ($status) {
            'completed' => 100,
            default => 0,
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function replaceTags(Task $task, string $rawTags): void
    {
        $task->tags()->delete();

        $tags = collect(explode(',', $rawTags))
            ->map(fn (string $tag) => mb_substr(trim($tag), 0, 50))
            ->filter()
            ->unique()
            ->values();

        foreach ($tags as $tagName) {
            $task->tags()->create(['tag_name' => $tagName]);
        }
    }

    private function taskPayload(Task $task, bool $includeTags = true): array
    {
        /**
         * Semua logika status deadline dan progress otomatis
         * dipusatkan di TaskProgressService.
         */
        return $this->taskProgressService->payload($task, $includeTags);
    }
}
