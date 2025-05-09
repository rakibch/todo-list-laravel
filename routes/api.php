<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Add your Task CRUD and assignment routes here
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);

});
