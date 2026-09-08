<x-assistant-layout title="Library Staff Dashboard" :unread-notifications="$unreadNotifications">

    <x-announcement-banner />
    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-gradient-to-r from-maroon-700 to-maroon-800 text-white rounded-xl p-5 flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <div class="font-semibold">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}! 👋</div>
            <div class="text-sm text-maroon-100">Here's what's happening in the library today.</div>
        </div>
        <div class="text-sm bg-white/10 px-3 py-1.5 rounded-lg font-mono"
             x-data="{ now: new Date() }"
             x-init="setInterval(() => now = new Date(), 1000)"
             x-text="now.toLocaleString('en-PH', { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })">
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <a href="{{ route('admin.books.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Total Books" :value="number_format($totalBookCopies)" hint="All copies in library" color="maroon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </x-stat-card>
        </a>
        <a href="{{ route('admin.books.available') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Available Books" :value="number_format($totalAvailable)" hint="{{ $totalBookCopies > 0 ? round($totalAvailable / $totalBookCopies * 100, 1) : 0 }}% of total" color="emerald">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </x-stat-card>
        </a>
        <a href="{{ route('admin.returns.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Borrowed Books" :value="number_format($totalBorrowed)" hint="{{ $totalBookCopies > 0 ? round($totalBorrowed / $totalBookCopies * 100, 1) : 0 }}% of total" color="amber">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
            </x-stat-card>
        </a>
        <a href="{{ route('admin.users.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Active Users" :value="number_format($activeUsers)" hint="Students &amp; Teachers" color="bark">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </x-stat-card>
        </a>
        <a href="{{ route('admin.returns.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Overdue Items" :value="number_format($overdueItems)" hint="Require attention" color="rose">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </x-stat-card>
        </a>
        <a href="{{ route('admin.pending-users') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Pending Approvals" :value="number_format($pendingApprovals ?? 0)" hint="Awaiting review" color="rose">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </x-stat-card>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Penalty overview -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Penalty Overview</h3>
                <a href="{{ route('admin.penalties.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-rose-600 dark:text-rose-400">₱{{ number_format($totalUnpaidPenalties, 2) }}</div>
                    <div class="text-xs text-bark-400">From {{ $usersWithPenalties }} users</div>
                </div>
            </div>
            <div class="text-sm space-y-1.5 pt-3 border-t border-bark-100 dark:border-bark-800">
                <div class="flex justify-between"><span class="text-bark-500 dark:text-bark-400">Unpaid Overdue Fines</span><span class="font-medium text-bark-700 dark:text-bark-200">₱{{ number_format($overdueFines, 2) }}</span></div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <a href="{{ route('admin.pending-users') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-bark-200 dark:border-bark-700 hover:bg-parchment-100 dark:hover:bg-bark-800 text-center">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    <span class="text-xs font-medium text-bark-700 dark:text-bark-200">Pending Approvals</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-bark-200 dark:border-bark-700 hover:bg-parchment-100 dark:hover:bg-bark-800 text-center">
                    <svg class="w-5 h-5 text-bark-600 dark:text-bark-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" /></svg>
                    <span class="text-xs font-medium text-bark-700 dark:text-bark-200">All Users</span>
                </a>
                <a href="{{ route('admin.borrows.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-bark-200 dark:border-bark-700 hover:bg-parchment-100 dark:hover:bg-bark-800 text-center">
                    <svg class="w-5 h-5 text-maroon-600 dark:text-maroon-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    <span class="text-xs font-medium text-bark-700 dark:text-bark-200">Borrow Requests</span>
                </a>
                <a href="{{ route('admin.returns.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-bark-200 dark:border-bark-700 hover:bg-parchment-100 dark:hover:bg-bark-800 text-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                    <span class="text-xs font-medium text-bark-700 dark:text-bark-200">Returns</span>
                </a>
                <a href="{{ route('admin.books.index') }}" class="flex flex-col items-center gap-2 p-4 rounded-lg border border-bark-200 dark:border-bark-700 hover:bg-parchment-100 dark:hover:bg-bark-800 text-center">
                    <svg class="w-5 h-5 text-bark-600 dark:text-bark-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    <span class="text-xs font-medium text-bark-700 dark:text-bark-200">Book Management</span>
                </a>
            </div>
        </div>
    </div>


    <!-- Return calendar -->
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 mb-6"
         x-data="{
            loans: {!! $calendarLoans->toJson() !!},
            selected: null,
            today: new Date(),
            get monthLabel() { return this.today.toLocaleString('en-US', { month: 'long', year: 'numeric' }); },
            get days() {
                const year = this.today.getFullYear(), month = this.today.getMonth();
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const days = [];
                for (let i = 0; i < firstDay; i++) days.push(null);
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    days.push({ day: d, dateStr, count: (this.loans[dateStr] || []).length });
                }
                return days;
            },
            selectDay(dateStr) { this.selected = dateStr; }
         }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Return Calendar</h3>
            <span class="text-sm text-bark-500 dark:text-bark-400" x-text="monthLabel"></span>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center text-[11px] text-bark-400 mb-1">
            <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
        </div>
        <div class="grid grid-cols-7 gap-1">
            <template x-for="(d, i) in days" :key="i">
                <button type="button" @click="d && selectDay(d.dateStr)" x-show="d !== null"
                    class="aspect-square rounded-lg text-xs flex flex-col items-center justify-center relative"
                    :class="d && selected === d.dateStr ? 'bg-maroon-700 text-white' : (d && d.count > 0 ? 'bg-amber-50 dark:bg-amber-900/20 text-bark-700 dark:text-bark-200 hover:bg-amber-100' : 'text-bark-500 dark:text-bark-400 hover:bg-parchment-100 dark:hover:bg-bark-800')">
                    <span x-text="d ? d.day : ''"></span>
                    <span x-show="d && d.count > 0" class="w-1.5 h-1.5 rounded-full bg-rose-500 absolute bottom-1" :class="selected === (d ? d.dateStr : '') ? 'bg-white' : ''"></span>
                </button>
            </template>
        </div>

        <div x-show="selected" x-cloak class="mt-4 pt-4 border-t border-bark-100 dark:border-bark-800">
            <p class="text-xs font-semibold text-bark-500 dark:text-bark-400 mb-2" x-text="'Due on ' + selected + ':'"></p>
            <template x-if="!(loans[selected] && loans[selected].length)">
                <p class="text-sm text-bark-400">No books scheduled to be returned this day.</p>
            </template>
            <ul class="space-y-2">
                <template x-for="item in (loans[selected] || [])" :key="item.book + item.borrower">
                    <li class="text-sm bg-parchment-50 dark:bg-bark-800/60 rounded-lg p-2.5">
                        <div class="font-medium text-bark-800 dark:text-parchment-100" x-text="item.book"></div>
                        <div class="text-xs text-bark-500 dark:text-bark-400" x-text="'Borrower: ' + item.borrower"></div>
                        <div class="text-xs text-bark-400" x-text="'Borrowed: ' + item.borrowed_at + ' · Due: ' + item.due_at"></div>
                    </li>
                </template>
            </ul>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Today's transactions -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Today's Transactions</h3>
            <ul class="space-y-3.5 text-sm">
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Books Borrowed</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ $booksBorrowedToday }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Books Returned</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ $booksReturnedToday }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">New Users Registered</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ $newUsersToday }}</span>
                </li>
            </ul>
        </div>

        <!-- Borrowings chart -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Borrowings Overview (Last 6 Weeks)</h3>
                <div class="flex items-center gap-4 text-xs text-bark-500 dark:text-bark-400">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-maroon-700"></span>Borrowed</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Returned</span>
                </div>
            </div>
            <canvas id="assistantChart" height="120"></canvas>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Recent transactions -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Recent Transactions</h3>
                <a href="{{ route('admin.returns.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">User</th>
                            <th class="px-2 pb-2 font-medium">Book</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Date</th>
                            <th class="px-2 pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @forelse($recentTransactions as $t)
                            <tr>
                                <td class="px-2 py-2.5">
                                    <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $t->user->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-bark-400 capitalize">{{ $t->user->role ?? '' }}</div>
                                </td>
                                <td class="px-2 py-2.5 text-bark-600 dark:text-bark-300">{{ $t->book->title ?? 'Unknown' }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $t->updated_at->format('M j, g:i A') }}</td>
                                <td class="px-2 py-2.5">
                                    @php
                                        $badge = match($t->status) {
                                            'returned' => 'bg-bark-100 text-bark-600 dark:bg-bark-800 dark:text-bark-300',
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                            default => 'bg-maroon-100 text-maroon-700 dark:bg-maroon-900/40 dark:text-maroon-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase {{ $badge }}">{{ $t->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-2 py-6 text-center text-bark-400">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming due dates -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Upcoming Due Dates</h3>
                <a href="{{ route('admin.returns.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:underline">View all</a>
            </div>
            <ul class="space-y-3">
                @forelse($upcomingDue as $r)
                    <li class="flex items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $r->book->title ?? 'Unknown' }}</div>
                            <div class="text-xs text-bark-400 truncate">{{ $r->user->name ?? 'Unknown' }}</div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs text-bark-500 dark:text-bark-400">{{ \Carbon\Carbon::parse($r->due_at)->format('M j, Y') }}</div>
                            <span class="text-[10px] font-semibold uppercase bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 px-1.5 py-0.5 rounded-full">Due Soon</span>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">Nothing due in the next 3 days.</li>
                @endforelse
            </ul>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('assistantChart'), {
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
</x-assistant-layout>
