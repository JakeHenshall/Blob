@props([
    'task' => null,
    'projects' => collect(),
    'statuses',
    'priorities',
    'assignableUsers',
    'preselectedProject' => null,
    'projectFixed' => false,
])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if (! $projectFixed)
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700">Project</label>
            <select name="project_id" class="mt-1 w-full rounded-md border-slate-300 text-sm">
                <option value="">Choose a project</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(old('project_id', $task?->project_id ?? $preselectedProject?->id) == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            <x-form-error name="project_id" />
        </div>
    @endif

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Title</label>
        <input type="text" name="title" value="{{ old('title', $task?->title) }}" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="title" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-slate-300 text-sm">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $task?->status->value ?? 'todo') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-form-error name="status" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Priority</label>
        <select name="priority" class="mt-1 w-full rounded-md border-slate-300 text-sm">
            @foreach ($priorities as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $task?->priority->value ?? 'medium') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-form-error name="priority" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Assignee</label>
        <select name="assigned_to" class="mt-1 w-full rounded-md border-slate-300 text-sm">
            <option value="">Unassigned</option>
            @foreach ($assignableUsers as $user)
                <option value="{{ $user->id }}" @selected(old('assigned_to', $task?->assigned_to) == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <x-form-error name="assigned_to" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Due</label>
        <input type="date" name="due_at" value="{{ old('due_at', $task?->due_at?->toDateString()) }}" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="due_at" />
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-slate-300 text-sm">{{ old('description', $task?->description) }}</textarea>
        <x-form-error name="description" />
    </div>
</div>
