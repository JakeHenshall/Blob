<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Authorisation rules for `Project` resources.
 *
 * Access model:
 *  - Admins/managers: unrestricted read; managers may create.
 *  - Project owner: full update/read; notes/files upload.
 *  - Task assignees: read-only access to their project.
 *  - Only admins may hard-delete a project.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        if ($project->owner_id === $user->id) {
            return true;
        }

        return $project->tasks()->where('assignee_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->owner_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function addNote(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    public function uploadFile(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }
}
