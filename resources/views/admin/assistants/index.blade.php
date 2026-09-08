<x-admin-layout title="Library Staff Management">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Create Library Staff</h3>
            <form action="{{ route('admin.assistants.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="name" required placeholder="Full name" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <input type="email" name="email" required placeholder="Email address" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <input type="password" name="password" required minlength="8" placeholder="Temporary password" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <button class="w-full bg-maroon-700 hover:bg-maroon-800 text-white font-semibold px-4 py-2 rounded-lg text-sm">Create Account</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Library Staffs</h3>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Name</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Email</th>
                            <th class="px-2 pb-2 font-medium">Status</th>
                            <th class="px-2 pb-2 font-medium hidden lg:table-cell">Last Login</th>
                            <th class="px-2 pb-2 font-medium hidden lg:table-cell">Last Logout</th>
                            <th class="px-2 pb-2 font-medium hidden xl:table-cell">Last Managed Transaction</th>
                            <th class="px-2 pb-2 font-medium text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($assistants as $assistant)
                            <tr>
                                <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">{{ $assistant->name }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $assistant->email }}</td>
                                <td class="px-2 py-2.5">
                                    @if($assistant->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">Deactivated</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2.5 hidden lg:table-cell text-bark-500 dark:text-bark-400 text-xs">{{ $assistant->last_login_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                <td class="px-2 py-2.5 hidden lg:table-cell text-bark-500 dark:text-bark-400 text-xs">{{ $assistant->last_logout_at?->format('M d, Y g:i A') ?? '—' }}</td>
                                <td class="px-2 py-2.5 hidden xl:table-cell text-bark-500 dark:text-bark-400 text-xs">
                                    @if($assistant->last_transaction_label)
                                        {{ $assistant->last_transaction_label }}<br>
                                        <span class="text-bark-400">{{ $assistant->last_transaction_at?->format('M d, Y g:i A') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-2 py-2.5 text-center space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.assistants.activity', $assistant) }}" class="text-xs font-medium text-maroon-700 dark:text-amber-300 hover:underline">Activity</a>
                                    <form action="{{ route('admin.assistants.toggle-active', $assistant) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-xs font-medium text-bark-600 hover:underline">{{ $assistant->is_active ? 'Deactivate' : 'Enable' }}</button>
                                    </form>
                                    <form action="{{ route('admin.assistants.destroy', $assistant) }}" method="POST" class="inline" onsubmit="return confirm('Delete this Library Staff account?');">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-medium text-rose-700 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
