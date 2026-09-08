@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp

<x-dynamic-component :component="$layoutComponent" title="Reports">

    <h2 class="text-lg font-semibold text-bark-800 dark:text-parchment-100 mb-5">Generate Reports</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($reports as $key => $label)
            <a href="{{ route('reports.show', $key) }}" class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 hover:border-maroon-400 dark:hover:border-amber-400 transition-colors">
                <div class="font-semibold text-bark-800 dark:text-parchment-100">{{ $label }}</div>
                <div class="text-xs text-bark-500 dark:text-bark-400 mt-1">View and export as CSV</div>
            </a>
        @endforeach
    </div>

</x-dynamic-component>
