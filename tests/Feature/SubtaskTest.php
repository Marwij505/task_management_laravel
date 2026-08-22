<?php

namespace Tests\Feature;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtaskTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Buat user login yang mempunyai session Flowlist valid.
     */
    private function loginUser(): User
    {
        $user =
            User::factory()->create([
                'username' =>
                    'subtasktester',

                'full_name' =>
                    'Subtask Tester',

                'role' =>
                    User::ROLE_USER,
            ]);


        $this->actingAs(
            $user
        );


        $this->withSession([
            'login_remembered' =>
                false,

            'login_expires_at' =>
                now()
                    ->addMinutes(120)
                    ->timestamp,
        ]);


        return $user;
    }


    /**
     * Buat parent task.
     */
    private function createTask(
        User $user
    ): Task {
        return Task::create([
            'user_id' =>
                $user->id,

            'title' =>
                'Flowlist MK2 Development',

            'description' =>
                'Testing subtasks.',

            'status' =>
                'pending',

            'priority' =>
                'high',

            'category' =>
                'Development',

            'deadline' =>
                now()
                    ->addWeek()
                    ->toDateString(),

            'progress' =>
                0,

            'assignee' =>
                $user->full_name,
        ]);
    }


    public function test_user_can_add_subtask(): void
    {
        $user =
            $this->loginUser();

        $task =
            $this->createTask(
                $user
            );


        $response =
            $this->postJson(
                route(
                    'flowlist.api.subtasks.store'
                ),
                [
                    'task_id' =>
                        $task->id,

                    'title' =>
                        'Create subtask migration',
                ]
            );


        $response
            ->assertCreated()
            ->assertJson([
                'success' =>
                    true,
            ]);


        $this->assertDatabaseHas(
            'subtasks',
            [
                'task_id' =>
                    $task->id,

                'title' =>
                    'Create subtask migration',

                'is_completed' =>
                    0,

                'position' =>
                    1,
            ]
        );


        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' =>
                    $user->id,

                'module' =>
                    'user_subtasks',

                'action' =>
                    'create',
            ]
        );
    }


    public function test_subtask_progress_is_calculated_automatically(): void
    {
        $user =
            $this->loginUser();

        $task =
            $this->createTask(
                $user
            );


        Subtask::create([
            'task_id' =>
                $task->id,

            'title' =>
                'Step One',

            'is_completed' =>
                true,

            'position' =>
                1,

            'completed_at' =>
                now(),
        ]);


        Subtask::create([
            'task_id' =>
                $task->id,

            'title' =>
                'Step Two',

            'is_completed' =>
                false,

            'position' =>
                2,
        ]);


        $response =
            $this->getJson(
                route(
                    'flowlist.api.subtasks.index'
                )
                .'?task_id='
                .$task->id
            );


        $response
            ->assertOk()
            ->assertJsonPath(
                'stats.total',
                2
            )
            ->assertJsonPath(
                'stats.completed',
                1
            )
            ->assertJsonPath(
                'stats.pending',
                1
            )
            ->assertJsonPath(
                'stats.percentage',
                50
            );
    }


    public function test_user_can_complete_and_reopen_subtask(): void
    {
        $user =
            $this->loginUser();

        $task =
            $this->createTask(
                $user
            );


        $subtask =
            Subtask::create([
                'task_id' =>
                    $task->id,

                'title' =>
                    'Testing',

                'position' =>
                    1,
            ]);


        /*
         * Complete.
         */
        $this->postJson(
            route(
                'flowlist.api.subtasks.toggle'
            ),
            [
                'task_id' =>
                    $task->id,

                'subtask_id' =>
                    $subtask->id,

                'is_completed' =>
                    true,
            ]
        )
            ->assertOk();


        $this->assertDatabaseHas(
            'subtasks',
            [
                'id' =>
                    $subtask->id,

                'is_completed' =>
                    1,
            ]
        );


        /*
         * Reopen.
         */
        $this->postJson(
            route(
                'flowlist.api.subtasks.toggle'
            ),
            [
                'task_id' =>
                    $task->id,

                'subtask_id' =>
                    $subtask->id,

                'is_completed' =>
                    false,
            ]
        )
            ->assertOk();


        $this->assertDatabaseHas(
            'subtasks',
            [
                'id' =>
                    $subtask->id,

                'is_completed' =>
                    0,
            ]
        );
    }


    public function test_user_can_edit_subtask(): void
    {
        $user =
            $this->loginUser();

        $task =
            $this->createTask(
                $user
            );


        $subtask =
            Subtask::create([
                'task_id' =>
                    $task->id,

                'title' =>
                    'Old Title',

                'position' =>
                    1,
            ]);


        $this->postJson(
            route(
                'flowlist.api.subtasks.update'
            ),
            [
                'task_id' =>
                    $task->id,

                'subtask_id' =>
                    $subtask->id,

                'title' =>
                    'Updated Title',
            ]
        )
            ->assertOk();


        $this->assertDatabaseHas(
            'subtasks',
            [
                'id' =>
                    $subtask->id,

                'title' =>
                    'Updated Title',
            ]
        );
    }


    public function test_user_can_delete_subtask(): void
    {
        $user =
            $this->loginUser();

        $task =
            $this->createTask(
                $user
            );


        $subtask =
            Subtask::create([
                'task_id' =>
                    $task->id,

                'title' =>
                    'Delete Me',

                'position' =>
                    1,
            ]);


        $this->postJson(
            route(
                'flowlist.api.subtasks.destroy'
            ),
            [
                'task_id' =>
                    $task->id,

                'subtask_id' =>
                    $subtask->id,
            ]
        )
            ->assertOk();


        $this->assertDatabaseMissing(
            'subtasks',
            [
                'id' =>
                    $subtask->id,
            ]
        );
    }


    public function test_user_can_reorder_subtasks(): void
    {
        $user =
            $this->loginUser();

        $task =
            $this->createTask(
                $user
            );


        $first =
            Subtask::create([
                'task_id' =>
                    $task->id,

                'title' =>
                    'First',

                'position' =>
                    1,
            ]);


        $second =
            Subtask::create([
                'task_id' =>
                    $task->id,

                'title' =>
                    'Second',

                'position' =>
                    2,
            ]);


        $this->postJson(
            route(
                'flowlist.api.subtasks.reorder'
            ),
            [
                'task_id' =>
                    $task->id,

                'ordered_ids' => [
                    $second->id,
                    $first->id,
                ],
            ]
        )
            ->assertOk();


        $this->assertDatabaseHas(
            'subtasks',
            [
                'id' =>
                    $second->id,

                'position' =>
                    1,
            ]
        );


        $this->assertDatabaseHas(
            'subtasks',
            [
                'id' =>
                    $first->id,

                'position' =>
                    2,
            ]
        );
    }


    /**
     * Security test.
     *
     * User A tidak boleh melihat atau memodifikasi
     * subtask pada task User B.
     */
    public function test_user_cannot_access_another_users_subtasks(): void
    {
        $userA =
            $this->loginUser();


        $userB =
            User::factory()->create([
                'username' =>
                    'otheruser',

                'full_name' =>
                    'Other User',

                'role' =>
                    User::ROLE_USER,
            ]);


        $otherTask =
            $this->createTask(
                $userB
            );


        $response =
            $this->getJson(
                route(
                    'flowlist.api.subtasks.index'
                )
                .'?task_id='
                .$otherTask->id
            );


        $response
            ->assertNotFound();


        /*
         * Pastikan masih login sebagai User A.
         */
        $this->assertAuthenticatedAs(
            $userA
        );
    }
}