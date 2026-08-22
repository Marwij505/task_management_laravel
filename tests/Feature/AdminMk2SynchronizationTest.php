<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMk2SynchronizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login sebagai admin dengan session Flowlist yang valid.
     */
    private function loginAdmin(): User
    {
        $admin = User::factory()->create([
            'username' => 'adminsync',
            'full_name' => 'Admin Synchronization',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin);

        $this->withSession([
            'login_remembered' => false,
            'login_expires_at' => now()
                ->addMinutes(120)
                ->timestamp,
        ]);

        return $admin;
    }

    /**
     * Membuat regular user untuk target task.
     */
    private function createUser(string $username = 'syncuser'): User
    {
        return User::factory()->create([
            'username' => $username,
            'full_name' => 'Synchronization User',
            'role' => User::ROLE_USER,
        ]);
    }

    /**
     * Membuat task utama.
     */
    private function createTask(User $user): Task
    {
        return Task::create([
            'user_id' => $user->id,
            'title' => 'Admin Synchronization Task',
            'description' => 'Testing Flowlist MK2 admin synchronization.',
            'status' => 'pending',
            'priority' => 'high',
            'category' => 'Development',
            'deadline' => now()->addWeek()->toDateString(),
            'progress' => 0,
            'assignee' => $user->full_name,
        ]);
    }

    public function test_admin_dashboard_recent_task_and_recent_user_are_clickable(): void
    {
        $this->loginAdmin();

        $user = $this->createUser();
        $task = $this->createTask($user);

        $response = $this->get(
            route('admin.dashboard')
        );

        $response
            ->assertOk()
            ->assertSee(
                route('admin.tasks.detail', $task),
                false
            )
            /*
             * Query string pada HTML dapat di-escape menjadi &amp;,
             * jadi cek bagian target yang stabil.
             */
            ->assertSee(
                'search='.urlencode($user->email),
                false
            )
            ->assertSee(
                'focus='.$user->id,
                false
            );
    }

    public function test_admin_all_tasks_shows_checklist_summary(): void
    {
        $this->loginAdmin();

        $user = $this->createUser();
        $task = $this->createTask($user);

        Subtask::create([
            'task_id' => $task->id,
            'title' => 'Completed checklist item',
            'position' => 1,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        Subtask::create([
            'task_id' => $task->id,
            'title' => 'Pending checklist item',
            'position' => 2,
            'is_completed' => false,
        ]);

        $response = $this->get(
            route('admin.tasks.index')
        );

        $response
            ->assertOk()
            ->assertSee('Checklist')
            ->assertSee('1')
            ->assertSee('2')
            ->assertSee('50% completed')
            ->assertSee(
                route('admin.tasks.detail', $task),
                false
            );
    }

    public function test_admin_can_view_task_detail_with_read_only_subtasks(): void
    {
        $this->loginAdmin();

        $user = $this->createUser();
        $task = $this->createTask($user);

        Subtask::create([
            'task_id' => $task->id,
            'title' => 'First Checklist',
            'position' => 1,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        Subtask::create([
            'task_id' => $task->id,
            'title' => 'Second Checklist',
            'position' => 2,
            'is_completed' => false,
        ]);

        $response = $this->get(
            route('admin.tasks.detail', $task)
        );

        $response
            ->assertOk()
            ->assertSee('Admin Synchronization Task')
            ->assertSee('Synchronization User')
            ->assertSee('First Checklist')
            ->assertSee('Second Checklist')
            ->assertSee('50%')
            ->assertSee('Checklist Read Only')
            ->assertSee('Administrator monitoring is read-only');
    }

    public function test_regular_user_cannot_access_admin_task_detail(): void
    {
        $user = $this->createUser();
        $task = $this->createTask($user);

        $this->actingAs($user);

        $this->withSession([
            'login_remembered' => false,
            'login_expires_at' => now()
                ->addMinutes(120)
                ->timestamp,
        ]);

        $this->get(
            route('admin.tasks.detail', $task)
        )
            ->assertForbidden();
    }

    public function test_admin_delete_task_cascades_subtasks(): void
    {
        $this->loginAdmin();

        $user = $this->createUser();
        $task = $this->createTask($user);

        $subtask = Subtask::create([
            'task_id' => $task->id,
            'title' => 'Cascade Delete Checklist',
            'position' => 1,
            'is_completed' => false,
        ]);

        $this->delete(
            route('admin.tasks.destroy', $task)
        )
            ->assertRedirect(
                route('admin.tasks.index')
            );

        $this->assertDatabaseMissing(
            'tasks',
            ['id' => $task->id]
        );

        $this->assertDatabaseMissing(
            'subtasks',
            ['id' => $subtask->id]
        );
    }

    public function test_activity_logs_include_subtask_module_in_user_event_summary(): void
    {
        $admin = $this->loginAdmin();

        ActivityLog::create([
            'user_id' => $admin->id,
            'target_user_id' => $admin->id,
            'module' => 'user_subtasks',
            'action' => 'complete',
            'description' => 'User completed subtask.',
            'properties' => [],
        ]);

        $response = $this->get(
            route('admin.logs.index')
        );

        $response
            ->assertOk()
            ->assertSee('User Subtasks')
            ->assertSee('Complete');
    }
}
