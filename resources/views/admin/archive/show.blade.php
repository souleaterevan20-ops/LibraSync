<x-admin-layout title="Archive Detail">

    <div class="max-w-3xl mx-auto space-y-6">

        <a href="{{ route('admin.archive.index') }}" class="text-sm font-medium text-bark-500 hover:text-bark-700 dark:hover:text-parchment-100 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            Back to Archive Management
        </a>

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-lg font-bold text-bark-800 dark:text-parchment-100">{{ $archive->semester }}</h1>
                    <p class="text-xs text-bark-400 mt-1">Created {{ $archive->created_at->format('M d, Y g:i A') }} by {{ $archive->createdBy->name ?? '—' }} · {{ $archive->sizeHuman() }}</p>
                </div>
                <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded-full {{ $archive->status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300' }}">{{ $archive->status }}</span>
            </div>

            <dl class="grid grid-cols-3 gap-4 mt-5 text-sm">
                <div><dt class="text-xs text-bark-400">Records</dt><dd class="font-semibold text-bark-700 dark:text-bark-200">{{ $archive->record_count }}</dd></div>
                <div><dt class="text-xs text-bark-400">Users</dt><dd class="font-semibold text-bark-700 dark:text-bark-200">{{ $archive->user_count }}</dd></div>
                <div><dt class="text-xs text-bark-400">Penalties / Payments</dt><dd class="font-semibold text-bark-700 dark:text-bark-200">{{ $archive->penalty_count }} / {{ $archive->payment_count }}</dd></div>
            </dl>

            <div class="flex flex-wrap gap-2 mt-5">
                <a href="{{ route('admin.archive.download-zip', $archive) }}" class="bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">Download Full ZIP</a>
                <a href="{{ route('admin.archive.view-report', $archive) }}" target="_blank" class="bg-bark-100 dark:bg-bark-800 hover:bg-bark-200 dark:hover:bg-bark-700 text-bark-700 dark:text-parchment-100 text-sm font-semibold px-4 py-2 rounded-lg">View Report</a>
            </div>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-1">Included Tables</h2>
            <p class="text-xs text-bark-400 mb-4">Each table can be downloaded on its own as a CSV, without unzipping the full package.</p>

            @if($csvFiles->isEmpty())
                <p class="text-sm text-bark-400 text-center py-6">No individual CSV files were found for this archive — try the full ZIP download above.</p>
            @else
                <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                    @foreach($csvFiles as $file)
                        <li class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-bark-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                <span class="text-sm font-medium text-bark-700 dark:text-bark-200">{{ $file['name'] }}</span>
                                <span class="text-xs text-bark-400">{{ $file['size'] }}</span>
                            </div>
                            <a href="{{ route('admin.archive.download-csv', ['archive' => $archive, 'file' => basename($file['path'])]) }}" class="text-xs font-semibold text-maroon-700 dark:text-maroon-300 hover:underline">Download CSV</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

</x-admin-layout>
