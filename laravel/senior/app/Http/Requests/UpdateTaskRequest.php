<?php

namespace App\Http\Requests;

use App\Support\TaskPriority;
use App\Support\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('task')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(TaskStatus::values())],
            'priority' => ['required', Rule::in(TaskPriority::values())],
            'assignee_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'due_on' => ['nullable', 'date'],
        ];
    }
}
