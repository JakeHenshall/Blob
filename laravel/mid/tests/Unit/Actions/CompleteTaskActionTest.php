<?php

use App\Actions\Tasks\CompleteTaskAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Support\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('marks a task completed and records completed_at', function () {
    $task = Task::factory()->todo()->create();
    $action = new CompleteTaskAction(new ActivityLogger);

    $result = $action->handle($task);

    expect($result->status)->toBe(TaskStatus::Completed)
        ->and($result->completed_at)->not->toBeNull();
});

it('is idempotent for already completed tasks', function () {
    $task = Task::factory()->completed()->create();
    $completedAt = $task->completed_at;

    $action = new CompleteTaskAction(new ActivityLogger);
    $result = $action->handle($task);

    expect($result->status)->toBe(TaskStatus::Completed)
        ->and($result->completed_at->equalTo($completedAt))->toBeTrue();
});
