@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="Pending Borrow Requests">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-rose-100 dark:bg-rose-900/30 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <div class="flex items-center gap-2 mb-4">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Pending Book Disbursements</h3>
            @if(auth()->user()->role !== 'student_assistant')
                <span class="text-[10px] font-bold uppercase tracking-wide bg-bark-100 dark:bg-bark-800 text-bark-500 dark:text-bark-400 px-2 py-0.5 rounded-full">View Only</span>
            @endif
        </div>

        @if($requests->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">There are currently no book requests waiting for approval.</p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">User</th>
                            <th class="px-2 pb-2 font-medium">Book Requested</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Requested On</th>
                            <th class="px-2 pb-2 font-medium text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($requests as $record)
                            <tr>
                                <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">{{ $record->user->name ?? 'Deleted User' }}</td>
                                <td class="px-2 py-2.5">
                                    <div class="font-medium text-bark-700 dark:text-bark-200">{{ $record->book->title ?? 'Deleted Book' }}</div>
                                    <div class="text-xs text-bark-400">Available: {{ $record->book->available_copies ?? '—' }} copies</div>
                                </td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $record->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-2 py-2.5 text-center space-x-2 whitespace-nowrap">
                                    @if(auth()->user()->role === 'student_assistant')
                                        <form action="{{ route('admin.borrows.approve', $record) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">
                                                Approve Request
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.borrows.reject', $record) }}" method="POST" class="inline-block" onsubmit="document.getElementById('reason-{{ $record->id }}').value = prompt('Reason for declining (optional) — e.g. Out of stock, Reservation conflict:') || ''; return true;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="reason" id="reason-{{ $record->id }}">
                                            <button type="submit" class="bg-bark-100 dark:bg-bark-800 hover:bg-rose-100 dark:hover:bg-rose-900/30 text-rose-700 dark:text-rose-300 px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">
                                                Decline Request
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 px-2 py-1 border border-bark-200 dark:border-bark-700 rounded-lg">View Only</span>
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