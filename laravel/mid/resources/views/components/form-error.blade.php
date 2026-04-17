@props(['name'])

@error($name)
    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
@enderror
