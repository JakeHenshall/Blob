<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    public const STATUSES = ['todo', 'in_progress', 'done'];

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'due_on',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_on' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }
}
