@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$label">

    <x-back-button />

    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <h2 class="text-lg font-semibold text-bark-800 dark:text-parchment-100">{{ $label }}</h2>

        <div class="flex items-center gap-2">
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <span class="text-bark-400 text-sm">to</span>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <button class="bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Filter</button>
            </form>
            <a href="{{ route('reports.export', ['type' => $type, 'from' => request('from'), 'to' => request('to')]) }}"
               class="bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">Export CSV</a>
        </div>
    </div>

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-parchment-100 dark:bg-bark-800 text-bark-500 dark:text-bark-400 text-xs uppercase tracking-wide">
                <tr>
                    @foreach($columns as $col)
                        <th class="text-left px-4 py-3">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                @forelse($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td class="px-4 py-3 text-bark-600 dark:text-bark-300">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-bark-400">No data for this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-dynamic-component>
