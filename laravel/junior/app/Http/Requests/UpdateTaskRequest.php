<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task !== null
            && $task->project->client->user_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                Rule::exists(Project::class, 'id')->where(function ($query) {
                    $query->whereIn(
                        'client_id',
                        $this->user()->clients()->select('id')
                    );
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Task::STATUSES)],
            'due_on' => ['nullable', 'date'],
        ];
    }
}
