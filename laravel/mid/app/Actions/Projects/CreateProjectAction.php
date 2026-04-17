<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectCreatedNotification;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateProjectAction
{
    public function __construct(private readonly ActivityLogger $logger) {}

    /**
     * @param  array{client_id:int,name:string,description:?string,status:string,starts_at:?string,due_at:?string}  $data
     */
    public function handle(User $owner, array $data): Project
    {
        return DB::transaction(function () use ($owner, $data) {
            $project = Project::create([
                'client_id' => $data['client_id'],
                'user_id' => $owner->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'starts_at' => $data['starts_at'] ?? null,
                'due_at' => $data['due_at'] ?? null,
            ]);

            $this->logger->log(
                action: 'project.created',
                subject: $project,
                description: "Created project \"{$project->name}\"",
                properties: ['client_id' => $project->client_id],
            );

            $recipients = User::query()
                ->whereIn('role', [\App\Enums\Role::Admin->value, \App\Enums\Role::Manager->value])
                ->where('id', '!=', $owner->id)
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new ProjectCreatedNotification($project));
            }

            return $project->fresh(['client', 'owner']);
        });
    }
}
