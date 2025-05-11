<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

/**
 * @group Task Management
 * 
 * APIs for managing tasks: listing, creating, viewing, updating, deleting, and assigning users.
 */

class TaskController extends Controller
{/**
     * Get a paginated list of tasks for the authenticated user.
     * 
     * The user will see tasks where they are the creator or an assignee.
     * You can filter tasks by `status`, `priority`, and `due_date`, and sort by `due_date` or `created_at`.
     * 
     * @authenticated
     * 
     * @queryParam status string Filter by task status. Example: pending
     * @queryParam priority string Filter by task priority. Example: high
     * @queryParam due_date date Filter by due date (YYYY-MM-DD). Example: 2024-12-01
     * @queryParam sort string Sort fields, comma-separated. Prefix with `-` for descending. Allowed: due_date, created_at. Example: -due_date,created_at
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Task Title",
     *       "status": "pending",
     *       ...
     *     }
     *   ],
     *   "links": {...},
     *   "meta": {...}
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

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

        $tasks = $query->paginate(10);
        return response()->json($tasks);
    }

    /**
     * Create a new task.
     * 
     * The authenticated user will be set as the owner of the task.
     * 
     * @authenticated
     * 
     * @bodyParam title string required The title of the task. Example: Complete report
     * @bodyParam description string Optional description. Example: Write Q4 financial analysis
     * @bodyParam status string Task status. Example: pending
     * @bodyParam priority string Task priority. Example: high
     * @bodyParam due_date date Due date (YYYY-MM-DD). Example: 2024-12-15
     * 
     * @response 201 {
     *   "id": 5,
     *   "title": "Complete report",
     *   ...
     * }
     * 
     * @param StoreTaskRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $task = Task::create($data);
        return response()->json($task, 201);
    }
     /**
     * Show a specific task.
     * 
     * Only the task creator can view the task details.
     * 
     * @authenticated
     * 
     * @urlParam task int required The ID of the task. Example: 2
     * 
     * @response 200 {
     *   "id": 2,
     *   "title": "Prepare slides",
     *   ...
     * }
     * 
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return response()->json($task);
    }
    /**
     * Update a task.
     * 
     * Only the task creator is authorized to update it.
     * 
     * @authenticated
     * 
     * @urlParam task int required The ID of the task. Example: 3
     * @bodyParam title string Optional title update.
     * @bodyParam status string Optional status update. Example: completed
     * @bodyParam priority string Optional priority update. Example: medium
     * @bodyParam due_date date Optional due date update. Example: 2024-11-30
     * 
     * @response 200 {
     *   "id": 3,
     *   "title": "Updated title",
     *   ...
     * }
     * 
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorizeTask($task);
        $task->update($request->validated());
        return response()->json($task);
    }
    /**
     * Update a task.
     * 
     * Only the task creator is authorized to update it.
     * 
     * @authenticated
     * 
     * @urlParam task int required The ID of the task. Example: 3
     * @bodyParam title string Optional title update.
     * @bodyParam status string Optional status update. Example: completed
     * @bodyParam priority string Optional priority update. Example: medium
     * @bodyParam due_date date Optional due date update. Example: 2024-11-30
     * 
     * @response 200 {
     *   "id": 3,
     *   "title": "Updated title",
     *   ...
     * }
     * 
     * @param UpdateTaskRequest $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
    /**
     * Check if the current user is authorized to manage the task.
     * 
     * @hidden
     * 
     * @param Task $task
     * @return void
     */
    protected function authorizeTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403);
        }
    }
    /**
     * Assign a user to a task.
     * 
     * Only the task creator is allowed to assign users.
     * 
     * @authenticated
     * 
     * @urlParam task int required The ID of the task. Example: 6
     * @bodyParam user_id int required The ID of the user to assign. Example: 2
     * 
     * @response 200 {
     *   "message": "User assigned to task."
     * }
     * 
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, Task $task)
    {
        $this->authorizeTask($task); // Reuse your existing ownership check
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $task->assignees()->syncWithoutDetaching([$request->user_id]);

        return response()->json(['message' => 'User assigned to task.']);
        }
}
 