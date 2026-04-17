<?php

namespace App\Actions\Clients;

use App\Models\Client;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\ProjectStatus;
use Illuminate\Support\Facades\DB;

/**
 * Archive a client and cascade a matching status change to its open projects.
 *
 * Idempotent: calling `execute` on an already-archived client is a no-op.
 */
class ArchiveClientAction
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Archive the client and mark non-completed projects as archived.
     *
     * @throws \Illuminate\Database\QueryException on persistence failure
     */
    public function execute(User $actor, Client $client): Client
    {
        if ($client->isArchived()) {
            return $client;
        }

        return DB::transaction(function () use ($actor, $client) {
            $client->archived_at = now();
            $client->save();

            $client->projects()
                ->where('status', '!=', ProjectStatus::Completed->value)
                ->update(['status' => ProjectStatus::Archived->value]);

            $this->activity->record($actor, 'client.archived', $client, [
                'name' => $client->name,
            ]);

            return $client->fresh();
        });
    }
}
