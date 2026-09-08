@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Staff Chat">

    <div class="max-w-2xl mx-auto">
        <button onclick="history.back()" class="inline-flex items-center gap-1.5 text-sm font-medium text-bark-500 dark:text-bark-400 hover:text-bark-800 dark:hover:text-parchment-100 mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            Back
        </button>
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-bark-800 dark:text-parchment-100">Staff Chat</h2>
            <p class="text-xs text-bark-400 mt-0.5">Internal messaging between Super Admins and Library Staffs — for library concerns and task coordination. Students and Teachers don't have access to this; direct them to the library during operating hours.</p>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 shadow-sm divide-y divide-bark-100 dark:divide-bark-800">
            @if($contacts->isEmpty())
                <p class="text-sm text-bark-400 text-center py-10">No other staff accounts to message yet.</p>
            @else
                @foreach($contacts as $contact)
                    <a href="{{ route('chat.show', $contact['user']) }}" class="flex items-center gap-3 px-5 py-4 hover:bg-bark-50 dark:hover:bg-bark-800/60 transition-colors">
                        <div class="relative flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-maroon-700 text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($contact['user']->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-bark-800 dark:text-parchment-100 truncate">{{ $contact['user']->name }}</p>
                                @if($contact['last_message'])
                                    <span class="text-[11px] text-bark-400 flex-shrink-0">{{ $contact['last_message']->created_at->diffForHumans(null, true) }}</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs text-bark-400 truncate">
                                    @if($contact['last_message'])
                                        {{ \Illuminate\Support\Str::limit($contact['last_message']->body ?? '📎 Attachment', 40) }}
                                    @endif
                                </p>
                                @if($contact['unread'] > 0)
                                    <span class="text-[10px] font-bold bg-maroon-700 text-white rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center flex-shrink-0">{{ $contact['unread'] }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</x-dynamic-component>
