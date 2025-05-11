<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_tasks_for_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Create a task owned by this user
        $var = Task::factory()->create(['user_id' => $user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/task/list');
    
        $response->assertStatus(200)
                ->assertJsonStructure(['data']);
    }


    public function test_can_filter_tasks_by_status()
    {
        $this->actingAs($this->user);

        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'done']);
        Task::factory()->create(['user_id' => $this->user->id, 'status' => 'inprogress']);

        $response = $this->getJson('/api/task/list?status=done');

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'done']);
    }

    public function test_can_create_task()
    {
        $this->actingAs($this->user);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Description here',
            'status' => 'inprogress',
            'priority' => 'high',
            'due_date' => now()->addDays(3)->toDateString()
        ];

        $response = $this->postJson('/api/task/add', $taskData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Test Task']);
    }

    public function test_can_show_task()
    {
        $this->actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $task->id]);
    }

    public function test_can_update_task()
    {
        $this->actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Task']);
    }

    public function test_can_delete_task()
    {
        $this->actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Deleted successfully']);
    }

    public function test_can_assign_user_to_task()
    {
        $this->actingAs($this->user);

        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $assignee = User::factory()->create();

        $response = $this->postJson("/api/tasks/{$task->id}/assign", [
            'user_id' => $assignee->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'User assigned to task.']);

        $this->assertTrue($task->assignees()->where('user_id', $assignee->id)->exists());
    }

    public function test_unauthorized_user_cannot_access_others_task()
    {
        $this->actingAs($this->user);
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");
        $response->assertStatus(403);
    }
}
