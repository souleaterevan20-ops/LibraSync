@foreach($messages as $message)
    @php $mine = $message->sender_id === auth()->id(); @endphp
    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }} mb-3">
        <div class="max-w-[75%] {{ $mine ? 'bg-maroon-700 text-white' : 'bg-bark-100 dark:bg-bark-800 text-bark-800 dark:text-parchment-100' }} rounded-2xl px-4 py-2.5">
            @if($message->body)
                <p class="text-sm whitespace-pre-wrap break-words">{{ $message->body }}</p>
            @endif

            @if($message->attachment_path)
                <div class="mt-2">
                    @if($message->isImage())
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($message->attachment_path) }}" target="_blank">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($message->attachment_path) }}" alt="{{ $message->attachment_name }}" class="rounded-lg max-h-48 border {{ $mine ? 'border-white/20' : 'border-bark-200 dark:border-bark-700' }}">
                        </a>
                    @else
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($message->attachment_path) }}" target="_blank"
                           class="flex items-center gap-2 text-xs font-medium underline {{ $mine ? 'text-white' : 'text-maroon-700 dark:text-maroon-300' }}">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01" /></svg>
                            {{ $message->attachment_name }}
                        </a>
                    @endif
                </div>
            @endif

            <p class="text-[10px] mt-1 {{ $mine ? 'text-maroon-100' : 'text-bark-400' }}">
                {{ $message->created_at->format('g:i A') }}
                @if($mine)
                    &middot; {{ $message->is_read ? 'Seen' : 'Sent' }}
                @endif
            </p>
        </div>
    </div>
@endforeach
