@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
    $unread = auth()->user()->unreadNotifications()->count();
@endphp

<x-dynamic-component :component="$layoutComponent" title="Notification Center" :unread-notifications="$unread">

    @if (session('status'))
        <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <button onclick="history.back()" class="inline-flex items-center gap-1.5 text-sm font-medium text-bark-500 dark:text-bark-400 hover:text-bark-800 dark:hover:text-parchment-100 mb-4">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        Back
    </button>

    <div x-data="{ unread: {{ $unread }} }">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-semibold text-bark-800 dark:text-parchment-100">
                Notification Center <span class="text-sm font-normal text-bark-400" x-show="unread > 0" x-cloak>(<span x-text="unread"></span> unread)</span>
            </h2>
            <button x-show="unread > 0" x-cloak @click="
                fetch('{{ route('notifications.read-all') }}', {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(() => {
                    unread = 0;
                    document.querySelectorAll('.notif-row').forEach(el => el.classList.remove('bg-maroon-50/40', 'dark:bg-maroon-900/10'));
                    document.querySelectorAll('.notif-dot').forEach(el => el.classList.replace('bg-maroon-600', 'bg-transparent'));
                    document.querySelectorAll('.mark-read-btn').forEach(el => el.remove());
                });
            " class="text-sm font-medium text-maroon-700 dark:text-amber-300 hover:underline">Mark all as read</button>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 divide-y divide-bark-100 dark:divide-bark-800">
            @forelse($notifications as $notification)
                <div class="notif-row flex items-start gap-3 p-4 {{ $notification->is_read ? '' : 'bg-maroon-50/40 dark:bg-maroon-900/10' }}">
                    <div class="notif-dot w-2 h-2 mt-2 rounded-full shrink-0 {{ $notification->is_read ? 'bg-transparent' : 'bg-maroon-600' }}"></div>
                    <a href="{{ route('notifications.open', $notification) }}" class="min-w-0 flex-1 hover:opacity-80">
                        <div class="text-sm font-semibold text-bark-800 dark:text-parchment-100">{{ $notification->title }}</div>
                        <div class="text-sm text-bark-500 dark:text-bark-400 mt-0.5">{{ $notification->message }}</div>
                        <div class="text-[11px] text-bark-400 dark:text-bark-500 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                    </a>
                    @unless($notification->is_read)
                        <button type="button" class="mark-read-btn text-xs font-medium text-maroon-700 dark:text-amber-300 hover:underline whitespace-nowrap" @click="
                            fetch('{{ route('notifications.read', $notification) }}', {
                                method: 'PATCH',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(() => {
                                unread = Math.max(0, unread - 1);
                                $el.closest('.notif-row').classList.remove('bg-maroon-50/40', 'dark:bg-maroon-900/10');
                                $el.closest('.notif-row').querySelector('.notif-dot').classList.replace('bg-maroon-600', 'bg-transparent');
                                $el.remove();
                            });
                        ">Mark read</button>
                    @endunless
                </div>
            @empty
                <div class="p-8 text-center text-sm text-bark-400 dark:text-bark-500">You're all caught up — no notifications yet.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>

</x-dynamic-component>
