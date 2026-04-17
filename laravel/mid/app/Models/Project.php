<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'user_id',
        'name',
        'description',
        'status',
        'starts_at',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'starts_at' => 'date',
            'due_at' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeOwnedBy(Builder $query, ?int $userId): Builder
    {
        if (blank($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('due_at')
            ->whereBetween('due_at', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    public function openTasksCount(): int
    {
        return $this->tasks()
            ->whereIn('status', [\App\Enums\TaskStatus::Todo->value, \App\Enums\TaskStatus::InProgress->value])
            ->count();
    }
}
