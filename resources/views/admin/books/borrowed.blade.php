@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Currently Borrowed">

    <x-back-button />

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">All Borrowed Books ({{ $records->count() }})</h3>
        @if($records->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">No books are currently borrowed.</p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Borrower</th>
                            <th class="px-2 pb-2 font-medium">Book Title</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Borrow Date</th>
                            <th class="px-2 pb-2 font-medium">Due Date</th>
                            <th class="px-2 pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($records as $record)
                            @php $overdue = $record->isOverdue(); @endphp
                            <tr>
                                <td class="px-2 py-2.5">
                                    <a href="{{ route('admin.users.show', $record->user) }}" class="font-medium text-bark-800 dark:text-parchment-100 hover:underline">{{ $record->user->name ?? 'Unknown' }}</a>
                                    <div class="text-xs text-bark-400">{{ $record->user?->roleLabel() ?? '' }}</div>
                                </td>
                                <td class="px-2 py-2.5">
                                    <a href="{{ route('admin.books.show', $record->book) }}" class="text-bark-700 dark:text-bark-200 hover:underline">{{ $record->book->title ?? 'Unknown' }}</a>
                                </td>
                                <td class="px-2 py-2.5 hidden sm:table-cell text-bark-500 dark:text-bark-400">{{ optional($record->borrowed_at)->format('M d, Y') }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ optional($record->due_at)->format('M d, Y') }}</td>
                                <td class="px-2 py-2.5">
                                    @if($overdue)
                                        <span class="text-[11px] font-bold uppercase px-2 py-1 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">Overdue</span>
                                    @else
                                        <span class="text-[11px] font-bold uppercase px-2 py-1 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300">On Time</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-dynamic-component>
