@props(['label', 'value', 'iconBg' => 'bg-emerald-100 text-emerald-600'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-gray-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-start justify-between">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $iconBg }}">
            {{ $icon }}
        </span>
    </div>
    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $value }}</p>
</div>
