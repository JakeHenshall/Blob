@php($p = $project ?? null)
<div>
    <label class="label" for="name">Name</label>
    <input id="name" name="name" class="input" value="{{ old('name', $p?->name) }}" required>
    @error('name')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="label" for="description">Description</label>
    <textarea id="description" name="description" rows="4" class="input">{{ old('description', $p?->description) }}</textarea>
</div>
@if (isset($statuses))
    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input">
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}" @selected(old('status', $p?->status?->value) === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>
@endif
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="starts_on">Starts on</label>
        <input id="starts_on" name="starts_on" type="date" class="input" value="{{ old('starts_on', $p?->starts_on?->format('Y-m-d')) }}">
    </div>
    <div>
        <label class="label" for="due_on">Due on</label>
        <input id="due_on" name="due_on" type="date" class="input" value="{{ old('due_on', $p?->due_on?->format('Y-m-d')) }}">
        @error('due_on')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
</div>
<div>
    <label class="label" for="budget_pence">Budget (pence)</label>
    <input id="budget_pence" name="budget_pence" type="number" min="0" class="input" value="{{ old('budget_pence', $p?->budget_pence) }}">
</div>
