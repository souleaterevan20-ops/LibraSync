@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
    $isSuperAdmin = auth()->user()->role === 'super_admin';
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$user->name">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if (session('tempPassword'))
        <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-lg text-sm">
            <p class="font-semibold mb-1">Temporary password for {{ session('tempPasswordUser') }}:</p>
            <p class="font-mono text-base bg-white dark:bg-bark-900 inline-block px-3 py-1 rounded-lg border border-amber-300 dark:border-amber-700 select-all">{{ session('tempPassword') }}</p>
            <p class="mt-1 text-xs">Relay this to the user directly (in person or by phone). They will be required to set a new password the moment they log in. This will not be shown again — write it down now if needed.</p>
        </div>
    @endif

    <x-back-button />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Profile card -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-14 h-14 rounded-full bg-maroon-100 dark:bg-maroon-900/40 flex items-center justify-center text-maroon-700 dark:text-amber-300 font-bold text-xl overflow-hidden">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" class="w-full h-full object-cover" alt="{{ $user->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="font-semibold text-bark-800 dark:text-parchment-100">{{ $user->name }}</div>
                    <div class="text-xs text-bark-400">{{ $user->roleLabel() }} &middot; ID #{{ $user->id }}</div>
                </div>
            </div>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-bark-400">Email</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Contact Number</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->contact_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">School / Employee ID</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->school_or_employee_id ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Course</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->course_program ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Department</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->department ?? '—' }}</dd></div>
                <div class="flex justify-between">
                    <dt class="text-bark-400">Account Status</dt>
                    <dd>
                        @php $status = $user->accountStatus(); @endphp
                        @if($status === 'deleted')
                            <span class="text-bark-500 font-semibold">Deleted</span>
                        @elseif($status === 'active')
                            <span class="text-emerald-600 font-semibold">Active</span>
                        @elseif($status === 'disabled')
                            <span class="text-rose-600 font-semibold">Disabled</span>
                        @elseif($status === 'pending')
                            <span class="text-amber-600 font-semibold">Pending</span>
                        @else
                            <span class="text-rose-600 font-semibold">Rejected</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-bark-400">Verification Status</dt>
                    <dd>
                        {{ $user->is_verified ? 'Verified' : 'Pending' }}
                        @if($user->is_verified && $user->approved_by)
                            <span class="text-bark-400 block text-xs">by {{ $user->approvedBy?->name ?? 'Unknown' }} on {{ $user->approved_at?->format('M d, Y') }}</span>
                        @endif
                    </dd>
                </div>
                @if($user->rejected_at)
                    <div class="flex justify-between">
                        <dt class="text-bark-400">Rejection Reason</dt>
                        <dd class="text-bark-700 dark:text-bark-200 text-right max-w-[60%]">{{ $user->rejection_reason }}</dd>
                    </div>
                @endif
                <div class="flex justify-between"><dt class="text-bark-400">Registered</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->created_at->format('M d, Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Last Login</dt><dd class="text-bark-700 dark:text-bark-200">{{ $lastLogin ? $lastLogin->created_at->format('M d, Y H:i') : 'Never' }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Rank</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->rank_label }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Points</dt><dd class="text-bark-700 dark:text-bark-200">{{ $user->points }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Penalty Balance</dt><dd class="text-bark-700 dark:text-bark-200">₱{{ number_format($user->penalty_balance, 2) }}</dd></div>
            </dl>

            <!-- Borrowing summary (spec #8) -->
            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-bark-100 dark:border-bark-800 text-center">
                <div>
                    <div class="text-lg font-bold text-bark-800 dark:text-parchment-100">{{ $totalBorrowed }}</div>
                    <div class="text-[10px] uppercase text-bark-400">Total Borrowed</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-bark-800 dark:text-parchment-100">{{ $totalReturned }}</div>
                    <div class="text-[10px] uppercase text-bark-400">Total Returned</div>
                </div>
                <div>
                    <div class="text-lg font-bold {{ $overdueCount > 0 ? 'text-rose-600' : 'text-bark-800 dark:text-parchment-100' }}">{{ $overdueCount }}</div>
                    <div class="text-[10px] uppercase text-bark-400">Overdue</div>
                </div>
            </div>

            @if($user->bio)
                <p class="text-sm text-bark-500 dark:text-bark-400 mt-4 border-t border-bark-100 dark:border-bark-800 pt-3">{{ $user->bio }}</p>
            @endif

            @if($isSuperAdmin)
                <div class="mt-4 pt-4 border-t border-bark-100 dark:border-bark-800 space-y-2">
                    <a href="{{ route('admin.users.activity', $user) }}" class="block text-center text-sm font-medium bg-bark-700 hover:bg-bark-800 text-white px-3 py-2 rounded-lg">View Activity</a>

                    @if($user->trashed())
                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" onsubmit="return confirm('Restore this account? Their borrowing, penalty, and payment history is all still intact.');">
                            @csrf @method('PATCH')
                            <button class="w-full text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg">Restore Account</button>
                        </form>
                    @else
                        <div class="flex gap-2">
                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ $user->is_active ? 'Disable this account?' : 'Enable this account?' }}');">
                                @csrf @method('PATCH')
                                <button class="w-full text-sm font-medium bg-amber-500 hover:bg-amber-600 text-white px-3 py-2 rounded-lg">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this account? Their history is preserved and this can be undone from Deleted Users.');">
                                @csrf @method('DELETE')
                                <button class="w-full text-sm font-medium bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg">Delete</button>
                            </form>
                        </div>

                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" onsubmit="return confirm('Reset password? A secure temporary password will be generated — {{ $user->name }} will need to set a new one the next time they log in.');">
                            @csrf @method('PATCH')
                            <input type="hidden" name="confirm" value="1">
                            <button class="w-full text-sm font-medium bg-maroon-700 hover:bg-maroon-800 text-white px-3 py-2 rounded-lg">Reset Password</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <!-- Records -->
        <div class="lg:col-span-2 space-y-4">

            @if($user->penalty_balance > 0)
                @php
                    $unsettledRecords = $user->borrowRecords()->with('book')->where('fine_amount', '>', 0)->get()->filter(fn ($r) => $r->fineRemaining() > 0);
                @endphp
                <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                    <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-1">Outstanding Penalties</h3>
                    <p class="text-xs text-bark-400 mb-3">Total outstanding: <span class="font-semibold text-rose-600">₱{{ number_format($user->penalty_balance, 2) }}</span> across {{ $unsettledRecords->count() }} book(s). Each book's penalty is settled independently.</p>
                    @foreach($unsettledRecords as $record)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-bark-100 dark:border-bark-800 last:border-0">
                            <div>
                                <span class="text-bark-700 dark:text-bark-200 font-medium">{{ $record->book->title ?? 'Unknown title' }}</span>
                                <span class="text-xs text-bark-400 block">{{ $record->penaltyType() }} — ₱{{ number_format($record->fineRemaining(), 2) }} remaining</span>
                            </div>
                            <a href="{{ route('admin.penalties.index') }}" class="text-xs font-semibold text-maroon-700 dark:text-maroon-300 underline whitespace-nowrap">Settle in Penalty Management &rarr;</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($user->penaltyPayments->isNotEmpty())
                <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                    <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Payment History</h3>
                    @foreach($user->penaltyPayments as $payment)
                        <div class="flex justify-between text-sm py-1.5 border-b border-bark-100 dark:border-bark-800 last:border-0">
                            <div>
                                <span class="text-bark-700 dark:text-bark-200 font-medium">₱{{ number_format($payment->amount, 2) }} paid</span>
                                <span class="text-bark-400"> — ₱{{ number_format($payment->balance_before, 2) }} → ₱{{ number_format($payment->balance_after, 2) }}</span>
                                @if($payment->balance_after == 0)
                                    <span class="text-emerald-600 font-semibold">(Cleared)</span>
                                @endif
                                @if($payment->notes)
                                    <div class="text-xs text-bark-400 italic">{{ $payment->notes }}</div>
                                @endif
                            </div>
                            <span class="text-bark-400 whitespace-nowrap">{{ $payment->created_at->format('M d, Y') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Current Borrowed Books</h3>
                @forelse($currentlyBorrowed as $record)
                    <div class="flex justify-between text-sm py-1.5 border-b border-bark-100 dark:border-bark-800 last:border-0">
                        <span class="text-bark-700 dark:text-bark-200">{{ $record->book->title ?? 'Deleted Book' }}</span>
                        <span class="text-bark-400">Due {{ optional($record->due_at)->format('M d, Y') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-bark-400">No books currently borrowed.</p>
                @endforelse
            </div>

            <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Borrow History</h3>
                @forelse($borrowHistory->take(10) as $record)
                    <div class="flex justify-between text-sm py-1.5 border-b border-bark-100 dark:border-bark-800 last:border-0">
                        <span class="text-bark-700 dark:text-bark-200">{{ $record->book->title ?? 'Deleted Book' }}</span>
                        <span class="text-bark-400">{{ optional($record->borrowed_at)->format('M d, Y') }} — {{ ucfirst($record->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-bark-400">No borrow history yet.</p>
                @endforelse
            </div>

            <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Return History</h3>
                @forelse($returnHistory->take(10) as $record)
                    <div class="flex justify-between text-sm py-1.5 border-b border-bark-100 dark:border-bark-800 last:border-0">
                        <span class="text-bark-700 dark:text-bark-200">{{ $record->book->title ?? 'Deleted Book' }}</span>
                        <span class="text-bark-400">{{ optional($record->returned_at)->format('M d, Y') }} — {{ ucfirst($record->condition) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-bark-400">No returns recorded yet.</p>
                @endforelse
            </div>

            @if($isSuperAdmin)
                <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                    <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Login History</h3>
                    @forelse($loginHistory as $log)
                        <div class="flex justify-between text-sm py-1.5 border-b border-bark-100 dark:border-bark-800 last:border-0">
                            <span class="text-bark-700 dark:text-bark-200">{{ $log->action }}</span>
                            <span class="text-bark-400">{{ $log->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-bark-400">No login history yet.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>
