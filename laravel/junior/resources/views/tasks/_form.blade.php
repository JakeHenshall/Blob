@props(['task' => null, 'projects', 'statuses', 'preselectedProjectId' => null])

@php($task = $task ?? new \App\Models\Task())

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="project_id" value="Project" />
        <select id="project_id" name="project_id" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <option value="">Select a project</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}"
                    @selected((int) old('project_id', $task->project_id ?? $preselectedProjectId) === $project->id)>
                    {{ $project->name }} &middot; {{ $project->client->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @foreach ($statuses as $s)
                <option value="{{ $s }}" @selected(old('status', $task->status ?? 'todo') === $s)>
                    {{ ucwords(str_replace('_', ' ', $s)) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="title" value="Title" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
            :value="old('title', $task->title)" required />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $task->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_on" value="Due on" />
        <x-text-input id="due_on" name="due_on" type="date" class="mt-1 block w-full"
            :value="old('due_on', optional($task->due_on)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('due_on')" class="mt-2" />
    </div>
</div>
