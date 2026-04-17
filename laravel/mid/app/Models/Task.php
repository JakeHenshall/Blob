<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeAssignedTo(Builder $query, ?int $userId): Builder
    {
        if (blank($userId)) {
            return $query;
        }

        return $query->where('assigned_to', $userId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [TaskStatus::Todo->value, TaskStatus::InProgress->value]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->toDateString());
    }

    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->open()
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    public function isOverdue(): bool
    {
        return $this->due_at
            && $this->status->isOpen()
            && $this->due_at->isBefore(now()->startOfDay());
    }
}
