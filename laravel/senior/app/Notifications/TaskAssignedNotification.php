<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 30;

    public function __construct(public readonly Task $task) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/tasks/'.$this->task->id);

        return (new MailMessage)
            ->subject('A task has been assigned to you')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been assigned a new task:')
            ->line($this->task->title)
            ->action('Open task', $url)
            ->line('Project: '.optional($this->task->project)->name);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
        ];
    }
}
