<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Facades\Notification;

test('project owner can assign a task and assignee is notified', function () {
    Notification::fake();

    $owner = actingAsManager();
    $assignee = User::factory()->create();
    $project = Project::factory()->for($owner, 'owner')->create();
    $task = Task::factory()->for($project)->todo()->create();

    $response = $this->patch(route('tasks.assign', $task), [
        'assigned_to' => $assignee->id,
    ]);

    $response->assertRedirect();
    expect($task->fresh()->assigned_to)->toBe($assignee->id);

    Notification::assertSentTo($assignee, TaskAssignedNotification::class);

    $this->assertDatabaseHas('activities', [
        'action' => 'task.assigned',
        'subject_type' => (new Task)->getMorphClass(),
        'subject_id' => $task->id,
    ]);
});

test('non-owners cannot assign tasks', function () {
    $intruder = actingAsUser();
    $task = Task::factory()->todo()->create();

    $this->patch(route('tasks.assign', $task), [
        'assigned_to' => $intruder->id,
    ])->assertForbidden();
});

test('completing a task sets status, timestamp and logs activity', function () {
    $user = actingAsManager();
    $project = Project::factory()->for($user, 'owner')->create();
    $task = Task::factory()->for($project)->todo()->create();

    $this->patch(route('tasks.complete', $task))->assertRedirect();

    $task->refresh();
    expect($task->status->value)->toBe('completed')
        ->and($task->completed_at)->not->toBeNull();

    $this->assertDatabaseHas('activities', [
        'action' => 'task.completed',
        'subject_type' => (new Task)->getMorphClass(),
        'subject_id' => $task->id,
    ]);
});

test('assignee can update their own task status', function () {
    $assignee = actingAsUser();
    $task = Task::factory()->todo()->create(['assigned_to' => $assignee->id]);

    $this->patch(route('tasks.update', $task), [
        'title' => $task->title,
        'status' => 'in_progress',
        'priority' => 'medium',
        'assigned_to' => $assignee->id,
    ])->assertRedirect();

    expect($task->fresh()->status->value)->toBe('in_progress');
});

test('tasks index can filter by mine and due soon', function () {
    $user = actingAsUser();

    Task::factory()->create([
        'title' => 'Mine due soon',
        'assigned_to' => $user->id,
        'status' => 'todo',
        'due_at' => now()->addDays(2),
    ]);
    Task::factory()->create([
        'title' => 'Not mine',
        'status' => 'todo',
        'due_at' => now()->addDays(2),
    ]);

    $this->get(route('tasks.index', ['mine' => 1, 'due_soon' => 1]))
        ->assertOk()
        ->assertSee('Mine due soon')
        ->assertDontSee('Not mine');
});
