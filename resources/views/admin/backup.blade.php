@php
    $role = auth()->user()->role;
    $layoutComponent = $role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
    $isSuperAdmin = $role === 'super_admin';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Backup &amp; Restore">

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

    @unless($isSuperAdmin)
        <div class="mb-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-lg text-xs">
            As a Library Staff, you can restore deleted books and transaction records. Restoring deleted user accounts and managing full system backup files are Super Admin-only.
        </div>
    @endunless

    @if($isSuperAdmin)
        <!-- Full system backups -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 mb-6">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="font-semibold text-bark-800 dark:text-parchment-100">System Backups</h3>
                    @if(!$isSqlite)
                        <p class="text-xs text-bark-400 mt-1">This deployment isn't using SQLite, so one-click file backups aren't available here — use your database's native backup tools (e.g. mysqldump).</p>
                    @else
                        <p class="text-xs text-bark-400 mt-1">Creates a full copy of the live database file. A safety copy is always taken automatically before any restore.</p>
                    @endif
                </div>
                @if($isSqlite)
                    <form method="POST" action="{{ route('admin.backup.create') }}">
                        @csrf
                        <button class="bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">Create Manual Backup</button>
                    </form>
                @endif
            </div>

            @if($isSqlite)
                @if($backupFiles->isEmpty())
                    <p class="text-sm text-bark-400 text-center py-8">No backups yet. Click "Create Manual Backup" to make one.</p>
                @else
                    <div class="overflow-x-auto -mx-1">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                                    <th class="px-2 pb-2 font-medium">File</th>
                                    <th class="px-2 pb-2 font-medium">Created</th>
                                    <th class="px-2 pb-2 font-medium">Size</th>
                                    <th class="px-2 pb-2 font-medium hidden md:table-cell">Contents</th>
                                    <th class="px-2 pb-2 font-medium text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                                @foreach($backupFiles as $file)
                                    <tr>
                                        <td class="px-2 py-2.5 font-mono text-xs text-bark-700 dark:text-bark-200">{{ $file['name'] }}</td>
                                        <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $file['created_at']->format('M d, Y g:i A') }}</td>
                                        <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400">{{ $file['size'] }}</td>
                                        <td class="px-2 py-2.5 hidden md:table-cell text-xs text-bark-400">
                                            @if($file['manifest'])
                                                {{ $file['manifest']['total_users'] }} users &middot; {{ $file['manifest']['total_books'] }} books &middot;
                                                {{ $file['manifest']['total_borrow_records'] }} records &middot; {{ $file['manifest']['included_media_count'] }} media &middot;
                                                by {{ $file['manifest']['created_by'] }}
                                            @else
                                                <span class="italic">No manifest (legacy backup)</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2.5 text-center space-x-2 whitespace-nowrap">
                                            <form method="POST" action="{{ route('admin.backup.restore') }}" class="inline-flex items-center gap-1"
                                                onsubmit="return confirm('This will OVERWRITE the live database with this backup file. A safety copy of the current state will be saved first. Continue?');">
                                                @csrf
                                                <input type="hidden" name="filename" value="{{ $file['name'] }}">
                                                <input type="text" name="confirm" required placeholder="Type RESTORE"
                                                    class="text-xs rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 w-28">
                                                <button class="text-xs font-medium text-amber-700 dark:text-amber-400 hover:underline">Restore</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.backup.delete') }}" class="inline" onsubmit="return confirm('Delete this backup file permanently?');">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="filename" value="{{ $file['name'] }}">
                                                <button class="text-xs font-medium text-rose-700 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    @endif

    <!-- Deleted records -->
    <div class="grid gap-6 lg:grid-cols-2">

        @if($isSuperAdmin)
            <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Deleted Users</h3>
                @if($deletedUsers->isEmpty())
                    <p class="text-sm text-bark-400 text-center py-6">No deleted user accounts.</p>
                @else
                    <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($deletedUsers as $user)
                            <li class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-bark-400">{{ $user->roleLabel() }} &middot; deleted {{ $user->deleted_at->diffForHumans() }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.backup.restore-user', $user->id) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-xs font-semibold text-emerald-700 hover:underline whitespace-nowrap">Restore</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Deleted Books</h3>
            @if($deletedBooks->isEmpty())
                <p class="text-sm text-bark-400 text-center py-6">No deleted books.</p>
            @else
                <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                    @foreach($deletedBooks as $book)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $book->title }}</p>
                                <p class="text-xs text-bark-400">ISBN {{ $book->isbn }} &middot; deleted {{ $book->deleted_at->diffForHumans() }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.backup.restore-book', $book->id) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs font-semibold text-emerald-700 hover:underline whitespace-nowrap">Restore</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Deleted Transaction / Inventory Records</h3>
            @if($deletedRecords->isEmpty())
                <p class="text-sm text-bark-400 text-center py-6">No deleted transaction records.</p>
            @else
                <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                    @foreach($deletedRecords as $record)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-bark-800 dark:text-parchment-100 truncate">{{ $record->book->title ?? 'Deleted book' }} — {{ $record->user->name ?? 'Deleted user' }}</p>
                                <p class="text-xs text-bark-400">Status: {{ ucfirst($record->status) }} &middot; deleted {{ $record->deleted_at->diffForHumans() }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.backup.restore-record', $record->id) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs font-semibold text-emerald-700 hover:underline whitespace-nowrap">Restore</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-dynamic-component>
