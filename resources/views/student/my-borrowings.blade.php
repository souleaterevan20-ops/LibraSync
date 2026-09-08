<x-student-layout title="My Borrowings" :unread-notifications="auth()->user()->unreadNotifications()->count()">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-5 shadow-sm">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Currently Borrowed</h3>

            @if($active->isEmpty())
                <p class="text-sm text-bark-400 text-center py-8">You have no active borrows or pending requests.</p>
            @else
                <div class="overflow-x-auto -mx-1">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                                <th class="px-2 pb-2 font-medium">Book</th>
                                <th class="px-2 pb-2 font-medium">Status</th>
                                <th class="px-2 pb-2 font-medium">Due Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                            @foreach($active as $record)
                                @php $overdue = $record->status === 'borrowed' && \Carbon\Carbon::parse($record->due_at)->isPast(); @endphp
                                <tr>
                                    <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">{{ $record->book->title ?? 'Unknown title' }}</td>
                                    <td class="px-2 py-2.5">
                                        @if($record->status === 'pending')
                                            <span class="text-[11px] font-bold uppercase px-2 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">Pending Approval</span>
                                        @elseif($overdue)
                                            <span class="text-[11px] font-bold uppercase px-2 py-1 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">Overdue</span>
                                            @if($record->overdue_marked_at)
                                                <span class="block text-[10px] text-rose-500 mt-0.5">₱{{ number_format($record->overdue_charged_amount, 2) }} penalty already charged</span>
                                            @endif
                                        @else
                                            <span class="text-[11px] font-bold uppercase px-2 py-1 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300">Borrowed</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $record->due_at ? \Carbon\Carbon::parse($record->due_at)->format('M d, Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-5 shadow-sm">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Borrowing History</h3>

            @if($history->isEmpty())
                <p class="text-sm text-bark-400 text-center py-8">No past borrows yet.</p>
            @else
                <div class="overflow-x-auto -mx-1">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                                <th class="px-2 pb-2 font-medium">Book</th>
                                <th class="px-2 pb-2 font-medium">Outcome</th>
                                <th class="px-2 pb-2 font-medium">Returned</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                            @foreach($history as $record)
                                <tr>
                                    <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">{{ $record->book->title ?? 'Unknown title' }}</td>
                                    <td class="px-2 py-2.5 capitalize text-bark-600 dark:text-bark-300">{{ $record->status }}</td>
                                    <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $record->returned_at?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $history->links() }}</div>
            @endif
        </div>
    </div>
</x-student-layout>
