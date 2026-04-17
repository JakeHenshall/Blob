@php($c = $client ?? null)
<div>
    <label class="label" for="name">Name</label>
    <input id="name" name="name" class="input" value="{{ old('name', $c?->name) }}" required>
    @error('name')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="label" for="contact_email">Contact email</label>
    <input id="contact_email" name="contact_email" type="email" class="input" value="{{ old('contact_email', $c?->contact_email) }}">
    @error('contact_email')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="label" for="contact_phone">Contact phone</label>
    <input id="contact_phone" name="contact_phone" class="input" value="{{ old('contact_phone', $c?->contact_phone) }}">
</div>
<div>
    <label class="label" for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="4" class="input">{{ old('notes', $c?->notes) }}</textarea>
</div>
