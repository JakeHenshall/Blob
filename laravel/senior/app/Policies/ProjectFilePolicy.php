<?php

namespace App\Policies;

use App\Models\ProjectFile;
use App\Models\User;

/**
 * Authorisation rules for project file uploads.
 *
 * A file is visible to anyone who can view its project. It may be removed by
 * the uploader, the project owner, or an administrator.
 */
class ProjectFilePolicy
{
    public function view(User $user, ProjectFile $file): bool
    {
        return app(ProjectPolicy::class)->view($user, $file->project);
    }

    public function delete(User $user, ProjectFile $file): bool
    {
        return $user->isAdmin()
            || $file->uploaded_by_id === $user->id
            || $file->project->owner_id === $user->id;
    }
}
