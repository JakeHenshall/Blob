<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

/**
 * Authorisation rules for project notes.
 *
 * A note may be viewed by anyone who can view its parent project. It may be
 * deleted only by its author or an administrator.
 */
class NotePolicy
{
    public function view(User $user, Note $note): bool
    {
        return app(ProjectPolicy::class)->view($user, $note->project);
    }

    public function delete(User $user, Note $note): bool
    {
        return $user->isAdmin() || $note->author_id === $user->id;
    }
}
