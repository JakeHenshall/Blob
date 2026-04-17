<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $project->user_id === $user->id
            || $project->tasks()->where('assigned_to', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->user_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->user_id === $user->id;
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function addNote(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }
}
