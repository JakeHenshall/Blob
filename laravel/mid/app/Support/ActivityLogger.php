<?php

namespace App\Support;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $properties = [],
    ): Activity {
        return Activity::create([
            'causer_id' => Auth::id(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }
}
