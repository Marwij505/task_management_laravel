<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickAddTaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat regular user dan session Flowlist yang masih valid.
     */
    private function loginUser(): User
    {
        $user = User::factory()->create([
            'username' => 'quickaddtester',
            'full_name' => 'Quick Add Tester',
            'role' => User::ROLE_USER,
        ]);

        $this->actingAs($user);

        $this->withSession([
            'login_remembered' => false,
            'login_expires_at' => now()
                ->addMinutes(120)
                ->timestamp,
        ]);

        return $user;
    }

    /**
     * Mengambil URL dari named route supaya test tidak bergantung
     * pada hard-coded path.
     */
    private function quickAddUrl(): string
    {
        return route('flowlist.api.tasks.quick-add');
    }

    public function test_user_can_quick_add_task(): void
    {
        $user = $this->loginUser();

        $response = $this->postJson(
            $this->quickAddUrl(),
            [
                'title' => 'Finish Flowlist MK2',
                'deadline' => now()->addDay()->toDateString(),
                'priority' => 'high',
                'category' => 'Development',
                'source' => 'dashboard',
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Task added successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'task_id',
                'task',
            ]);

        $this->assertDatabaseHas(
            'tasks',
            [
                'user_id' => $user->id,
                'title' => 'Finish Flowlist MK2',
                'status' => 'pending',
                'priority' => 'high',
                'category' => 'Development',
                'progress' => 0,
            ]
        );

        $this->assertDatabaseHas(
            'activity_logs',
            [
                'user_id' => $user->id,
                'target_user_id' => $user->id,
                'module' => 'user_tasks',
                'action' => 'quick_add',
            ]
        );
    }

    public function test_quick_add_requires_title(): void
    {
        $this->loginUser();

        $response = $this->postJson(
            $this->quickAddUrl(),
            [
                'title' => '',
                'deadline' => now()->addDay()->toDateString(),
                'priority' => 'medium',
                'category' => 'Testing',
                'source' => 'tasks.index',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors([
                'title',
            ]);
    }

    public function test_quick_add_rejects_invalid_priority(): void
    {
        $this->loginUser();

        $response = $this->postJson(
            $this->quickAddUrl(),
            [
                'title' => 'Invalid Priority Task',
                'deadline' => now()->addDay()->toDateString(),
                'priority' => 'urgent',
                'category' => 'Testing',
                'source' => 'calendar',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors([
                'priority',
            ]);
    }

    public function test_quick_add_requires_valid_deadline(): void
    {
        $this->loginUser();

        $response = $this->postJson(
            $this->quickAddUrl(),
            [
                'title' => 'Invalid Deadline Task',
                'deadline' => 'not-a-date',
                'priority' => 'low',
                'category' => 'Testing',
                'source' => 'calendar',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors([
                'deadline',
            ]);
    }

    /**
     * Source memang opsional. Test ini mencegah field nullable
     * berubah menjadi string kosong dan gagal Rule::in().
     */
    public function test_quick_add_allows_missing_source(): void
    {
        $user = $this->loginUser();

        $response = $this->postJson(
            $this->quickAddUrl(),
            [
                'title' => 'Quick Task Without Source',
                'deadline' => now()->addDay()->toDateString(),
                'priority' => 'medium',
                'category' => '',
            ]
        );

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas(
            'tasks',
            [
                'user_id' => $user->id,
                'title' => 'Quick Task Without Source',
                'category' => null,
            ]
        );
    }

    public function test_quick_add_rejects_invalid_source(): void
    {
        $this->loginUser();

        $response = $this->postJson(
            $this->quickAddUrl(),
            [
                'title' => 'Invalid Source Task',
                'deadline' => now()->addDay()->toDateString(),
                'priority' => 'medium',
                'category' => 'Testing',
                'source' => 'unknown-page',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source',
            ]);
    }
}