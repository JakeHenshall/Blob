<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Project $project) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->project;
        $url = route('projects.show', $project);

        return (new MailMessage)
            ->subject("New project created: {$project->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("A new project \"{$project->name}\" has been created for client \"{$project->client?->name}\".")
            ->line('Owner: '.($project->owner?->name ?? 'Unknown'))
            ->line('Status: '.$project->status->label())
            ->action('View project', $url)
            ->line('You are receiving this because you manage ClientHub.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'client_id' => $this->project->client_id,
            'client_name' => $this->project->client?->name,
        ];
    }
}
