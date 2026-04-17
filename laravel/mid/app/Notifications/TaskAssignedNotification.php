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

    public function __construct(public readonly Task $task) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $task = $this->task;
        $url = route('tasks.show', $task);

        return (new MailMessage)
            ->subject("New task assigned: {$task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("You have been assigned a new task on project \"{$task->project->name}\".")
            ->line("Task: {$task->title}")
            ->line('Priority: '.$task->priority->label())
            ->when($task->due_at, fn (MailMessage $m) => $m->line('Due: '.$task->due_at->toFormattedDateString()))
            ->action('View task', $url)
            ->line('Thanks for being part of ClientHub.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name ?? null,
        ];
    }
}
