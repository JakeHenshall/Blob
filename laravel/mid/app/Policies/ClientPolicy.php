<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Client $client): bool
    {
        return $user->isStaff() || $client->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isAdmin() || $client->user_id === $user->id;
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isAdmin() || $client->user_id === $user->id;
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->isAdmin();
    }
}
