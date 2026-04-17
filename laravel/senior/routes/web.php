<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::post('clients/{client}/archive', [ClientController::class, 'archive'])->name('clients.archive');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('clients/{client}/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('clients/{client}/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('projects/{project}/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::delete('projects/{project}/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::post('projects/{project}/files', [ProjectFileController::class, 'store'])->name('files.store');
    Route::get('projects/{project}/files/{file}/download', [ProjectFileController::class, 'download'])->name('files.download');
    Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'destroy'])->name('files.destroy');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});
