@if($announcements->isNotEmpty())
    <div class="space-y-3 mb-6">
        @foreach($announcements as $announcement)
            <a href="{{ route('announcements.show', $announcement) }}"
               @class([
                   'block rounded-2xl p-5 border transition-colors',
                   'bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/20 dark:to-rose-900/10 border-rose-200 dark:border-rose-800 hover:border-rose-300 dark:hover:border-rose-700' => $announcement->priority === 'urgent',
                   'bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-900/10 border-amber-200 dark:border-amber-800 hover:border-amber-300 dark:hover:border-amber-700' => $announcement->priority !== 'urgent',
               ])>

                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 {{ $announcement->priority === 'urgent' ? 'text-rose-500' : 'text-amber-500' }} mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.875a2.625 2.625 0 0 1 0 5.25H12M8.25 9.75H6M8.25 9.75V6.375c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875V9.75M4.5 15h15" /></svg>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold {{ $announcement->priority === 'urgent' ? 'text-rose-900 dark:text-rose-200' : 'text-amber-900 dark:text-amber-200' }} flex items-center gap-2">
                            {{ $announcement->title }}
                            @if($announcement->priority === 'urgent')
                                <span class="text-[10px] font-bold uppercase tracking-wide bg-rose-600 text-white px-2 py-0.5 rounded-full">Urgent</span>
                            @elseif($announcement->priority === 'important')
                                <span class="text-[10px] font-bold uppercase tracking-wide bg-amber-600 text-white px-2 py-0.5 rounded-full">Important</span>
                            @endif
                        </p>
                        <p class="text-[11px] {{ $announcement->priority === 'urgent' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">Posted {{ $announcement->posted_at->format('M d, Y g:i A') }} by {{ $announcement->creator->name ?? 'Super Admin' }}</p>
                        @if($announcement->message)
                            <p class="text-sm {{ $announcement->priority === 'urgent' ? 'text-rose-800 dark:text-rose-100' : 'text-amber-800 dark:text-amber-100' }} mt-2 line-clamp-2">{{ $announcement->message }}</p>
                        @endif
                        <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $announcement->priority === 'urgent' ? 'text-rose-700 dark:text-rose-300' : 'text-amber-700 dark:text-amber-300' }} mt-2">
                            View full announcement
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </span>
                    </div>
                    @if($announcement->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($announcement->image_path) }}" alt="" loading="lazy"
                             onerror="this.remove()"
                             class="w-16 h-16 rounded-lg object-cover border {{ $announcement->priority === 'urgent' ? 'border-rose-200 dark:border-rose-800' : 'border-amber-200 dark:border-amber-800' }} flex-shrink-0">
                    @elseif($announcement->video_path)
                        <div class="w-16 h-16 rounded-lg {{ $announcement->priority === 'urgent' ? 'bg-rose-200 dark:bg-rose-900/40' : 'bg-amber-200 dark:bg-amber-900/40' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 {{ $announcement->priority === 'urgent' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5 19.5 8.25v7.5l-3.75-2.25M4.5 18.75h9a1.5 1.5 0 0 0 1.5-1.5v-7.5a1.5 1.5 0 0 0-1.5-1.5h-9a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
