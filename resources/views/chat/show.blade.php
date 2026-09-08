@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Chat with {{ $contact->name }}">

    @if (session('error'))
        <div class="mb-4 bg-rose-100 dark:bg-rose-900/30 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="max-w-3xl mx-auto bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 shadow-sm flex flex-col" style="height: calc(100vh - 180px);">

        <!-- Header -->
        <div class="flex items-center gap-3 px-5 py-3.5 border-b border-bark-100 dark:border-bark-800">
            <a href="{{ route('chat.index') }}" class="text-bark-400 hover:text-bark-700 dark:hover:text-parchment-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="w-9 h-9 rounded-full bg-maroon-700 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($contact->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-sm font-semibold text-bark-800 dark:text-parchment-100">{{ $contact->name }}</p>
            </div>
        </div>

        <!-- Messages -->
        <div id="message-list" class="flex-1 overflow-y-auto px-5 py-4" data-poll-url="{{ route('chat.poll', $contact) }}">
            @if($messages->isEmpty())
                <p class="text-sm text-bark-400 text-center py-10">No messages yet. Say hello!</p>
            @else
                @include('chat.partials.messages')
            @endif
        </div>

        <!-- Composer -->
        <form method="POST" action="{{ route('chat.store', $contact) }}" enctype="multipart/form-data" class="border-t border-bark-100 dark:border-bark-800 p-3 flex items-end gap-2">
            @csrf
            <label class="cursor-pointer text-bark-400 hover:text-maroon-700 dark:hover:text-maroon-300 p-2" title="Attach an image or file (PDF, Word, Excel)">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01" /></svg>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx" class="hidden" onchange="this.form.querySelector('#file-name').textContent = this.files[0]?.name ?? ''">
            </label>
            <div class="flex-1">
                <textarea name="body" rows="1" placeholder="Type a message…" class="w-full rounded-xl border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm resize-none" onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault(); this.form.submit();}"></textarea>
                <p id="file-name" class="text-[11px] text-bark-400 mt-0.5"></p>
            </div>
            <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white rounded-xl px-4 py-2.5 text-sm font-semibold flex-shrink-0">Send</button>
        </form>
    </div>

    <script>
        (function () {
            const list = document.getElementById('message-list');
            const pollUrl = list.dataset.pollUrl;

            function scrollToBottom() {
                list.scrollTop = list.scrollHeight;
            }
            scrollToBottom();

            // Lightweight auto-refresh — polls every 4 seconds for new messages
            // and read/seen updates. Not true real-time (no websockets), but
            // keeps the thread current without a manual page reload.
            setInterval(function () {
                fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (res) { return res.text(); })
                    .then(function (html) {
                        const wasAtBottom = list.scrollTop + list.clientHeight >= list.scrollHeight - 40;
                        list.innerHTML = html;
                        if (wasAtBottom) scrollToBottom();
                    })
                    .catch(function () { /* silent — next poll will retry */ });
            }, 4000);
        })();
    </script>
</x-dynamic-component>
