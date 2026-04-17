<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\TaskStatus;
use Illuminate\Support\Facades\DB;

/**
 * Mark a task as done, stamping `completed_at` and recording activity.
 *
 * Idempotent: re-completing an already-complete task is a no-op.
 */
class CompleteTaskAction
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * @throws \Illuminate\Database\QueryException on persistence failure
     */
    public function execute(User $actor, Task $task): Task
    {
        if ($task->isComplete()) {
            return $task;
        }

        return DB::transaction(function () use ($actor, $task) {
            $task->status = TaskStatus::Done;
            $task->completed_at = now();
            $task->save();

            $this->activity->record($actor, 'task.completed', $task, [
                'title' => $task->title,
            ]);

            return $task->fresh();
        });
    }
}
