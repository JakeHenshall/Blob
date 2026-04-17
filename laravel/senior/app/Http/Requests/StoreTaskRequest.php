<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Task;
use App\Support\TaskPriority;
use App\Support\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $project = $this->route('project');

        if (! $user || ! $project instanceof Project) {
            return false;
        }

        return $user->can('view', $project)
            && $user->can('create', [Task::class, $project]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', Rule::in(TaskStatus::values())],
            'priority' => ['nullable', Rule::in(TaskPriority::values())],
            'assignee_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'due_on' => ['nullable', 'date'],
        ];
    }
}
