@props(['project' => null, 'clients', 'statuses'])

@php($project = $project ?? new \App\Models\Project())
@php($preselected = request('client_id'))

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="client_id" value="Client" />
        <select id="client_id" name="client_id" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <option value="">Select a client</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    @selected((int) old('client_id', $project->client_id ?? $preselected) === $client->id)>
                    {{ $client->name }}@if ($client->company) &middot; {{ $client->company }}@endif
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $project->status ?? 'active') === $status)>
                    {{ ucwords(str_replace('_', ' ', $status)) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="name" value="Project name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $project->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $project->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="starts_on" value="Starts on" />
        <x-text-input id="starts_on" name="starts_on" type="date" class="mt-1 block w-full"
            :value="old('starts_on', optional($project->starts_on)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('starts_on')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ends_on" value="Ends on" />
        <x-text-input id="ends_on" name="ends_on" type="date" class="mt-1 block w-full"
            :value="old('ends_on', optional($project->ends_on)->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('ends_on')" class="mt-2" />
    </div>
</div>
