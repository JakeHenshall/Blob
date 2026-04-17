<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * Persistence-backed audit trail.
 *
 * Produces an `ActivityLog` row for each domain event the application wants
 * to remember (project created, task completed, file uploaded, etc.).
 * Subjects are stored polymorphically via `subject_type` + `subject_id`.
 */
class ActivityLogger
{
    /**
     * Record an activity entry.
     *
     * @param  array<string, mixed>  $properties  arbitrary JSON-serialisable context
     */
    public function record(
        ?User $actor,
        string $event,
        ?Model $subject = null,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $actor?->id,
            'event' => $event,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
