<?php

namespace Database\Factories;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['Todo', 'In Progress', 'Done']),
            'priority' => $this->faker->randomElement(['Low', 'Medium', 'High']),
            'due_date' => now()->addDays(rand(1, 30)),
        ];
    }
}
