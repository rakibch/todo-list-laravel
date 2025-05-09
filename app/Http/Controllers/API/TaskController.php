<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        // Only fetch tasks where the user is the creator or assignee
        $query->where(function ($q) use ($request) {
            $q->where('user_id', $request->user()->id)
            ->orWhereHas('assignees', function ($q2) use ($request) {
                $q2->where('user_id', $request->user()->id);
            });
        });

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        // Sorting
        if ($request->filled('sort')) {
            $sortFields = explode(',', $request->sort);

            foreach ($sortFields as $field) {
                $direction = 'asc';
                if (str_starts_with($field, '-')) {
                    $field = ltrim($field, '-');
                    $direction = 'desc';
                }

                if (in_array($field, ['due_date', 'created_at'])) {
                    $query->orderBy($field, $direction);
                }
            }
        } else {
            $query->orderBy('created_at', 'desc'); // default sort
        }

        // Pagination (10 per page)
        $tasks = $query->paginate(10);

        return response()->json($tasks);
    }


    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return response()->json($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorizeTask($task);
        $task->update($request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    protected function authorizeTask(Task $task)
    {
        if (auth()->id !== $task->user_id) {
            abort(403, 'Unauthorized');
        }
    }

    public function assign(Request $request, Task $task)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->authorizeTask($task);

        $task->assignees()->syncWithoutDetaching([$request->user_id]);

        return response()->json(['message' => 'User assigned to task.']);
    }

}
