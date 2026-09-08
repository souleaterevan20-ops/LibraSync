@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="Announcement">

    <div class="max-w-2xl mx-auto">
        <button onclick="history.back()" class="inline-flex items-center gap-1.5 text-sm font-medium text-bark-500 dark:text-bark-400 hover:text-bark-800 dark:hover:text-parchment-100 mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            Back
        </button>

        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-extrabold text-bark-800 dark:text-parchment-100">{{ $announcement->title }}</h1>
                    <p class="text-xs text-bark-400 mt-1">Posted {{ $announcement->posted_at->format('M d, Y g:i A') }} by {{ $announcement->creator->name ?? 'Super Admin' }}</p>
                    @if($announcement->expires_at)
                        <p class="text-xs text-amber-500 mt-1">Visible until {{ $announcement->expires_at->format('M d, Y g:i A') }}</p>
                    @endif
                </div>

                @if(auth()->user()->role === 'super_admin')
                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement for everyone?');">
                        @csrf @method('DELETE')
                        <button class="text-xs font-semibold text-rose-700 hover:underline whitespace-nowrap">Delete</button>
                    </form>
                @endif
            </div>

            @if($announcement->message)
                <p class="text-sm text-bark-700 dark:text-bark-200 mt-4 whitespace-pre-wrap leading-relaxed">{{ $announcement->message }}</p>
            @endif

            @if($announcement->image_path)
                <button type="button" onclick="document.getElementById('announcement-lightbox').classList.remove('hidden')" class="mt-5 block w-full">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}"
                         loading="lazy" onerror="this.closest('button').classList.add('hidden')"
                         class="rounded-xl w-full max-h-[420px] object-cover border border-bark-200 dark:border-bark-800 hover:opacity-90 transition-opacity cursor-zoom-in">
                </button>

                <div id="announcement-lightbox" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4" onclick="this.classList.add('hidden')">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}" class="max-w-full max-h-full rounded-lg">
                </div>
            @endif

            @if($announcement->video_path)
                <video src="{{ \Illuminate\Support\Facades\Storage::url($announcement->video_path) }}" controls preload="metadata" class="mt-5 rounded-xl w-full border border-bark-200 dark:border-bark-800"></video>
            @endif
        </div>
    </div>
</x-dynamic-component>
