<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Task;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    public function test_authenticated_user_can_create_task()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Test Task',
            'description' => 'Testing',
            'status' => 'Todo',
            'priority' => 'High',
            'due_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tasks', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Test Task']);
    }

    public function test_user_can_view_their_tasks()
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_user_can_filter_tasks_by_status()
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['status' => 'Done']);
        Task::factory()->for($user)->create(['status' => 'Todo']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks?status=Done');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_user_can_assign_task_to_another_user()
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->for($creator)->create();

        $response = $this->actingAs($creator, 'sanctum')->postJson("/api/tasks/{$task->id}/assign", [
            'user_id' => $assignee->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Task assigned successfully.']);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }
}
