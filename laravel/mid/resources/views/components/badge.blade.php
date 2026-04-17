@props(['colour' => 'bg-slate-100 text-slate-700'])

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {$colour}"]) }}>
    {{ $slot }}
</span>
