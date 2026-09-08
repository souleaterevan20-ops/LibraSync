@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'admin-layout',
    };
@endphp

<x-dynamic-component :component="$layoutComponent" title="Penalty Management">

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-xl font-bold text-bark-800 dark:text-parchment-100">Penalty Management</h1>
    </div>

    <!-- Summary cards (spec #33 — all values from actual database records) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 p-4">
            <p class="text-xs text-bark-400">Total Outstanding</p>
            <p class="text-xl font-bold text-rose-600 mt-1">₱{{ number_format($totalOutstanding, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 p-4">
            <p class="text-xs text-bark-400">Total Collected</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">₱{{ number_format($totalCollected, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 p-4">
            <p class="text-xs text-bark-400">Unpaid / Partial</p>
            <p class="text-xl font-bold text-amber-600 mt-1">{{ $unpaidCount }} / {{ $partiallyPaidCount }}</p>
        </div>
        <div class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 p-4">
            <p class="text-xs text-bark-400">Paid Penalties</p>
            <p class="text-xl font-bold text-bark-700 dark:text-parchment-100 mt-1">{{ $paidCount }}</p>
        </div>
    </div>

    <!-- Filters (spec #44) -->
    <form method="GET" class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 p-4 mb-5 grid sm:grid-cols-5 gap-3">
        <input type="text" name="user" value="{{ request('user') }}" placeholder="Search borrower..." class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
        <select name="status" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
            <option value="">All Statuses</option>
            @foreach(['unpaid' => 'Unpaid', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid', 'waived' => 'Waived'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-3 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.penalties.index') }}" class="flex-1 text-center bg-bark-100 dark:bg-bark-800 text-bark-600 dark:text-bark-300 text-sm font-semibold px-3 py-2 rounded-lg">Reset</a>
        </div>
    </form>

    <!-- Penalty table (spec #32) -->
    <div class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-parchment-100 dark:bg-bark-800 text-left text-xs text-bark-500 dark:text-bark-400">
                    <tr>
                        <th class="px-4 py-2.5 font-medium">Borrower</th>
                        <th class="px-4 py-2.5 font-medium">Book</th>
                        <th class="px-4 py-2.5 font-medium hidden md:table-cell">Reason</th>
                        <th class="px-4 py-2.5 font-medium hidden lg:table-cell">Due / Returned</th>
                        <th class="px-4 py-2.5 font-medium">Penalty</th>
                        <th class="px-4 py-2.5 font-medium">Paid</th>
                        <th class="px-4 py-2.5 font-medium">Remaining</th>
                        <th class="px-4 py-2.5 font-medium">Status</th>
                        <th class="px-4 py-2.5 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                    @forelse($records as $record)
                        @php $status = $record->computedFineStatus(); @endphp
                        <tr>
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $record->user->name ?? 'Deleted User' }}</div>
                                <div class="text-xs text-bark-400">ID #{{ $record->user_id }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-bark-600 dark:text-bark-300">{{ $record->book->title ?? 'Unknown Book' }}</td>
                            <td class="px-4 py-2.5 text-xs text-bark-400 hidden md:table-cell">{{ $record->penaltyReason() }}</td>
                            <td class="px-4 py-2.5 text-xs text-bark-400 hidden lg:table-cell">
                                Due {{ $record->due_at?->format('M d, Y') }}<br>
                                {{ $record->returned_at ? 'Returned ' . $record->returned_at->format('M d, Y') : 'Not yet returned' }}
                            </td>
                            <td class="px-4 py-2.5 font-medium text-bark-700 dark:text-parchment-100">₱{{ number_format($record->fine_amount, 2) }}</td>
                            <td class="px-4 py-2.5 text-emerald-600">₱{{ number_format($record->fine_paid_amount, 2) }}</td>
                            <td class="px-4 py-2.5 font-semibold {{ $record->fineRemaining() > 0 ? 'text-rose-600' : 'text-bark-400' }}">₱{{ number_format($record->fineRemaining(), 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span @class([
                                    'text-xs font-semibold uppercase px-2 py-0.5 rounded-full',
                                    'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300' => $status === 'unpaid',
                                    'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' => $status === 'partially_paid',
                                    'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' => $status === 'paid',
                                    'bg-bark-100 dark:bg-bark-800 text-bark-500' => $status === 'waived',
                                ])>{{ str_replace('_', ' ', $status) }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center space-x-2 whitespace-nowrap">
                                @if($record->fineRemaining() > 0)
                                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'settle-{{ $record->id }}')"
                                        class="text-xs font-semibold text-maroon-700 dark:text-maroon-300 underline">Settle Payment</button>
                                @else
                                    <span class="text-xs font-semibold text-emerald-600">&check; Settled</span>
                                @endif
                                @if(auth()->user()->role === 'super_admin' && $status !== 'waived')
                                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'adjust-{{ $record->id }}')"
                                        class="text-xs font-semibold text-bark-500 dark:text-bark-400 underline">Adjust / Waive</button>
                                @endif
                            </td>
                        </tr>

                        @if(auth()->user()->role === 'super_admin' && $status !== 'waived')
                            <x-modal name="adjust-{{ $record->id }}" maxWidth="md" :show="(($errors->any() || session('error')) && old('record_id') == $record->id && old('_adjust_form') == '1')">
                                <form method="POST" action="{{ route('admin.penalties.adjust', $record) }}" class="p-6 space-y-3"
                                    onsubmit="return confirm('Apply this penalty adjustment? This will be logged and the borrower will be notified.');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="record_id" value="{{ $record->id }}">
                                    <input type="hidden" name="_adjust_form" value="1">
                                    <h2 class="text-lg font-bold text-bark-800 dark:text-parchment-100">Adjust / Waive Penalty</h2>
                                    <p class="text-xs text-bark-400">Super Admin oversight action — use for special circumstances (e.g. lost book found, administrative goodwill). This changes the total penalty itself, not a payment.</p>
                                    <dl class="text-sm space-y-1 text-bark-500 dark:text-bark-400">
                                        <div class="flex justify-between"><dt>Borrower</dt><dd class="text-bark-800 dark:text-parchment-100 font-medium">{{ $record->user->name ?? 'Deleted User' }}</dd></div>
                                        <div class="flex justify-between"><dt>Book</dt><dd>{{ $record->book->title ?? 'Unknown' }}</dd></div>
                                        <div class="flex justify-between"><dt>Current Amount</dt><dd>₱{{ number_format($record->fine_amount, 2) }}</dd></div>
                                        <div class="flex justify-between"><dt>Already Paid</dt><dd>₱{{ number_format($record->fine_paid_amount, 2) }}</dd></div>
                                    </dl>
                                    <label class="flex items-start gap-2 text-sm text-bark-700 dark:text-bark-200 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900 rounded-lg p-3">
                                        <input type="checkbox" name="waive" value="1" id="waive-{{ $record->id }}"
                                            onchange="
                                                const amt = this.form.new_amount;
                                                if (this.checked) {
                                                    amt.value = '{{ number_format($record->fine_paid_amount, 2, '.', '') }}';
                                                    amt.disabled = true;
                                                    amt.classList.add('opacity-50');
                                                } else {
                                                    amt.disabled = false;
                                                    amt.classList.remove('opacity-50');
                                                }
                                            "
                                            class="mt-0.5 rounded border-bark-300">
                                        <span>
                                            <span class="font-semibold">Waive completely</span> — sets the remaining balance to ₱0 and marks this as Waived, regardless of what's entered below.
                                        </span>
                                    </label>
                                    <div>
                                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">New Amount (₱)</label>
                                        <input type="number" name="new_amount" step="0.01" min="{{ $record->fine_paid_amount }}"
                                            value="{{ (old('record_id') == $record->id && old('_adjust_form') == '1' && old('new_amount') !== null) ? old('new_amount') : number_format($record->fine_amount, 2, '.', '') }}"
                                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                                        <p class="text-[10px] text-bark-400 mt-1">Cannot be set below the ₱{{ number_format($record->fine_paid_amount, 2) }} already paid. Ignored if "Waive completely" above is checked.</p>
                                        @if(old('record_id') == $record->id && old('_adjust_form') == '1')
                                            <x-input-error :messages="$errors->get('new_amount')" class="mt-1" />
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Reason (required)</label>
                                        <input type="text" name="reason" required maxlength="255" placeholder="e.g. Book recovered, Approved administrative adjustment"
                                            value="{{ (old('record_id') == $record->id && old('_adjust_form') == '1') ? old('reason') : '' }}"
                                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                                        @if(old('record_id') == $record->id && old('_adjust_form') == '1')
                                            <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                                        @endif
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" x-on:click="$dispatch('close-modal', 'adjust-{{ $record->id }}')" class="px-4 py-2 rounded-lg text-sm font-semibold text-bark-500 hover:bg-bark-100 dark:hover:bg-bark-800">Cancel</button>
                                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold bg-bark-700 hover:bg-bark-800 text-white">Confirm Adjustment</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif

                        @if($record->fineRemaining() > 0)
                            <x-modal name="settle-{{ $record->id }}" maxWidth="md" :show="(($errors->any() || session('error')) && old('record_id') == $record->id && old('_settle_form') == '1')">
                                <form method="POST" action="{{ route('admin.penalties.settle', $record) }}" class="p-6 space-y-3">
                                    @csrf
                                    <input type="hidden" name="record_id" value="{{ $record->id }}">
                                    <input type="hidden" name="_settle_form" value="1">
                                    <h2 class="text-lg font-bold text-bark-800 dark:text-parchment-100">Settle Payment</h2>
                                    <dl class="text-sm space-y-1 text-bark-500 dark:text-bark-400">
                                        <div class="flex justify-between"><dt>Borrower</dt><dd class="text-bark-800 dark:text-parchment-100 font-medium">{{ $record->user->name ?? 'Deleted User' }}</dd></div>
                                        <div class="flex justify-between"><dt>Book</dt><dd>{{ $record->book->title ?? 'Unknown' }}</dd></div>
                                        <div class="flex justify-between"><dt>Reason</dt><dd>{{ $record->penaltyType() }}</dd></div>
                                        <div class="flex justify-between"><dt>Due Date</dt><dd>{{ $record->due_at?->format('M d, Y') }}</dd></div>
                                        <div class="flex justify-between"><dt>Return Date</dt><dd>{{ $record->returned_at?->format('M d, Y') ?? '—' }}</dd></div>
                                        <div class="flex justify-between"><dt>Days Late</dt><dd>{{ $record->due_at && $record->returned_at && $record->returned_at->gt($record->due_at) ? $record->due_at->diffInDays($record->returned_at) : (int) ($record->overdue_points_deducted / 10) }}</dd></div>
                                        <div class="flex justify-between"><dt>Penalty</dt><dd>₱{{ number_format($record->fine_amount, 2) }}</dd></div>
                                        <div class="flex justify-between"><dt>Already Paid</dt><dd>₱{{ number_format($record->fine_paid_amount, 2) }}</dd></div>
                                        <div class="flex justify-between font-semibold text-rose-600"><dt>Remaining Balance</dt><dd>₱{{ number_format($record->fineRemaining(), 2) }}</dd></div>
                                    </dl>
                                    <div>
                                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Payment Amount (₱)</label>
                                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $record->fineRemaining() }}" required
                                            value="{{ (old('record_id') == $record->id && old('_settle_form') == '1') ? old('amount') : '' }}"
                                            oninput="
                                                const max = {{ $record->fineRemaining() }};
                                                if (parseFloat(this.value) > max) {
                                                    this.setCustomValidity('Cannot exceed the ₱' + max.toFixed(2) + ' remaining balance.');
                                                    this.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                                                } else {
                                                    this.setCustomValidity('');
                                                    this.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                                                }
                                            "
                                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                                        <p class="text-[10px] text-bark-400 mt-1">Cannot exceed the ₱{{ number_format($record->fineRemaining(), 2) }} remaining balance shown above.</p>
                                        @if(old('record_id') == $record->id && old('_settle_form') == '1')
                                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Payment Method</label>
                                        <select name="payment_method" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                                            <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                            <option value="gcash" @selected(old('payment_method') === 'gcash')>GCash</option>
                                            <option value="other" @selected(old('payment_method') === 'other')>Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Notes (optional)</label>
                                        <input type="text" name="notes" maxlength="255"
                                            value="{{ (old('record_id') == $record->id && old('_settle_form') == '1') ? old('notes') : '' }}"
                                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" x-on:click="$dispatch('close-modal', 'settle-{{ $record->id }}')" class="px-4 py-2 rounded-lg text-sm font-semibold text-bark-500 hover:bg-bark-100 dark:hover:bg-bark-800">Cancel</button>
                                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold bg-maroon-700 hover:bg-maroon-800 text-white">Confirm Settlement</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center text-sm text-bark-400">No outstanding penalties.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $records->links() }}</div>

</x-dynamic-component>