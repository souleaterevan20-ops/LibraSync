@props(['label', 'value', 'hint' => null, 'color' => 'maroon'])

@php
    $colors = [
        'maroon'  => ['bg' => 'bg-maroon-50 dark:bg-maroon-900/40', 'text' => 'text-maroon-700 dark:text-maroon-300', 'value' => 'text-maroon-800 dark:text-maroon-200'],
        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'text-emerald-600 dark:text-emerald-400', 'value' => 'text-emerald-700 dark:text-emerald-300'],
        'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/30', 'text' => 'text-amber-600 dark:text-amber-400', 'value' => 'text-amber-700 dark:text-amber-300'],
        'bark'    => ['bg' => 'bg-bark-100 dark:bg-bark-800/60', 'text' => 'text-bark-600 dark:text-bark-300', 'value' => 'text-bark-800 dark:text-bark-100'],
        'rose'    => ['bg' => 'bg-rose-50 dark:bg-rose-900/30', 'text' => 'text-rose-600 dark:text-rose-400', 'value' => 'text-rose-700 dark:text-rose-300'],
    ][$color] ?? ['bg' => 'bg-bark-100 dark:bg-bark-800/60', 'text' => 'text-bark-600 dark:text-bark-300', 'value' => 'text-bark-800 dark:text-bark-100'];
@endphp

<div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-4 sm:p-5">
    <div class="flex items-start justify-between">
        <div class="min-w-0">
            <div class="text-xs sm:text-sm font-medium text-bark-500 dark:text-bark-400 truncate">{{ $label }}</div>
            <div class="mt-1.5 text-xl sm:text-2xl font-bold {{ $colors['value'] }}">{{ $value }}</div>
            @if($hint)
                <div class="mt-1 text-[11px] text-bark-400 dark:text-bark-500 truncate">{{ $hint }}</div>
            @endif
        </div>
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full {{ $colors['bg'] }} {{ $colors['text'] }} flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">{{ $slot }}</svg>
        </div>
    </div>
</div>
