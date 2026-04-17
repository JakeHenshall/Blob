@props(['project' => null, 'clients', 'statuses', 'preselectedClientId' => null])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $project?->name) }}" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="name" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Client</label>
        <select name="client_id" class="mt-1 w-full rounded-md border-slate-300 text-sm">
            <option value="">Choose a client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $project?->client_id ?? $preselectedClientId) == $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
        <x-form-error name="client_id" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-slate-300 text-sm">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $project?->status->value ?? 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-form-error name="status" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Starts</label>
        <input type="date" name="starts_at" value="{{ old('starts_at', $project?->starts_at?->toDateString()) }}" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="starts_at" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Due</label>
        <input type="date" name="due_at" value="{{ old('due_at', $project?->due_at?->toDateString()) }}" class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="due_at" />
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-slate-300 text-sm">{{ old('description', $project?->description) }}</textarea>
        <x-form-error name="description" />
    </div>
</div>
