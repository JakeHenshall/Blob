<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->client = Client::factory()->for($this->user)->create();
    $this->project = Project::factory()->for($this->client)->create();
});

test('user can create a task', function () {
    $this->actingAs($this->user)
        ->post(route('tasks.store'), [
            'project_id' => $this->project->id,
            'title' => 'Wire up login page',
            'status' => 'todo',
            'due_on' => now()->addWeek()->toDateString(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'project_id' => $this->project->id,
        'title' => 'Wire up login page',
        'status' => 'todo',
    ]);
});

test('marking a task done sets completed_at', function () {
    $task = Task::factory()->for($this->project)->create([
        'status' => 'todo',
        'completed_at' => null,
    ]);

    $this->actingAs($this->user)
        ->put(route('tasks.update', $task), [
            'project_id' => $this->project->id,
            'title' => $task->title,
            'status' => 'done',
        ])
        ->assertRedirect();

    $task->refresh();

    expect($task->status)->toBe('done')
        ->and($task->completed_at)->not->toBeNull();
});

test('reopening a task clears completed_at', function () {
    $task = Task::factory()->for($this->project)->done()->create();

    $this->actingAs($this->user)
        ->put(route('tasks.update', $task), [
            'project_id' => $this->project->id,
            'title' => $task->title,
            'status' => 'in_progress',
        ])
        ->assertRedirect();

    expect($task->fresh()->completed_at)->toBeNull();
});

test('user cannot move a task to another user\'s project', function () {
    $task = Task::factory()->for($this->project)->create();
    $otherProject = Project::factory()->create();

    $this->actingAs($this->user)
        ->put(route('tasks.update', $task), [
            'project_id' => $otherProject->id,
            'title' => 'Hijack',
            'status' => 'todo',
        ])
        ->assertSessionHasErrors('project_id');
});

test('task index filter by status', function () {
    Task::factory()->for($this->project)->create(['title' => 'Open Task', 'status' => 'todo']);
    Task::factory()->for($this->project)->create(['title' => 'Closed Task', 'status' => 'done']);

    $this->actingAs($this->user)
        ->get(route('tasks.index', ['status' => 'todo']))
        ->assertOk()
        ->assertSee('Open Task')
        ->assertDontSee('Closed Task');
});
