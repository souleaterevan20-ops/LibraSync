@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Borrow Details">

    <button onclick="history.back()" class="inline-flex items-center gap-1.5 text-sm font-medium text-bark-500 dark:text-bark-400 hover:text-bark-800 dark:hover:text-parchment-100 mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        Back
    </button>

    <div class="max-w-2xl mx-auto bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 shadow-sm p-6">
        <h1 class="text-lg font-bold text-bark-800 dark:text-parchment-100 mb-5">Borrow Record #{{ $record->id }}</h1>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Borrower</p>
                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100">{{ $record->user->name ?? 'Unknown' }}</p>
                <p class="text-xs text-bark-400">{{ $record->user->email ?? '' }} &middot; {{ $record->user?->roleLabel() ?? '' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Book</p>
                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100">{{ $record->book->title ?? 'Unknown' }}</p>
                <p class="text-xs text-bark-400">ISBN {{ $record->book->isbn ?? '' }} &middot; {{ $record->book->author ?? '' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Status</p>
                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100 capitalize">{{ $record->status }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Condition</p>
                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100 capitalize">{{ $record->condition ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Borrowed</p>
                <p class="text-sm text-bark-600 dark:text-bark-300">{{ $record->borrowed_at ? \Carbon\Carbon::parse($record->borrowed_at)->format('M d, Y g:i A') : '—' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Due</p>
                <p class="text-sm text-bark-600 dark:text-bark-300">{{ $record->due_at ? \Carbon\Carbon::parse($record->due_at)->format('M d, Y g:i A') : '—' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Returned</p>
                <p class="text-sm text-bark-600 dark:text-bark-300">{{ $record->returned_at ? \Carbon\Carbon::parse($record->returned_at)->format('M d, Y g:i A') : '—' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Checked In By</p>
                <p class="text-sm text-bark-600 dark:text-bark-300">{{ $record->checkedInBy->name ?? '—' }}</p>
            </div>
            @if($record->fine_amount > 0)
                @php $status = $record->computedFineStatus(); @endphp
                <div class="sm:col-span-2 border-t border-bark-100 dark:border-bark-800 pt-4 mt-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-2">Penalty Details</p>
                    <dl class="grid sm:grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between"><dt class="text-bark-400">Type</dt><dd class="font-medium text-bark-700 dark:text-parchment-100">{{ $record->penaltyType() }}</dd></div>
                        <div class="flex justify-between"><dt class="text-bark-400">Rate</dt><dd class="text-bark-600 dark:text-bark-300">{{ $record->condition === 'good' || !$record->condition ? '₱10/day' : 'Replacement cost' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-bark-400">Total Penalty</dt><dd class="font-semibold text-bark-800 dark:text-parchment-100">₱{{ number_format($record->fine_amount, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-bark-400">Amount Paid</dt><dd class="text-emerald-600">₱{{ number_format($record->fine_paid_amount, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-bark-400">Remaining</dt><dd class="font-semibold {{ $record->fineRemaining() > 0 ? 'text-rose-600' : 'text-bark-400' }}">₱{{ number_format($record->fineRemaining(), 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-bark-400">Status</dt><dd class="font-semibold uppercase text-xs">{{ str_replace('_', ' ', $status) }}</dd></div>
                    </dl>
                    <p class="text-xs text-bark-400 mt-2">{{ $record->penaltyReason() }}</p>

                    @if($record->penaltyPayments->isNotEmpty())
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mt-4 mb-2">Payment History</p>
                        <ul class="space-y-1.5">
                            @foreach($record->penaltyPayments as $payment)
                                <li class="flex justify-between text-xs text-bark-500 dark:text-bark-400">
                                    <span>₱{{ number_format($payment->amount, 2) }} via {{ ucfirst($payment->payment_method) }} &mdash; recorded by {{ $payment->recordedBy->name ?? 'System' }}</span>
                                    <span>{{ $payment->created_at->format('M d, Y g:i A') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
            @if($record->overdue_marked_at)
                <div class="sm:col-span-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 mb-1">Overdue Flag</p>
                    <p class="text-sm text-rose-600">Marked overdue on {{ $record->overdue_marked_at->format('M d, Y g:i A') }} — ₱{{ number_format($record->overdue_charged_amount, 2) }} was charged and the borrower was notified at that time.</p>
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>
