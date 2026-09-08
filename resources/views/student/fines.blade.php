<x-student-layout title="My Fines &amp; Payments" :unread-notifications="auth()->user()->unreadNotifications()->count()">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Balance summary -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-bark-400">Outstanding Penalty Balance</p>
                <p class="text-3xl font-extrabold {{ $penaltyBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">₱{{ number_format($penaltyBalance, 2) }}</p>
                @if($penaltyBalance <= 0)
                    <p class="text-sm text-emerald-600 font-medium mt-1">✓ No outstanding penalty</p>
                @endif
            </div>
            <p class="text-xs text-bark-400 mt-3">Fines are ₱10/day for late returns. Settle any balance at the library circulation desk.</p>
        </div>

        <!-- Active loans accruing fines -->
        @if($activeLoans->isNotEmpty())
            <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-5 shadow-sm">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Currently Accruing Fines</h3>
                <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                    @foreach($activeLoans as $loan)
                        <li class="py-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100">{{ $loan['title'] }}</p>
                                <p class="text-xs text-bark-400">Due {{ \Carbon\Carbon::parse($loan['due_at'])->format('M d, Y') }}</p>
                            </div>
                            <span class="text-sm font-bold text-rose-600">₱{{ number_format($loan['fine'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Past fines -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-5 shadow-sm">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Fine History</h3>
            @if($pastFines->isEmpty())
                <p class="text-sm text-bark-400 text-center py-6">No past fines on record.</p>
            @else
                <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                    @foreach($pastFines as $record)
                        @php $status = $record->computedFineStatus(); @endphp
                        <li class="py-2.5 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100">{{ $record->book->title ?? 'Unknown title' }}</p>
                                <p class="text-xs text-bark-400 capitalize">{{ $record->penaltyType() }} &middot; {{ $record->returned_at?->format('M d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-bark-600 dark:text-bark-300 block">₱{{ number_format($record->fine_amount, 2) }}</span>
                                <span @class([
                                    'text-[10px] font-semibold uppercase',
                                    'text-rose-500' => $status === 'unpaid',
                                    'text-amber-500' => $status === 'partially_paid',
                                    'text-emerald-500' => $status === 'paid',
                                    'text-bark-400' => $status === 'waived',
                                ])>{{ str_replace('_', ' ', $status) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</x-student-layout>
