@php
    $stockPct = fn($n) => $stock['total'] > 0 ? round(($n / $stock['total']) * 100, 1) : 0;
@endphp

<x-admin-layout title="Super Admin Dashboard" :unread-notifications="$unreadNotifications" :pending-registrations="$pendingRegistrations">

    <x-announcement-banner />
    @if (session('status'))
        <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <!-- Stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('admin.books.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Total Books" :value="number_format($totalBookCopies)" hint="All copies in library" color="maroon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </x-stat-card>
        </a>

        <a href="{{ route('admin.books.available') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Available Copies" :value="number_format($totalAvailable)" hint="Ready to borrow" color="emerald">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </x-stat-card>
        </a>

        <a href="{{ route('admin.books.borrowed') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Borrowed Books" :value="number_format($totalBorrowed)" hint="Currently borrowed" color="amber">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </x-stat-card>
        </a>

        <a href="{{ route('reports.show', 'overdue') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Overdue Books" :value="number_format($overdueBooks)" hint="Past due date" color="rose">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </x-stat-card>
        </a>

        <a href="{{ route('admin.users.index') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Total Users" :value="number_format($totalUsers)" :hint="'Active Students & Teachers' . ($disabledStudentTeacher > 0 ? ' - ' . number_format($disabledStudentTeacher) . ' disabled' : '')" color="bark">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </x-stat-card>
        </a>

        <a href="{{ route('admin.pending-users') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Pending Approvals" :value="number_format($pendingRegistrations)" hint="Awaiting registration review" color="amber">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </x-stat-card>
        </a>

        <a href="{{ route('reports.show', 'penalty') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Outstanding Penalties" value="₱{{ number_format($outstandingPenalties, 2) }}" hint="Unpaid across all users" color="rose">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v7.5m0 0-3-3m3 3 3-3m-8.25 6h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Z" />
            </x-stat-card>
        </a>

        <a href="{{ route('reports.show', 'payments') }}" class="block hover:opacity-90 transition-opacity">
            <x-stat-card label="Collected Penalties" value="₱{{ number_format($collectedPenalties, 2) }}" hint="All-time settlements" color="emerald">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </x-stat-card>
        </a>
    </div>


    <!-- Charts row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        <!-- Books stock donut -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 overflow-hidden">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Books Stock Overview</h3>
            <div class="flex flex-col sm:flex-row items-center gap-6 min-w-0">
                <div class="relative w-32 h-32 shrink-0">
                    <canvas id="stockChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-lg font-bold text-bark-800 dark:text-parchment-100">{{ number_format($stock['total']) }}</span>
                        <span class="text-[10px] text-bark-500 dark:text-bark-400">Total Copies</span>
                    </div>
                </div>
                <div class="space-y-3 text-sm min-w-0 w-full">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="text-bark-600 dark:text-bark-300 truncate">Available</span>
                        <span class="ml-auto font-semibold text-bark-800 dark:text-parchment-100 text-right shrink-0">{{ number_format($stock['available']) }} ({{ $stockPct($stock['available']) }}%)</span>
                    </div>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0"></span>
                        <span class="text-bark-600 dark:text-bark-300 truncate">Borrowed</span>
                        <span class="ml-auto font-semibold text-bark-800 dark:text-parchment-100 text-right shrink-0">{{ number_format($stock['borrowed']) }} ({{ $stockPct($stock['borrowed']) }}%)</span>
                    </div>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                        <span class="text-bark-600 dark:text-bark-300 truncate">Reserved</span>
                        <span class="ml-auto font-semibold text-bark-800 dark:text-parchment-100 text-right shrink-0">{{ number_format($stock['reserved']) }} ({{ $stockPct($stock['reserved']) }}%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Borrowings line chart -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Borrowings Overview (Last 6 Weeks)</h3>
                <div class="flex items-center gap-4 text-xs text-bark-500 dark:text-bark-400">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-maroon-700"></span>Borrowed</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Returned</span>
                </div>
            </div>
            <canvas id="borrowChart" height="130"></canvas>
        </div>
    </div>

    <!-- Tables row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        <!-- Latest borrowings -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Latest Borrowings</h3>
                <a href="{{ route('reports.index') }}" class="text-xs font-medium text-maroon-600 dark:text-maroon-400 hover:text-maroon-700 dark:hover:text-maroon-300">View all</a>
            </div>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 dark:text-bark-500 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">User</th>
                            <th class="px-2 pb-2 font-medium">Book</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Due Date</th>
                            <th class="px-2 pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @forelse($latestBorrowings as $row)
                            <tr>
                                <td class="px-2 py-2.5">
                                    <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $row['user']->name ?? 'Unknown' }}</div>
                                    <div class="text-xs text-bark-400 dark:text-bark-500 capitalize">{{ $row['user']->role ?? '' }}</div>
                                </td>
                                <td class="px-2 py-2.5 text-bark-600 dark:text-bark-300">{{ $row['book']->title ?? 'Unknown' }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $row['due'] ? \Carbon\Carbon::parse($row['due'])->format('M j, Y') : '—' }}</td>
                                <td class="px-2 py-2.5">
                                    @php
                                        $badge = match($row['status']) {
                                            'overdue' => 'bg-rose-100 text-rose-700',
                                            'returned' => 'bg-slate-100 text-bark-600 dark:text-bark-300',
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-maroon-100 text-maroon-700 dark:bg-maroon-900/40 dark:text-maroon-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold uppercase {{ $badge }}">{{ $row['status'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-2 py-6 text-center text-bark-400 dark:text-bark-500">No borrowing records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System summary -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">System Summary</h3>
            <ul class="space-y-3.5 text-sm">
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Active Library Staffs</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ $activeStudentAssistants }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">New Books (This Month)</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ $newBooksThisMonth }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Pending Borrow Requests</span>
                    <span class="font-semibold text-amber-600">{{ $pendingBorrowRequests }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-bark-600 dark:text-bark-300">Unread Notifications</span>
                    <span class="font-semibold text-bark-800 dark:text-parchment-100">{{ $unreadNotifications }}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Quick actions -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                @php
                    $selectedActions = auth()->user()->quick_actions ?: ['add-book', 'add-announcement', 'archive'];
                    $actionLabels = \App\Http\Controllers\ProfileController::availableQuickActions();
                    $actionMeta = \App\Http\Controllers\ProfileController::quickActionMeta();
                @endphp
                @foreach($selectedActions as $key)
                    @continue(!isset($actionMeta[$key]))
                    <a href="{{ route($actionMeta[$key]['route']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-bark-200 dark:border-bark-800 text-sm font-medium text-bark-700 dark:text-bark-200 hover:bg-parchment-100 dark:hover:bg-bark-800">
                        <svg class="w-4 h-4 text-maroon-600 dark:text-maroon-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $actionMeta[$key]['icon'] }}" /></svg>
                        {{ $actionLabels[$key] }}
                    </a>
                @endforeach
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-dashed border-bark-200 dark:border-bark-700 text-sm font-medium text-bark-400 hover:text-bark-600 hover:border-bark-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94" /></svg>
                    Customize
                </a>
            </div>
        </div>

        <!-- Top active users -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Top Active Users</h3>
                <span class="text-xs text-bark-400 dark:text-bark-500">By points</span>
            </div>
            <ul class="space-y-3">
                @forelse($topUsers as $i => $u)
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold
                            {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-slate-200 text-bark-600 dark:text-bark-300' : ($i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-bark-500 dark:text-bark-400')) }}">
                            {{ $i + 1 }}
                        </span>
                        <div class="w-8 h-8 rounded-full bg-maroon-700 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $u->name }}</div>
                            <div class="text-xs text-bark-400 dark:text-bark-500 capitalize">{{ $u->role }}</div>
                        </div>
                        <span class="text-sm font-semibold text-bark-700 dark:text-bark-200">{{ number_format($u->points) }}</span>
                    </li>
                @empty
                    <li class="text-sm text-bark-400 dark:text-bark-500 text-center py-4">No activity yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stockCtx = document.getElementById('stockChart');
            new Chart(stockCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Available', 'Borrowed', 'Reserved'],
                    datasets: [{
                        data: [{{ $stock['available'] }}, {{ $stock['borrowed'] }}, {{ $stock['reserved'] }}],
                        backgroundColor: ['#10b981', '#fbbf24', '#f43f5e'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    cutout: '72%',
                    plugins: { legend: { display: false } }
                }
            });

            const borrowCtx = document.getElementById('borrowChart');
            new Chart(borrowCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($weeks->pluck('label')) !!},
                    datasets: [
                        {
                            label: 'Borrowed',
                            data: {!! json_encode($weeks->pluck('borrowed')) !!},
                            borderColor: '#7c1e18',
                            backgroundColor: 'rgba(124,30,24,0.08)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 3,
                        },
                        {
                            label: 'Returned',
                            data: {!! json_encode($weeks->pluck('returned')) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.08)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 3,
                        }
                    ]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        });
    </script>
</x-admin-layout>
