@props(['client' => null])

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $client?->name) }}"
            class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">
        <x-form-error name="name" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Company</label>
        <input type="text" name="company" value="{{ old('company', $client?->company) }}"
            class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="company" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $client?->email) }}"
            class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="email" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $client?->phone) }}"
            class="mt-1 w-full rounded-md border-slate-300 text-sm">
        <x-form-error name="phone" />
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea name="notes" rows="4" class="mt-1 w-full rounded-md border-slate-300 text-sm">{{ old('notes', $client?->notes) }}</textarea>
        <x-form-error name="notes" />
    </div>
</div>
