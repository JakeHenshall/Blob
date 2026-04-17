<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CompleteTaskAction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function handle(Task $task): Task
    {
        if ($task->status === TaskStatus::Completed) {
            return $task;
        }

        return DB::transaction(function () use ($task) {
            $task->status = TaskStatus::Completed;
            $task->completed_at = now();
            $task->save();

            $this->logger->log(
                action: 'task.completed',
                subject: $task,
                description: "Completed task \"{$task->title}\"",
                properties: ['project_id' => $task->project_id],
            );

            return $task->fresh(['assignee', 'project']);
        });
    }
}
