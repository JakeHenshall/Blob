<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class);

    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/notes', [NoteController::class, 'store'])->name('projects.notes.store');
    Route::delete('projects/{project}/notes/{note}', [NoteController::class, 'destroy'])->name('projects.notes.destroy');

    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
});

require __DIR__.'/auth.php';
