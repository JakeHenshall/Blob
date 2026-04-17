<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $task->assigned_to === $user->id
            || $task->project->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($task->project->user_id === $user->id) {
            return true;
        }

        return $task->assigned_to === $user->id;
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->project->user_id === $user->id;
    }

    public function complete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->project->user_id === $user->id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }
}
