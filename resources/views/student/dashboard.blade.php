<x-student-layout title="Student/Teacher Dashboard" :unread-notifications="$unreadNotifications">

    <x-announcement-banner />
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

    <p class="text-bark-500 dark:text-bark-400 mb-6">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}! 📚 Happy reading and keep exploring!</p>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <a href="{{ route('my-borrowings.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Currently Borrowed" :value="$currentlyBorrowed" hint="Books" color="maroon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </x-stat-card>
        </a>
        <a href="{{ route('my-borrowings.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Due Soon" :value="$dueSoon" hint="Book(s)" color="amber">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </x-stat-card>
        </a>
        <a href="{{ route('my-borrowings.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Overdue" :value="$overdue" :hint="$overdue > 0 ? 'Needs attention' : 'Good job!'" color="rose">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </x-stat-card>
        </a>
        <a href="{{ route('fines.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Current Penalty" :value="'₱' . number_format($currentPenalty, 2)" hint="View details" color="bark">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </x-stat-card>
        </a>
        <div>
            <x-stat-card label="My Points" :value="number_format($points) . ' pts'" hint="Keep reading and earn more points for exciting rewards!" color="maroon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172" />
            </x-stat-card>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Current borrowings -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Current Borrowings</h3>
                <a href="{{ route('catalog.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <div class="space-y-3">
                @forelse($borrowedRecords as $record)
                    @php $isOverdue = \Carbon\Carbon::parse($record->due_at)->isPast(); @endphp
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-bark-100 dark:border-bark-800">
                        <div class="w-10 h-14 rounded bg-bark-100 dark:bg-bark-800 flex items-center justify-center shrink-0 text-bark-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $record->book->title ?? 'Unknown' }}</div>
                            <div class="text-xs text-bark-400">by {{ $record->book->author ?? '—' }}</div>
                            <div class="text-xs text-bark-500 dark:text-bark-400 mt-1">
                                Borrowed on {{ \Carbon\Carbon::parse($record->borrowed_at)->format('M j, Y') }} &middot;
                                Due {{ \Carbon\Carbon::parse($record->due_at)->format('M j, Y') }}
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold uppercase px-2 py-1 rounded-full shrink-0 {{ $isOverdue ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300' }}">
                            {{ $isOverdue ? 'Overdue' : 'Ongoing' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-bark-400 text-center py-6">You have no active borrowings right now.</p>
                @endforelse
            </div>
            <a href="{{ route('catalog.index') }}" class="mt-4 block text-center w-full bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
                Browse the Catalog
            </a>
        </div>

        <!-- Account overview -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Account Overview</h3>
            <ul class="space-y-3.5 text-sm">
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Total Points</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ number_format($points) }} pts</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Current Penalty</span>
                    <span class="font-semibold {{ $currentPenalty > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">₱{{ number_format($currentPenalty, 2) }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Account Status</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">Active</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Reading activity -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">My Reading Activity</h3>
                <div class="flex items-center gap-4 text-xs text-bark-500 dark:text-bark-400">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-maroon-700"></span>Borrowed</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Returned</span>
                </div>
            </div>
            <canvas id="activityChart" height="130"></canvas>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="bg-parchment-100 dark:bg-bark-800 rounded-lg p-3 text-center">
                    <div class="text-xl font-bold text-bark-800 dark:text-parchment-100">{{ $booksThisMonth }}</div>
                    <div class="text-xs text-bark-500 dark:text-bark-400">Books Borrowed This Month</div>
                </div>
                <div class="bg-parchment-100 dark:bg-bark-800 rounded-lg p-3 text-center">
                    <div class="text-xl font-bold text-bark-800 dark:text-parchment-100">{{ number_format($points) }}</div>
                    <div class="text-xs text-bark-500 dark:text-bark-400">Total Points Earned</div>
                </div>
            </div>
        </div>

        <!-- Leaderboard preview -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Top Active Users</h3>
                <span class="text-xs text-bark-400">By points</span>
            </div>
            <ul class="space-y-3">
                @forelse($topUsers as $i => $u)
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold
                            {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-bark-200 text-bark-600' : ($i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-bark-100 text-bark-500')) }}">
                            {{ $i + 1 }}
                        </span>
                        <div class="w-8 h-8 rounded-full bg-maroon-700 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                       <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">
                                 @if($u->id === auth()->id())
                                   {{ $u->name }} (You)
                                 @else
                                   {{ collect(explode(' ', $u->name))->map(fn($part) => strtoupper(substr($part, 0, 1)))->implode('. ') }}.
                                 @endif
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-bark-700 dark:text-bark-200">{{ number_format($u->points) }}</span>
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">No activity yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- New arrivals -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">New Arrivals</h3>
                <a href="{{ route('catalog.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                @forelse($newArrivals as $book)
                    <div>
                        <div class="aspect-[3/4] rounded-lg bg-gradient-to-br from-maroon-100 to-bark-100 dark:from-maroon-900/40 dark:to-bark-800 flex items-center justify-center text-maroon-400 mb-2">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        </div>
                        <div class="text-xs font-medium text-bark-800 dark:text-parchment-100 line-clamp-2">{{ $book->title }}</div>
                        <div class="text-[11px] text-bark-400">by {{ $book->author }}</div>
                    </div>
                @empty
                    <p class="col-span-full text-sm text-bark-400 text-center py-6">No books in the catalog yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Available now -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Available Now</h3>
                <a href="{{ route('catalog.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <ul class="space-y-3">
                @forelse($availableBooks as $book)
                    <li class="flex items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $book->title }}</div>
                            <div class="text-xs text-bark-400 truncate">by {{ $book->author }}</div>
                        </div>
                        <form method="POST" action="{{ route('books.request', $book) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-white bg-maroon-700 hover:bg-maroon-800 px-3 py-1.5 rounded-lg transition-colors">
                                Reserve
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">Nothing available right now.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Suggested books -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Suggested Books</h3>
                <a href="{{ route('catalog.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <ul class="space-y-3">
                @forelse($suggestedBooks as $book)
                    <li class="flex items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $book->title }}</div>
                            <div class="text-xs text-bark-400 truncate">by {{ $book->author }} &middot; {{ $book->genre }}</div>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">Borrow a few books and we'll start suggesting titles you might like.</li>
                @endforelse
            </ul>
        </div>

        <!-- Most borrowed books -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Most Borrowed Books</h3>
                <a href="{{ route('catalog.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <ul class="space-y-3">
                @forelse($mostBorrowedBooks as $book)
                    <li class="flex items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $book->title }}</div>
                            <div class="text-xs text-bark-400 truncate">by {{ $book->author }}</div>
                        </div>
                        <span class="text-xs font-semibold text-bark-400 whitespace-nowrap">{{ $book->borrow_records_count }}x borrowed</span>
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">No borrowing history yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <x-quote-of-the-day />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('activityChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($weeks->pluck('label')) !!},
                    datasets: [
                        { label: 'Borrowed', data: {!! json_encode($weeks->pluck('borrowed')) !!}, borderColor: '#7c1e18', backgroundColor: 'rgba(124,30,24,0.08)', tension: 0.35, fill: true, pointRadius: 3 },
                        { label: 'Returned', data: {!! json_encode($weeks->pluck('returned')) !!}, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', tension: 0.35, fill: true, pointRadius: 3 }
                    ]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        });
    </script>
</x-student-layout>
