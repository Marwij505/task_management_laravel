<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlowlistSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_authenticated_user_can_open_all_main_pages(): void
    {
        $user = User::factory()->create([
            'username' => 'tester',
            'full_name' => 'Flowlist Tester',
        ]);

        $this->actingAs($user);

        $this->withSession([
            'login_remembered' => false,
            'login_expires_at' => now()->addMinutes(120)->timestamp,
        ]);

        foreach (['/dashboard', '/tasks', '/tasks/create', '/task-detail', '/calendar', '/statistics', '/profile'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_authenticated_user_can_manage_a_task(): void
    {
        $user = User::factory()->create(['username' => 'tester']);
        $this->actingAs($user);

        $this->withSession([
            'login_remembered' => false,
            'login_expires_at' => now()->addMinutes(120)->timestamp,
        ]);

        $create = $this->postJson('/flowlist-api/tasks/store', [
            'title' => 'Laravel migration test',
            'description' => 'Created by an automated Flowlist smoke test.',
            'status' => 'pending',
            'priority' => 'high',
            'category' => 'Testing',
            'deadline' => now()->addWeek()->toDateString(),
            'assignee' => 'Tester',
            'tags' => 'laravel, migration',
        ])->assertCreated()->assertJson(['success' => true]);

        $taskId = (int) $create->json('task_id');

        $this->getJson('/flowlist-api/tasks')->assertOk()->assertJson(['success' => true]);
        $this->getJson('/flowlist-api/task-detail?id='.$taskId)
            ->assertOk()
            ->assertJsonPath('task.title', 'Laravel migration test');

        $this->postJson('/flowlist-api/tasks/update', [
            'task_id' => $taskId,
            'title' => 'Laravel migration updated',
            'description' => 'Updated successfully.',
            'status' => 'in-progress',
            'priority' => 'medium',
            'category' => 'Testing',
            'deadline' => now()->addDays(10)->toDateString(),
            'assignee' => 'Tester',
            'tags' => 'laravel, updated',
        ])->assertOk()->assertJson(['success' => true]);

        $this->postJson('/flowlist-api/tasks/complete', ['task_id' => $taskId])
            ->assertOk()
            ->assertJsonPath('task.progress', 100);

        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'status' => 'completed',
            'progress' => 100,
        ]);

        $this->postJson('/flowlist-api/tasks/delete', ['task_id' => $taskId])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }
}
