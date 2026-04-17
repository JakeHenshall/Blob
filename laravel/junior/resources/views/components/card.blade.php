@props(['padding' => 'p-6'])

<div {{ $attributes->merge(['class' => 'bg-white shadow-sm sm:rounded-lg border border-gray-100 ' . $padding]) }}>
    {{ $slot }}
</div>
