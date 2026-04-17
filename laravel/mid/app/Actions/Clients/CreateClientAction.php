<?php

namespace App\Actions\Clients;

use App\Models\Client;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CreateClientAction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    /**
     * @param  array{name:string,company:?string,email:?string,phone:?string,notes:?string}  $data
     */
    public function handle(User $owner, array $data): Client
    {
        return DB::transaction(function () use ($owner, $data) {
            $client = Client::create([
                'user_id' => $owner->id,
                'name' => $data['name'],
                'company' => $data['company'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->logger->log(
                action: 'client.created',
                subject: $client,
                description: "Created client \"{$client->name}\"",
            );

            return $client;
        });
    }
}
