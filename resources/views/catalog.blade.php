@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="Library Book Catalog">

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <form action="{{ route('catalog.index') }}" method="GET" class="flex gap-2 mb-5">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by title, author, or genre..." class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white font-semibold px-5 rounded-lg text-sm transition-colors shrink-0">
                    Search
                </button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($books as $book)
                    <div class="border border-bark-200 dark:border-bark-700 rounded-xl p-4">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <h4 class="font-semibold text-bark-800 dark:text-parchment-100 truncate">{{ $book->title }}</h4>
                                <p class="text-xs text-bark-400">by {{ $book->author }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold shrink-0 {{ $book->type === 'reference' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                {{ ucfirst($book->type) }}
                            </span>
                        </div>
                        <div class="mt-3 text-xs text-bark-500 dark:text-bark-400 space-y-0.5">
                            <div><span class="font-medium text-bark-600 dark:text-bark-300">Genre:</span> {{ $book->genre }}</div>
                            <div><span class="font-medium text-bark-600 dark:text-bark-300">Availability:</span> {{ $book->available_copies }} / {{ $book->total_copies }} copies</div>
                        </div>
                        <div class="mt-3">
                            @if($book->type === 'reference')
                                <button disabled class="w-full bg-bark-200 dark:bg-bark-700 text-bark-500 dark:text-bark-400 py-2 rounded-lg text-xs font-semibold cursor-not-allowed">
                                    In-Library Reference Only
                                </button>
                            @elseif($book->available_copies < 1)
                                <button disabled class="w-full bg-rose-300 dark:bg-rose-900/50 text-white dark:text-rose-300 py-2 rounded-lg text-xs font-semibold cursor-not-allowed">
                                    Out of Stock
                                </button>
                            @else
                                <form action="{{ route('books.request', $book) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-maroon-700 hover:bg-maroon-800 text-white py-2 rounded-lg text-xs font-semibold uppercase transition-colors">
                                        Request to Borrow
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-sm text-bark-400 text-center py-8">No books found matching your criteria.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">My Borrow History</h3>

            @forelse($myBorrows as $record)
                <div class="flex items-center justify-between py-2.5 border-b border-bark-100 dark:border-bark-800 last:border-0">
                    <span class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $record->book->title ?? 'Deleted Book' }}</span>
                    <span class="text-[11px] font-semibold uppercase text-right shrink-0 ml-2">
                        @if($record->status === 'pending')
                            <span class="text-amber-600 dark:text-amber-400">Pending Approval</span>
                        @elseif($record->status === 'borrowed')
                            @php $currentFine = $record->calculateCurrentFine(); @endphp
                            @if($currentFine > 0)
                                <span class="text-rose-600 dark:text-rose-400 block">Overdue (₱{{ number_format($currentFine, 2) }})</span>
                            @else
                                <span class="text-sky-600 dark:text-sky-400 block">Out (Due {{ \Carbon\Carbon::parse($record->due_at)->format('M d') }})</span>
                            @endif
                        @elseif($record->status === 'returned')
                            @if($record->fine_amount > 0 && $record->fine_status === 'unpaid')
                                <span class="text-rose-600 dark:text-rose-400 block">Fine Unpaid: ₱{{ number_format($record->fine_amount, 2) }}</span>
                            @elseif($record->fine_amount > 0 && $record->fine_status === 'paid')
                                <span class="text-emerald-600 dark:text-emerald-400 block">Fine Paid (₱{{ number_format($record->fine_amount, 2) }})</span>
                            @else
                                <span class="text-emerald-600 dark:text-emerald-400 block">Returned On Time</span>
                            @endif
                        @endif
                    </span>
                </div>
            @empty
                <p class="text-sm text-bark-400 text-center py-6">You haven't requested any books yet.</p>
            @endforelse
        </div>
    </div>
</x-dynamic-component>
