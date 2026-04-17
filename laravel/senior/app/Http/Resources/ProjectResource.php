<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Project
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status?->value,
            'description' => $this->description,
            'budget_pence' => $this->budget_pence,
            'starts_on' => $this->starts_on?->toDateString(),
            'due_on' => $this->due_on?->toDateString(),
            'client' => [
                'id' => $this->client?->id,
                'name' => $this->client?->name,
            ],
            'owner' => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
            ],
            'counts' => [
                'tasks' => $this->whenCounted('tasks'),
                'open_tasks' => $this->whenCounted('open_tasks_count'),
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
