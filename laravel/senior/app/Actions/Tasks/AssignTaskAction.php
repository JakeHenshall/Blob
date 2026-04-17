<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;

/**
 * Assign (or unassign) a task to a user.
 *
 * When a new assignee is set and differs from the actor, a queued
 * `TaskAssignedNotification` is dispatched. All side effects — write,
 * activity log, and notification hook — are wrapped in a transaction so
 * nothing partial is persisted on failure.
 */
class AssignTaskAction
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Update the task's assignee. Pass `null` to clear the assignment.
     *
     * @throws \Illuminate\Database\QueryException on persistence failure
     */
    public function execute(User $actor, Task $task, ?User $assignee): Task
    {
        return DB::transaction(function () use ($actor, $task, $assignee) {
            $previousAssigneeId = $task->assignee_id;

            $task->assignee_id = $assignee?->id;
            $task->save();

            $this->activity->record($actor, 'task.assigned', $task, [
                'assignee_id' => $assignee?->id,
                'previous_assignee_id' => $previousAssigneeId,
            ]);

            if ($assignee && $assignee->id !== $actor->id) {
                $assignee->notify(new TaskAssignedNotification($task));
            }

            return $task->fresh(['assignee', 'project']);
        });
    }
}
