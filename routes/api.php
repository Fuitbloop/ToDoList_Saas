<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  // Auth
  Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/oauth/google', [AuthController::class, 'oAuthUrl']);
    Route::get('/oauth/google/callback', [AuthController::class, 'oAuthCallback']);

    Route::middleware('auth:sanctum')->group(function () {
      Route::get('/me', [AuthController::class, 'me']);
      Route::post('/logout', [AuthController::class, 'logout']);
    });
  });

  Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::delete('/user/delete-avatar', [UserController::class, 'deleteAvatar']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);
  });

  // Public plans
  Route::get('plans', [PlanController::class, 'index']);

  // Protected Routes
  Route::middleware('auth:sanctum')->group(function () {
    // Tasks
    Route::apiResource('tasks', TaskController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('tasks/{id}', [TaskController::class, 'update']);

    // Subtasks
    Route::post('/subtasks/change-status', [SubtaskController::class, 'changeStatus']);
    Route::apiResource('subtasks', SubtaskController::class)->only(['index', 'destroy']);
    Route::post('subtasks', [SubtaskController::class, 'store']); // CREATE
    Route::post('subtasks/{id}', [SubtaskController::class, 'update']); // UPDATE

    // Orders
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'destroy']);

    // Payments
    Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
  });

  Route::post('/payments/callback', [PaymentController::class, 'callback']);
});
