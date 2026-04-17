<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * Authorisation rules for `Task` resources.
 *
 * Access model:
 *  - Admins/managers: unrestricted.
 *  - Project owners: full control over their project's tasks.
 *  - Assignees: may view, update, and complete their own tasks.
 */
class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $task->project->owner_id === $user->id
            || $task->assignee_id === $user->id;
    }

    /**
     * Whether `$user` may create a task, optionally scoped to `$project`.
     *
     * Laravel invokes this with only the user when the ability is checked
     * against the class (`Gate::check('create', Task::class)`). When a
     * project context is available, callers should pass it explicitly via
     * `Gate::check('create', [Task::class, $project])` for a tighter check.
     */
    public function create(User $user, ?Project $project = null): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        if ($project === null) {
            return false;
        }

        return $project->owner_id === $user->id;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin()
            || $task->project->owner_id === $user->id
            || $task->assignee_id === $user->id;
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->project->owner_id === $user->id;
    }

    public function complete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->project->owner_id === $user->id;
    }
}
