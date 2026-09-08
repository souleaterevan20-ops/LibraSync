<x-admin-layout title="Archive Management">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <!-- End of Semester -->
    <div class="bg-gradient-to-br from-maroon-800 to-maroon-900 rounded-2xl p-6 text-white shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold">End of Semester</h2>
                <p class="text-sm text-maroon-200 mt-1 max-w-xl">
                    Archives every completed borrow record, disables all {{ $activeStudentTeacherCount }} active
                    Student/Teacher account(s), and resets their leaderboard points to zero. Accounts stay disabled
                    until a Super Admin or Library Staff directly reactivates them from the Users page.
                </p>
                <p class="text-xs text-maroon-300 mt-2">{{ $eligibleForArchive }} completed record(s) are ready to archive right now.</p>
            </div>

            <div x-data="{ semester: '' }">
                <button type="button" x-on:click="semester.trim() && $dispatch('open-modal', 'confirm-end-semester')"
                    class="bg-amber-400 hover:bg-amber-300 text-maroon-900 text-sm font-bold px-5 py-2.5 rounded-lg whitespace-nowrap disabled:opacity-50"
                    x-bind:disabled="!semester.trim()">
                    Run End of Semester
                </button>
                <input type="text" x-model="semester" placeholder="e.g. 1st Semester 2026-2027"
                    class="mt-2 block rounded-lg border-0 text-sm text-bark-800 w-full">

                <x-modal name="confirm-end-semester" maxWidth="md">
                    <form method="POST" action="{{ route('admin.archive.end-semester') }}" class="p-6 space-y-3">
                        @csrf
                        <input type="hidden" name="semester" x-bind:value="semester">
                        <h2 class="text-lg font-bold text-bark-800 dark:text-parchment-100">Confirm End of Semester</h2>
                        <p class="text-sm text-bark-500 dark:text-bark-400">This action will run for <strong x-text="semester"></strong> and cannot be undone from this screen. Please review before confirming:</p>
                        <dl class="text-sm space-y-1.5 bg-parchment-50 dark:bg-bark-800 rounded-lg p-3">
                            <div class="flex justify-between"><dt class="text-bark-500 dark:text-bark-400">Records to archive</dt><dd class="font-semibold text-bark-800 dark:text-parchment-100">{{ $preview['records'] }}</dd></div>
                            <div class="flex justify-between"><dt class="text-bark-500 dark:text-bark-400">Users affected (disabled)</dt><dd class="font-semibold text-bark-800 dark:text-parchment-100">{{ $preview['users'] }}</dd></div>
                            <div class="flex justify-between"><dt class="text-bark-500 dark:text-bark-400">Penalties on record</dt><dd class="font-semibold text-bark-800 dark:text-parchment-100">{{ $preview['penalties'] }}</dd></div>
                            <div class="flex justify-between"><dt class="text-bark-500 dark:text-bark-400">Payments on record</dt><dd class="font-semibold text-bark-800 dark:text-parchment-100">{{ $preview['payments'] }}</dd></div>
                        </dl>
                        <p class="text-xs text-rose-500">Every active Student/Teacher account will be disabled and their leaderboard points reset to zero. A downloadable archive package will be generated automatically.</p>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" x-on:click="$dispatch('close-modal', 'confirm-end-semester')" class="px-4 py-2 rounded-lg text-sm font-semibold text-bark-500 hover:bg-bark-100 dark:hover:bg-bark-800">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold bg-maroon-700 hover:bg-maroon-800 text-white">Confirm &amp; Run</button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>

    <!-- Archive History (spec #47) -->
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 mb-6">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Archive History</h3>
        @if($archiveHistory->isEmpty())
            <p class="text-sm text-bark-400 text-center py-6">No archived records yet. Run "End of Semester" above once a term is complete.</p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Semester</th>
                            <th class="px-2 pb-2 font-medium">Created</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Created By</th>
                            <th class="px-2 pb-2 font-medium">Records</th>
                            <th class="px-2 pb-2 font-medium hidden md:table-cell">Size</th>
                            <th class="px-2 pb-2 font-medium">Status</th>
                            <th class="px-2 pb-2 font-medium text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($archiveHistory as $entry)
                            <tr>
                                <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">{{ $entry->semester }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $entry->created_at->format('M d, Y') }}</td>
                                <td class="px-2 py-2.5 hidden sm:table-cell text-bark-500 dark:text-bark-400">{{ $entry->createdBy->name ?? '—' }}</td>
                                <td class="px-2 py-2.5 text-bark-600 dark:text-bark-300">{{ $entry->record_count }}</td>
                                <td class="px-2 py-2.5 hidden md:table-cell text-bark-500 dark:text-bark-400">{{ $entry->sizeHuman() }}</td>
                                <td class="px-2 py-2.5">
                                    <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded-full {{ $entry->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300' }}">{{ $entry->status }}</span>
                                </td>
                                <td class="px-2 py-2.5 text-center space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.archive.show', $entry) }}" class="text-xs font-semibold text-bark-600 dark:text-bark-300 hover:underline">View</a>
                                    <a href="{{ route('admin.archive.download-zip', $entry) }}" class="text-xs font-semibold text-maroon-700 dark:text-maroon-300 hover:underline">ZIP</a>
                                    <a href="{{ route('admin.archive.view-report', $entry) }}" target="_blank" class="text-xs font-semibold text-maroon-700 dark:text-maroon-300 hover:underline">Report</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Archived records -->
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Archived Transaction History</h3>
            @if($semesters->isNotEmpty())
                <span class="text-xs text-bark-400">Semesters on file: {{ $semesters->implode(', ') }}</span>
            @endif
        </div>

        @if($archived->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">No archived records yet. Run "End of Semester" above once a term is complete.</p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Borrower</th>
                            <th class="px-2 pb-2 font-medium">Book</th>
                            <th class="px-2 pb-2 font-medium">Returned</th>
                            <th class="px-2 pb-2 font-medium">Semester</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($archived as $record)
                            <tr>
                                <td class="px-2 py-2.5">
                                    <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $record->user->name ?? 'Deleted user' }}</div>
                                </td>
                                <td class="px-2 py-2.5 text-bark-600 dark:text-bark-300">{{ $record->book->title ?? 'Deleted book' }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $record->returned_at?->format('M d, Y') }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $record->archived_semester }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $archived->links() }}</div>
        @endif
    </div>
</x-admin-layout>
