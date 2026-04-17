@props(['status'])

@php
    $label = ucwords(str_replace('_', ' ', $status));
    $classes = match ($status) {
        'active', 'in_progress' => 'bg-blue-100 text-blue-800',
        'completed', 'done' => 'bg-green-100 text-green-800',
        'on_hold' => 'bg-yellow-100 text-yellow-800',
        'archived' => 'bg-gray-200 text-gray-700',
        'todo' => 'bg-slate-100 text-slate-700',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $classes }}">
    {{ $label }}
</span>
