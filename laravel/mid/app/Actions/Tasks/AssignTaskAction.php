<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class AssignTaskAction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function handle(Task $task, ?User $assignee): Task
    {
        return DB::transaction(function () use ($task, $assignee) {
            $previousId = $task->assigned_to;
            $task->assigned_to = $assignee?->id;
            $task->save();

            if ($assignee && $previousId !== $assignee->id) {
                $assignee->notify(new TaskAssignedNotification($task));

                $this->logger->log(
                    action: 'task.assigned',
                    subject: $task,
                    description: "Assigned task \"{$task->title}\" to {$assignee->name}",
                    properties: [
                        'assignee_id' => $assignee->id,
                        'previous_assignee_id' => $previousId,
                    ],
                );
            } elseif (! $assignee && $previousId) {
                $this->logger->log(
                    action: 'task.unassigned',
                    subject: $task,
                    description: "Unassigned task \"{$task->title}\"",
                    properties: ['previous_assignee_id' => $previousId],
                );
            }

            return $task->fresh(['assignee', 'project']);
        });
    }
}
