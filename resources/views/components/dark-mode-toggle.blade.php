@props(['class' => ''])

<button
    type="button"
    x-data
    @click="
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('librasync-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    "
    title="Toggle dark mode"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center w-9 h-9 rounded-full border border-bark-200 dark:border-bark-700 text-bark-500 dark:text-parchment-100 hover:bg-bark-50 dark:hover:bg-bark-800 transition-colors ' . $class]) }}
>
    <!-- moon (shown in light mode) -->
    <svg class="w-5 h-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
    </svg>
    <!-- sun (shown in dark mode) -->
    <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-6.364-.386 1.591-1.591M3 12h2.25m.386-6.364 1.591 1.591M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
</button>
