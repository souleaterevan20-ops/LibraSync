@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'assistant-layout',
    };
    $view = $view ?? 'pending';
    $isStaff = auth()->user()->role === 'student_assistant';
@endphp

<x-dynamic-component :component="$layoutComponent" :title="$view === 'rejected' ? 'Registration History' : 'Pending Account Approvals'">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <!-- Spec #79: rejected registrations are kept for administrative reference,
         in a separate tab so they never get mixed back into "awaiting a decision". -->
    <div class="flex items-center gap-2 mb-4 text-sm font-medium">
        <a href="{{ route('admin.pending-users') }}"
           class="px-3 py-1.5 rounded-lg {{ $view === 'pending' ? 'bg-maroon-700 text-white' : 'bg-bark-100 dark:bg-bark-800 text-bark-600 dark:text-bark-300' }}">
            Pending Approvals
        </a>
        <a href="{{ route('admin.rejected-users') }}"
           class="px-3 py-1.5 rounded-lg {{ $view === 'rejected' ? 'bg-maroon-700 text-white' : 'bg-bark-100 dark:bg-bark-800 text-bark-600 dark:text-bark-300' }}">
            Registration History (Rejected)
        </a>
    </div>

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <div class="flex items-center gap-2 mb-4">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100">
                {{ $view === 'rejected' ? 'Rejected Registrations' : 'Users Awaiting Administrative Vetting' }}
            </h3>
            @if(! $isStaff)
                <span class="text-[10px] font-bold uppercase tracking-wide bg-bark-100 dark:bg-bark-800 text-bark-500 dark:text-bark-400 px-2 py-0.5 rounded-full">View Only</span>
            @endif
        </div>

        @if($pendingUsers->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">
                {{ $view === 'rejected' ? 'No registrations have been rejected.' : 'There are currently no accounts waiting for approval.' }}
            </p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Name</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Email</th>
                            <th class="px-2 pb-2 font-medium">Requested Role</th>
                            <th class="px-2 pb-2 font-medium hidden md:table-cell">
                                {{ $view === 'rejected' ? 'Rejected At' : 'Registered At' }}
                            </th>
                            @if($view === 'rejected')
                                <th class="px-2 pb-2 font-medium hidden lg:table-cell">Reason</th>
                            @endif
                            <th class="px-2 pb-2 font-medium text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($pendingUsers as $user)
                            <tr>
                                <td class="px-2 py-2.5 font-medium text-bark-800 dark:text-parchment-100">{{ $user->name }}</td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $user->email }}</td>
                                <td class="px-2 py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        {{ $user->roleLabel() }}
                                    </span>
                                </td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden md:table-cell">
                                    {{ $view === 'rejected' ? $user->rejected_at?->format('M d, Y H:i') : $user->created_at->format('M d, Y H:i') }}
                                </td>
                                @if($view === 'rejected')
                                    <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden lg:table-cell">{{ $user->rejection_reason }}</td>
                                @endif
                                <td class="px-2 py-2.5 text-center space-x-2 whitespace-nowrap">
                                    @if($view === 'pending')
                                        @if($isStaff)
                                            <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">
                                                    Approve Access
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="inline reject-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="reason" class="reject-reason-input">
                                                <button type="button" class="reject-trigger bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold uppercase transition-colors">
                                                    Reject
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 px-2 py-1 border border-bark-200 dark:border-bark-700 rounded-lg">View Only</span>
                                        @endif
                                    @else
                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-bark-400 px-2 py-1 border border-bark-200 dark:border-bark-700 rounded-lg">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($view === 'pending' && $isStaff)
        <script>
            // Spec #79: a rejection always requires a reason — the button
            // prompts for it and submits the form with a hidden field
            // rather than the old confirm()-only flow, which never actually
            // captured why a registration was turned down.
            document.querySelectorAll('.reject-trigger').forEach(function (button) {
                button.addEventListener('click', function () {
                    const form = button.closest('.reject-form');
                    const reason = prompt('Reason for rejecting this registration:');
                    if (reason === null) return; // cancelled
                    if (reason.trim() === '') {
                        alert('A reason is required to reject a registration.');
                        return;
                    }
                    form.querySelector('.reject-reason-input').value = reason.trim();
                    form.submit();
                });
            });
        </script>
    @endif
</x-dynamic-component>
