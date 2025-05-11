<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;
use Illuminate\Http\Request;
use App\Http\Controllers\TestController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('task')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/test-index',[TestController::class,'index']);
    // main routes
    Route::post('/add',[TaskController::class,'store']);
    Route::get('/list',[TaskController::class,'index']);
    Route::get('/show/{task}',[TaskController::class,'show']);
    Route::post('/update/{task}',[TaskController::class,'update']);
    Route::post('/delete/{task}',[TaskController::class,'destroy']);
});
