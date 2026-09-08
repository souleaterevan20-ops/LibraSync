@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="Book Returns & Active Loans">

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

    <div x-data="{
            modalOpen: false, recordId: null, condition: '',
            bookTitle: '', borrowerName: '', bookPrice: 0, penaltyAmount: 0, notes: '',
            openModal(id, cond, title, borrower, price) {
                this.recordId = id; this.condition = cond; this.bookTitle = title;
                this.borrowerName = borrower; this.bookPrice = price; this.penaltyAmount = price; this.notes = '';
                this.modalOpen = true;
            }
        }">
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Books Currently Out on Loan</h3>
                @if(auth()->user()->role !== 'student_assistant')
                    <span class="text-[10px] font-bold uppercase tracking-wide bg-bark-100 dark:bg-bark-800 text-bark-500 dark:text-bark-400 px-2 py-0.5 rounded-full">View Only</span>
                @endif
            </div>

            <!-- Search borrower / book (spec #30) -->
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search borrower or book..."
                    class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm w-56">
                <button class="bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Search</button>
                @if($search)
                    <a href="{{ route('admin.returns.index') }}" class="text-sm font-medium text-bark-500 hover:text-bark-700 dark:hover:text-bark-200 px-2 py-2">Reset</a>
                @endif
            </form>
        </div>

        @if($loans->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">
                {{ $search ? "No active loans match \"{$search}\"." : 'There are currently no active loans checked out.' }}
            </p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Borrower</th>
                            <th class="px-2 pb-2 font-medium">Book Details</th>
                            <th class="px-2 pb-2 font-medium hidden md:table-cell">Borrowed On</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Due Date &amp; Penalties</th>
                            <th class="px-2 pb-2 font-medium text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($loans as $record)
                            <tr>
                                <td class="px-2 py-2.5">
                                    <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $record->user->name ?? 'Deleted User' }}</div>
                                    <div class="text-xs text-bark-400">{{ $record->user->email ?? '—' }}</div>
                                </td>
                                <td class="px-2 py-2.5">
                                    <div class="font-medium text-bark-700 dark:text-bark-200">{{ $record->book->title ?? 'Deleted Book' }}</div>
                                    <div class="text-xs text-bark-400">ISBN: {{ $record->book->isbn ?? '—' }}</div>
                                </td>
                                <td class="px-2 py-2.5 hidden md:table-cell text-bark-500 dark:text-bark-400">
                                    {{ \Carbon\Carbon::parse($record->borrowed_at)->format('M d, Y g:i A') }}
                                </td>
                                <td class="px-2 py-2.5 hidden sm:table-cell">
                                    @php $currentFine = $record->calculateCurrentFine(); @endphp
                                    @if($currentFine > 0)
                                        <span class="text-rose-600 dark:text-rose-400 font-semibold block">
                                            OVERDUE (₱{{ number_format($currentFine, 2) }})
                                            @if($record->overdue_marked_at)
                                                <span class="block text-[10px] font-normal text-rose-400">Marked overdue {{ $record->overdue_marked_at->diffForHumans() }} — borrower notified</span>
                                            @endif
                                        </span>
                                        <div class="text-xs text-rose-400">Due: {{ \Carbon\Carbon::parse($record->due_at)->format('M d, Y') }}</div>
                                    @else
                                        <span class="text-bark-600 dark:text-bark-300 block font-medium">On Time</span>
                                        <div class="text-xs text-bark-400">Due: {{ \Carbon\Carbon::parse($record->due_at)->format('M d, Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-2 py-2.5 text-center space-y-1.5">
                                    <a href="{{ route('admin.borrows.show', $record) }}" class="block text-[11px] font-medium text-maroon-700 dark:text-amber-300 hover:underline">View Borrow Details</a>
                                    @if(auth()->user()->role === 'student_assistant')
                                        <form action="{{ route('admin.returns.process', $record) }}" method="POST" class="inline-block w-full">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="condition" value="good">
                                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">
                                                Return Book
                                            </button>
                                        </form>
                                        @if($currentFine > 0)
                                            <a href="{{ route('admin.penalties.index') }}" class="block text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline">View Penalty</a>
                                            <form action="{{ route('admin.returns.mark-overdue', $record) }}" method="POST" class="block" onsubmit="return confirm('Sync the overdue penalty for this book now? This charges any newly-accrued fine (₱{{ number_format($currentFine, 2) }} total so far) to {{ $record->user->name ?? 'this user' }}\'s penalty balance and notifies them — the book stays checked out until it\'s returned.');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">
                                                    Overdue
                                                </button>
                                            </form>
                                        @endif
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                @click="openModal({{ $record->id }}, 'lost', '{{ addslashes($record->book->title ?? 'Deleted Book') }}', '{{ addslashes($record->user->name ?? 'Deleted User') }}', {{ $record->book->replacement_cost ?? 0 }})"
                                                class="bg-rose-100 dark:bg-rose-900/30 hover:bg-rose-200 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 px-2.5 py-1 rounded-lg text-[10px] font-semibold uppercase transition-colors">
                                                Lost
                                            </button>
                                            <button type="button"
                                                @click="openModal({{ $record->id }}, 'damaged', '{{ addslashes($record->book->title ?? 'Deleted Book') }}', '{{ addslashes($record->user->name ?? 'Deleted User') }}', {{ $record->book->replacement_cost ?? 0 }})"
                                                class="bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-300 px-2.5 py-1 rounded-lg text-[10px] font-semibold uppercase transition-colors">
                                                Damaged
                                            </button>
                                        </div>
                                        @if($currentFine > 0)
                                            <p class="text-[10px] text-rose-500">Checking in now applies the overdue fine automatically.</p>
                                        @endif
                                    @else
                                        @if($currentFine > 0)
                                            <a href="{{ route('admin.penalties.index') }}" class="block text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline">View Penalty</a>
                                        @endif
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 px-2 py-1 border border-bark-200 dark:border-bark-700 rounded-lg inline-block">View Only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $loans->links() }}</div>
        @endif
    </div>

    @if($settledRecently->isNotEmpty())
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 mt-5">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Recently Returned — Penalty Status</h3>
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                        <th class="px-2 pb-2 font-medium">Borrower</th>
                        <th class="px-2 pb-2 font-medium">Book</th>
                        <th class="px-2 pb-2 font-medium hidden sm:table-cell">Returned</th>
                        <th class="px-2 pb-2 font-medium">Penalty</th>
                        <th class="px-2 pb-2 font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                    @foreach($settledRecently as $record)
                        <tr>
                            <td class="px-2 py-2.5">
                                <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $record->user->name ?? 'Deleted User' }}</div>
                            </td>
                            <td class="px-2 py-2.5 text-bark-600 dark:text-bark-300">{{ $record->book->title ?? 'Unknown' }}</td>
                            <td class="px-2 py-2.5 hidden sm:table-cell text-bark-500 dark:text-bark-400">{{ $record->returned_at?->format('M d, Y') }}</td>
                            <td class="px-2 py-2.5 font-semibold {{ $record->fineRemaining() > 0 ? 'text-rose-600' : 'text-emerald-600' }}">₱{{ number_format($record->fine_amount, 2) }}</td>
                            <td class="px-2 py-2.5 text-center space-y-1.5">
                                @if($record->fineRemaining() > 0)
                                    <a href="{{ route('admin.penalties.index') }}" class="block text-[11px] font-medium text-rose-600 dark:text-rose-400 hover:underline">View Penalty</a>
                                    <a href="{{ route('admin.penalties.index') }}" class="block bg-maroon-700 hover:bg-maroon-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">Settle Payment</a>
                                @else
                                    <a href="{{ route('admin.borrows.show', $record) }}" class="block text-[11px] font-medium text-maroon-700 dark:text-amber-300 hover:underline">View Details</a>
                                    <span class="block text-emerald-600 text-xs font-semibold">&check; Settled</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative bg-white dark:bg-bark-900 rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
            <h3 class="text-lg font-bold text-bark-800 dark:text-parchment-100 mb-1" x-text="condition === 'lost' ? 'Mark Book as Lost' : 'Mark Book as Damaged'"></h3>
            <p class="text-xs text-bark-400 mb-4">This charges a penalty to the borrower and deducts 20 Leaderboard Points.</p>

            <form :action="'/admin/returns/' + recordId + '/process'" method="POST" @submit="modalOpen = false">
                @csrf
                @method('PATCH')
                <input type="hidden" name="condition" :value="condition">

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-bark-500 mb-1">Book Title</label>
                        <p class="text-sm font-medium text-bark-800 dark:text-parchment-100" x-text="bookTitle"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-bark-500 mb-1">Borrower</label>
                        <p class="text-sm font-medium text-bark-800 dark:text-parchment-100" x-text="borrowerName"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-bark-500 mb-1">Original Book Price</label>
                        <p class="text-sm text-bark-600 dark:text-bark-300">₱<span x-text="bookPrice.toFixed(2)"></span></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-bark-500 mb-1">Penalty Amount (editable)</label>
                        <input type="number" name="penalty_amount" step="0.01" min="0" x-model.number="penaltyAmount"
                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-bark-500 mb-1">Notes (optional)</label>
                        <textarea name="notes" x-model="notes" rows="2" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex gap-2 mt-5">
                    <button type="button" @click="modalOpen = false" class="flex-1 text-sm font-medium border border-bark-200 dark:border-bark-700 text-bark-600 dark:text-bark-300 px-3 py-2 rounded-lg hover:bg-bark-50 dark:hover:bg-bark-800">Cancel</button>
                    <button type="submit" class="flex-1 text-sm font-semibold bg-maroon-700 hover:bg-maroon-800 text-white px-3 py-2 rounded-lg">Done</button>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-dynamic-component>