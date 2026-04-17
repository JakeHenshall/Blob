<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

/**
 * Authorisation rules for `Client` resources.
 *
 * Access model:
 *  - Admins/managers: full read access.
 *  - Client owner: may view, update, and archive.
 *  - Only admins may hard-delete.
 */
class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Client $client): bool
    {
        return $user->isAdmin()
            || $user->isManager()
            || $client->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isAdmin() || $client->owner_id === $user->id;
    }

    public function archive(User $user, Client $client): bool
    {
        return $this->update($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isAdmin();
    }
}
