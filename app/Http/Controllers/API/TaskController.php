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
        $tasks = Task::where('user_id', $request->user()->id)->paginate(10);
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
}
