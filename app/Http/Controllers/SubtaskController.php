<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderSubtaskRequest;
use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\UpdateSubtaskRequest;
use App\Models\Subtask;
use App\Models\Task;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubtaskController extends Controller
{
    /**
     * ============================================================
     * GET SUBTASK LIST
     * ============================================================
     *
     * Endpoint:
     *
     * GET /flowlist-api/subtasks?task_id=123
     */
    public function index(
        Request $request
    ): JsonResponse {
        $taskId = (int) $request->query(
            'task_id',
            0
        );

        if ($taskId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid task ID.',
            ], 400);
        }

        /*
         * Security:
         *
         * hanya ambil task milik user login.
         */
        $task = $this->findOwnedTask(
            $request,
            $taskId
        );

        if (! $task) {
            return $this->taskNotFound();
        }

        /*
         * Ambil subtask sesuai position.
         */
        $subtasks = Subtask::query()
            ->where('task_id', $task->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,

            'subtasks' =>
                $subtasks
                    ->map(
                        fn (Subtask $subtask) =>
                            $this->subtaskPayload(
                                $subtask
                            )
                    )
                    ->values(),

            /*
             * Statistik checklist:
             *
             * total
             * completed
             * pending
             * percentage
             */
            'stats' =>
                $this->statistics(
                    $subtasks
                ),
        ]);
    }


    /**
     * ============================================================
     * CREATE SUBTASK
     * ============================================================
     */
    public function store(
        StoreSubtaskRequest $request
    ): JsonResponse {
        $data =
            $request->validated();

        $task =
            $this->findOwnedTask(
                $request,
                (int) $data['task_id']
            );

        if (! $task) {
            return $this->taskNotFound();
        }

        /*
         * Subtask baru otomatis diletakkan
         * pada posisi terakhir.
         */
        $subtask =
            DB::transaction(
                function () use (
                    $task,
                    $data
                ): Subtask {

                    $lastPosition =
                        (int) Subtask::query()
                            ->where(
                                'task_id',
                                $task->id
                            )
                            ->max('position');

                    return Subtask::create([
                        'task_id' =>
                            $task->id,

                        'title' =>
                            $data['title'],

                        'is_completed' =>
                            false,

                        'position' =>
                            $lastPosition + 1,

                        'completed_at' =>
                            null,
                    ]);
                }
            );


        /*
         * Activity Log.
         */
        ActivityLogService::log(
            module: 'user_subtasks',

            action: 'create',

            description:
                'User created subtask: '
                .$subtask->title,

            properties: [
                'task_id' =>
                    $task->id,

                'task_title' =>
                    $task->title,

                'subtask_id' =>
                    $subtask->id,

                'subtask_title' =>
                    $subtask->title,

                'position' =>
                    $subtask->position,
            ],

            targetUserId:
                $request->user()->id
        );


        return $this->successResponse(
            task: $task,
            message:
                'Subtask added successfully.',
            status: 201
        );
    }


    /**
     * ============================================================
     * UPDATE SUBTASK TITLE
     * ============================================================
     */
    public function update(
        UpdateSubtaskRequest $request
    ): JsonResponse {
        $data =
            $request->validated();

        $task =
            $this->findOwnedTask(
                $request,
                (int) $data['task_id']
            );

        if (! $task) {
            return $this->taskNotFound();
        }

        $subtask =
            $this->findOwnedSubtask(
                $task,
                (int) $data['subtask_id']
            );

        if (! $subtask) {
            return $this->subtaskNotFound();
        }

        $before =
            $subtask->title;

        $subtask->update([
            'title' =>
                $data['title'],
        ]);


        ActivityLogService::log(
            module: 'user_subtasks',

            action: 'update',

            description:
                'User updated subtask: '
                .$subtask->title,

            properties: [
                'task_id' =>
                    $task->id,

                'task_title' =>
                    $task->title,

                'subtask_id' =>
                    $subtask->id,

                'before_title' =>
                    $before,

                'after_title' =>
                    $subtask->title,
            ],

            targetUserId:
                $request->user()->id
        );


        return $this->successResponse(
            task: $task,
            message:
                'Subtask updated successfully.'
        );
    }


    /**
     * ============================================================
     * COMPLETE / REOPEN SUBTASK
     * ============================================================
     *
     * is_completed:
     *
     * true  = tandai selesai
     * false = buka kembali
     */
    public function toggle(
        Request $request
    ): JsonResponse {
        $validator =
            Validator::make(
                $request->all(),
                [
                    'task_id' => [
                        'required',
                        'integer',
                        'min:1',
                    ],

                    'subtask_id' => [
                        'required',
                        'integer',
                        'min:1',
                    ],

                    'is_completed' => [
                        'required',
                        'boolean',
                    ],
                ]
            );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,

                'message' =>
                    $validator
                        ->errors()
                        ->first(),

                'errors' =>
                    $validator
                        ->errors()
                        ->toArray(),
            ], 422);
        }

        $task =
            $this->findOwnedTask(
                $request,
                (int) $request->input(
                    'task_id'
                )
            );

        if (! $task) {
            return $this->taskNotFound();
        }

        $subtask =
            $this->findOwnedSubtask(
                $task,
                (int) $request->input(
                    'subtask_id'
                )
            );

        if (! $subtask) {
            return $this->subtaskNotFound();
        }

        $completed =
            $request->boolean(
                'is_completed'
            );

        /*
         * Jika selesai:
         * completed_at = sekarang.
         *
         * Jika dibuka kembali:
         * completed_at = NULL.
         */
        $subtask->update([
            'is_completed' =>
                $completed,

            'completed_at' =>
                $completed
                    ? now()
                    : null,
        ]);


        ActivityLogService::log(
            module: 'user_subtasks',

            action:
                $completed
                    ? 'complete'
                    : 'reopen',

            description:
                $completed
                    ? 'User completed subtask: '
                        .$subtask->title
                    : 'User reopened subtask: '
                        .$subtask->title,

            properties: [
                'task_id' =>
                    $task->id,

                'task_title' =>
                    $task->title,

                'subtask_id' =>
                    $subtask->id,

                'subtask_title' =>
                    $subtask->title,

                'is_completed' =>
                    $completed,
            ],

            targetUserId:
                $request->user()->id
        );


        return $this->successResponse(
            task: $task,

            message:
                $completed
                    ? 'Subtask completed.'
                    : 'Subtask reopened.'
        );
    }


    /**
     * ============================================================
     * DELETE SUBTASK
     * ============================================================
     */
    public function destroy(
        Request $request
    ): JsonResponse {
        $validator =
            Validator::make(
                $request->all(),
                [
                    'task_id' => [
                        'required',
                        'integer',
                        'min:1',
                    ],

                    'subtask_id' => [
                        'required',
                        'integer',
                        'min:1',
                    ],
                ]
            );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,

                'message' =>
                    $validator
                        ->errors()
                        ->first(),

                'errors' =>
                    $validator
                        ->errors()
                        ->toArray(),
            ], 422);
        }


        $task =
            $this->findOwnedTask(
                $request,
                (int) $request->input(
                    'task_id'
                )
            );

        if (! $task) {
            return $this->taskNotFound();
        }


        $subtask =
            $this->findOwnedSubtask(
                $task,
                (int) $request->input(
                    'subtask_id'
                )
            );

        if (! $subtask) {
            return $this->subtaskNotFound();
        }


        /*
         * Simpan data untuk Activity Log
         * sebelum row dihapus.
         */
        $deletedData = [
            'id' =>
                $subtask->id,

            'title' =>
                $subtask->title,

            'position' =>
                $subtask->position,

            'is_completed' =>
                $subtask->is_completed,
        ];


        DB::transaction(
            function () use (
                $task,
                $subtask
            ): void {

                $subtask->delete();

                /*
                 * Setelah delete,
                 * posisi dinormalisasi kembali.
                 *
                 * Contoh:
                 *
                 * 1,2,4,5
                 *
                 * menjadi:
                 *
                 * 1,2,3,4
                 */
                $this->normalizePositions(
                    $task
                );
            }
        );


        ActivityLogService::log(
            module: 'user_subtasks',

            action: 'delete',

            description:
                'User deleted subtask: '
                .$deletedData['title'],

            properties: [
                'task_id' =>
                    $task->id,

                'task_title' =>
                    $task->title,

                'deleted_subtask' =>
                    $deletedData,
            ],

            targetUserId:
                $request->user()->id
        );


        return $this->successResponse(
            task: $task,
            message:
                'Subtask deleted successfully.'
        );
    }


    /**
     * ============================================================
     * REORDER SUBTASK
     * ============================================================
     *
     * Request:
     *
     * {
     *     task_id: 1,
     *     ordered_ids: [5, 3, 8, 9]
     * }
     */
    public function reorder(
        ReorderSubtaskRequest $request
    ): JsonResponse {
        $data =
            $request->validated();

        $task =
            $this->findOwnedTask(
                $request,
                (int) $data['task_id']
            );

        if (! $task) {
            return $this->taskNotFound();
        }


        /*
         * ID subtask yang benar-benar dimiliki task.
         */
        $currentIds =
            Subtask::query()
                ->where(
                    'task_id',
                    $task->id
                )
                ->pluck('id')
                ->map(
                    fn ($id) => (int) $id
                )
                ->sort()
                ->values();


        /*
         * ID dari browser.
         */
        $submittedIds =
            collect(
                $data['ordered_ids']
            )
                ->map(
                    fn ($id) => (int) $id
                );


        /*
         * Security + data integrity:
         *
         * browser harus mengirim seluruh subtask milik task,
         * tidak boleh kurang atau memasukkan ID task lain.
         */
        if (
            $currentIds->all()
            !==
            $submittedIds
                ->sort()
                ->values()
                ->all()
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Invalid subtask order.',
            ], 422);
        }


        DB::transaction(
            function () use (
                $task,
                $data
            ): void {

                foreach (
                    $data['ordered_ids']
                    as $index => $subtaskId
                ) {
                    Subtask::query()
                        ->where(
                            'task_id',
                            $task->id
                        )
                        ->where(
                            'id',
                            (int) $subtaskId
                        )
                        ->update([
                            'position' =>
                                $index + 1,
                        ]);
                }
            }
        );


        ActivityLogService::log(
            module: 'user_subtasks',

            action: 'reorder',

            description:
                'User reordered subtasks for task: '
                .$task->title,

            properties: [
                'task_id' =>
                    $task->id,

                'task_title' =>
                    $task->title,

                'ordered_ids' =>
                    array_map(
                        'intval',
                        $data['ordered_ids']
                    ),
            ],

            targetUserId:
                $request->user()->id
        );


        return $this->successResponse(
            task: $task,
            message:
                'Subtask order updated.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */


    /**
     * Cari task milik user login.
     *
     * Ini merupakan authorization utama
     * untuk seluruh operasi subtask.
     */
    private function findOwnedTask(
        Request $request,
        int $taskId
    ): ?Task {
        return Task::query()
            ->where(
                'id',
                $taskId
            )
            ->where(
                'user_id',
                $request->user()->id
            )
            ->first();
    }


    /**
     * Cari subtask yang benar-benar berada
     * di dalam parent task.
     */
    private function findOwnedSubtask(
        Task $task,
        int $subtaskId
    ): ?Subtask {
        return Subtask::query()
            ->where(
                'id',
                $subtaskId
            )
            ->where(
                'task_id',
                $task->id
            )
            ->first();
    }


    /**
     * Ubah Subtask menjadi payload JSON.
     */
    private function subtaskPayload(
        Subtask $subtask
    ): array {
        return [
            'id' =>
                $subtask->id,

            'task_id' =>
                $subtask->task_id,

            'title' =>
                $subtask->title,

            'is_completed' =>
                (bool)
                $subtask->is_completed,

            'position' =>
                (int)
                $subtask->position,

            'completed_at' =>
                $subtask->completed_at
                    ?->toISOString(),

            'created_at' =>
                $subtask->created_at
                    ?->toISOString(),

            'updated_at' =>
                $subtask->updated_at
                    ?->toISOString(),
        ];
    }


    /**
     * ============================================================
     * STATISTIK SUBTASK
     * ============================================================
     *
     * Contoh:
     *
     * total     = 5
     * completed = 3
     * pending   = 2
     * percentage = 60
     */
    private function statistics(
        Collection $subtasks
    ): array {
        $total =
            $subtasks->count();

        $completed =
            $subtasks
                ->where(
                    'is_completed',
                    true
                )
                ->count();

        $pending =
            max(
                0,
                $total - $completed
            );

        $percentage =
            $total > 0
                ? (int) round(
                    ($completed / $total)
                    * 100
                )
                : 0;


        return [
            'total' =>
                $total,

            'completed' =>
                $completed,

            'pending' =>
                $pending,

            'percentage' =>
                $percentage,
        ];
    }


    /**
     * Normalisasi posisi setelah delete.
     */
    private function normalizePositions(
        Task $task
    ): void {
        $ids =
            Subtask::query()
                ->where(
                    'task_id',
                    $task->id
                )
                ->orderBy(
                    'position'
                )
                ->orderBy(
                    'id'
                )
                ->pluck(
                    'id'
                );

        foreach (
            $ids
            as $index => $id
        ) {
            Subtask::query()
                ->where(
                    'id',
                    $id
                )
                ->update([
                    'position' =>
                        $index + 1,
                ]);
        }
    }


    /**
     * Response standar setelah operasi berhasil.
     */
    private function successResponse(
        Task $task,
        string $message,
        int $status = 200
    ): JsonResponse {
        $subtasks =
            Subtask::query()
                ->where(
                    'task_id',
                    $task->id
                )
                ->orderBy(
                    'position'
                )
                ->orderBy(
                    'id'
                )
                ->get();


        return response()->json([
            'success' => true,

            'message' =>
                $message,

            'subtasks' =>
                $subtasks
                    ->map(
                        fn (Subtask $subtask) =>
                            $this->subtaskPayload(
                                $subtask
                            )
                    )
                    ->values(),

            'stats' =>
                $this->statistics(
                    $subtasks
                ),
        ], $status);
    }


    private function taskNotFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' =>
                'Task not found.',
        ], 404);
    }


    private function subtaskNotFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' =>
                'Subtask not found.',
        ], 404);
    }
}