@props(['label' => 'Back'])

<button onclick="history.back()" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-sm font-medium text-bark-500 dark:text-bark-400 hover:text-bark-800 dark:hover:text-parchment-100 mb-4']) }}>
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
    {{ $label }}
</button>
