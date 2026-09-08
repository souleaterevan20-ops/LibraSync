@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
    $isSuperAdmin = auth()->user()->role === 'super_admin';
@endphp
<x-dynamic-component :component="$layoutComponent" title="All Users">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100">
                {{ $showingDeleted ? 'Deleted Users' : 'All Users' }}
            </h3>
        </div>

        <!-- Search & filters (spec #13) -->
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-6 gap-2 mb-4">
            <input type="text" name="search" value="{{ $search }}" placeholder="Name, email, or ID..."
                class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm lg:col-span-2">
            <select name="role" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <option value="">All Roles</option>
                <option value="student" @selected(request('role') === 'student')>Student</option>
                <option value="teacher" @selected(request('role') === 'teacher')>Teacher</option>
            </select>
            <select name="status" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
                <option value="pending" @selected(in_array(request('status'), ['pending', 'unverified']))>Pending</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                @if($isSuperAdmin)
                    <option value="deleted" @selected(request('status') === 'deleted')>Deleted</option>
                @endif
            </select>
            <select name="department" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="flex-1 bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Search</button>
                <a href="{{ route('admin.users.index') }}" class="flex-1 text-center bg-bark-100 dark:bg-bark-800 text-bark-600 dark:text-bark-300 text-sm font-medium px-3 py-2 rounded-lg">Reset</a>
            </div>
        </form>

        @if($users->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">No users found.</p>
        @else
            @if(! $showingDeleted)
            <form method="POST" id="bulk-form" action="{{ route('admin.users.bulk-disable') }}">
                @csrf
                @method('PATCH')

                @if($isSuperAdmin)
                    <div class="flex items-center justify-between mb-3">
                        <label class="flex items-center gap-2 text-xs text-bark-500">
                            <input type="checkbox" onclick="document.querySelectorAll('.user-row-check').forEach(cb => cb.checked = this.checked)" class="rounded border-bark-300">
                            Select All
                        </label>
                        <div class="space-x-3">
                            <button type="submit" formaction="{{ route('admin.users.bulk-enable') }}" onclick="return confirm('Enable the selected users?');" class="text-xs font-semibold text-emerald-700 hover:underline">Enable Selected</button>
                            <button type="submit" formaction="{{ route('admin.users.bulk-disable') }}" onclick="return confirm('Disable the selected users?');" class="text-xs font-semibold text-rose-700 hover:underline">Disable Selected</button>
                        </div>
                    </div>
                @endif
            @endif

                <div class="overflow-x-auto -mx-1">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                                @if($isSuperAdmin && ! $showingDeleted)
                                    <th class="px-2 pb-2 font-medium w-6"></th>
                                @endif
                                <th class="px-2 pb-2 font-medium">Name</th>
                                <th class="px-2 pb-2 font-medium hidden sm:table-cell">Email</th>
                                <th class="px-2 pb-2 font-medium">Role</th>
                                <th class="px-2 pb-2 font-medium hidden md:table-cell">Department</th>
                                <th class="px-2 pb-2 font-medium hidden md:table-cell">Points</th>
                                <th class="px-2 pb-2 font-medium">Status</th>
                                <th class="px-2 pb-2 font-medium text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                            @foreach($users as $user)
                                <tr>
                                    @if($isSuperAdmin && ! $showingDeleted)
                                        <td class="px-2 py-2.5">
                                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-row-check rounded border-bark-300" form="bulk-form">
                                        </td>
                                    @endif
                                    <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">
                                        @if($showingDeleted)
                                            {{ $user->name }}
                                        @else
                                            <a href="{{ route('admin.users.show', $user) }}" class="hover:underline">{{ $user->name }}</a>
                                        @endif
                                        <span class="text-[10px] text-bark-400 font-normal block">ID #{{ $user->id }}</span>
                                    </td>
                                    <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $user->email }}</td>
                                    <td class="px-2 py-2.5">{{ $user->roleLabel() }}</td>
                                    <td class="px-2 py-2.5 hidden md:table-cell text-bark-500 dark:text-bark-400">{{ $user->department ?? '—' }}</td>
                                    <td class="px-2 py-2.5 hidden md:table-cell">{{ $user->points }}</td>
                                    <td class="px-2 py-2.5">
                                        @php $status = $showingDeleted ? 'deleted' : $user->accountStatus(); @endphp
                                        @if($status === 'deleted')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-bark-100 text-bark-500 dark:bg-bark-800">Deleted</span>
                                        @elseif($status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Active</span>
                                        @elseif($status === 'disabled')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-700">Disabled</span>
                                        @elseif($status === 'pending')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">Pending</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-bark-100 text-bark-500 dark:bg-bark-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2.5 text-center space-x-2 whitespace-nowrap">
                                        @if($showingDeleted)
                                            @if($isSuperAdmin)
                                                <button type="submit" form="restore-user-{{ $user->id }}" onclick="return confirm('Restore this account?');" class="text-xs font-medium text-emerald-700 hover:underline">Restore</button>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.users.show', $user) }}" class="text-xs font-medium text-maroon-700 dark:text-amber-300 hover:underline">View</a>
                                            @php
                                                $canToggle = $isSuperAdmin || in_array($user->role, ['student', 'teacher'], true);
                                            @endphp
                                            @if($canToggle)
                                                <button type="submit" form="toggle-active-{{ $user->id }}" onclick="return confirm('{{ $user->is_active ? 'Disable' : 'Enable' }} this account?');" class="text-xs font-medium text-bark-600 hover:underline">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
                                            @endif
                                            @if($isSuperAdmin)
                                                <button type="submit" form="delete-user-{{ $user->id }}" onclick="return confirm('Delete this account? History is preserved and this can be undone.');" class="text-xs font-medium text-rose-700 hover:underline">Delete</button>
                                            @elseif(! $user->is_active)
                                                <button type="submit" form="reactivate-user-{{ $user->id }}" onclick="return confirm('Reactivate this account?');" class="text-xs font-medium text-emerald-700 hover:underline">Reactivate</button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @if(! $showingDeleted)
            </form>
            @endif

            <!-- Standalone forms for per-row actions (kept outside the bulk-select <form> since nested forms aren't valid HTML) -->
            @foreach($users as $user)
                @if($showingDeleted)
                    @if($isSuperAdmin)
                        <form id="restore-user-{{ $user->id }}" action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="hidden">
                            @csrf @method('PATCH')
                        </form>
                    @endif
                @else
                    @php
                        $canToggle = $isSuperAdmin || in_array($user->role, ['student', 'teacher'], true);
                    @endphp
                    @if($canToggle)
                        <form id="toggle-active-{{ $user->id }}" action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="hidden">
                            @csrf @method('PATCH')
                        </form>
                    @endif
                    @if($isSuperAdmin)
                        <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @elseif(! $user->is_active)
                        <form id="reactivate-user-{{ $user->id }}" action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="hidden">
                            @csrf @method('PATCH')
                        </form>
                    @endif
                @endif
            @endforeach
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    </div>
</x-dynamic-component>
