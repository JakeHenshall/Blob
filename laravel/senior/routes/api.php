<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/token', [AuthController::class, 'token'])
        ->middleware('throttle:10,1')
        ->name('api.auth.token');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

        Route::get('projects', [ProjectController::class, 'index'])->name('api.projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('api.projects.show');

        Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('api.projects.tasks.index');
        Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('api.projects.tasks.store');
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('api.tasks.complete');
    });
});
