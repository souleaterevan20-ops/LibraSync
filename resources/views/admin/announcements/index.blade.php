<x-admin-layout title="Special Announcements">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 bg-rose-100 dark:bg-rose-900/30 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Create announcement -->
    <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-5 mb-6">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-1">Post a New Announcement</h3>
        <p class="text-xs text-bark-400 mb-4">This will appear on every user's dashboard (Super Admin, Library Staff, Student, Teacher) as soon as they log in, until they dismiss it or it expires.</p>

        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Title</label>
                <input type="text" name="title" required value="{{ old('title') }}" maxlength="150"
                    class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm" placeholder="e.g. Library closed this Friday">
            </div>

            <div>
                <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Message <span class="text-bark-400 font-normal">(optional)</span></label>
                <textarea name="message" rows="4" maxlength="3000"
                    class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm" placeholder="Full announcement text...">{{ old('message') }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                    <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Image (optional)</label>
                    <input type="file" id="announcement-image-input" name="image" accept="image/jpeg,image/png,image/webp" onchange="librasyncPreviewAnnouncementImage(this)" class="w-full text-xs text-bark-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-maroon-50 file:text-maroon-700 dark:file:bg-maroon-900/30 dark:file:text-maroon-300 file:text-xs">
                    <p class="text-[10px] text-bark-400 mt-1">JPG/PNG/WebP. Recommended: 1200&times;675px (16:9), max 5 MB.</p>
                    <div id="announcement-image-preview-wrap" class="hidden mt-2 relative">
                        <img id="announcement-image-preview" src="" alt="Preview" class="w-full h-32 object-cover rounded-lg border border-bark-200 dark:border-bark-700">
                        <button type="button" onclick="librasyncClearAnnouncementFile('image')"
                            class="absolute top-1.5 right-1.5 bg-black/60 hover:bg-black/80 text-white text-[11px] font-semibold px-2 py-1 rounded-md">
                            Remove
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Video (optional)</label>
                    <input type="file" id="announcement-video-input" name="video" accept="video/mp4,video/webm" onchange="librasyncPreviewAnnouncementVideo(this)" class="w-full text-xs text-bark-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-maroon-50 file:text-maroon-700 dark:file:bg-maroon-900/30 dark:file:text-maroon-300 file:text-xs">
                    <p class="text-[10px] text-bark-400 mt-1">MP4/WebM, 1280&times;720px recommended, max 50 MB.</p>
                    <div id="announcement-video-preview-wrap" class="hidden mt-2 relative">
                        <video id="announcement-video-preview" src="" controls class="w-full h-32 rounded-lg border border-bark-200 dark:border-bark-700 bg-black"></video>
                        <button type="button" onclick="librasyncClearAnnouncementFile('video')"
                            class="absolute top-1.5 right-1.5 bg-black/60 hover:bg-black/80 text-white text-[11px] font-semibold px-2 py-1 rounded-md">
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-xs">
                <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Priority</label>
                <select name="priority" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="important" {{ old('priority') === 'important' ? 'selected' : '' }}>Important</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
                <p class="text-[11px] text-bark-400 mt-1">Important/Urgent announcements are the only ones that will trigger SMS/email once that's enabled.</p>
            </div>

            <div class="max-w-xs">
                <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Expires at (optional)</label>
                <input type="datetime-local" name="expires_at" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                <p class="text-[11px] text-bark-400 mt-1">Leave blank to keep it visible until manually removed.</p>
            </div>

            <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">Post Announcement</button>
        </form>
    </div>

    <!-- Existing announcements -->
    <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-5">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">All Announcements</h3>

        @if($announcements->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">No announcements posted yet.</p>
        @else
            <ul class="divide-y divide-bark-100 dark:divide-bark-800">
                @foreach($announcements as $announcement)
                    <li class="py-3 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-bark-800 dark:text-parchment-100 flex items-center gap-2">
                                {{ $announcement->title }}
                                @if($announcement->priority === 'urgent')
                                    <span class="text-[10px] font-bold uppercase tracking-wide bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 px-2 py-0.5 rounded-full">Urgent</span>
                                @elseif($announcement->priority === 'important')
                                    <span class="text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 px-2 py-0.5 rounded-full">Important</span>
                                @endif
                            </p>
                            <p class="text-xs text-bark-400 mt-0.5">
                                Posted {{ $announcement->posted_at->format('M d, Y g:i A') }} by {{ $announcement->creator->name ?? 'Unknown' }}
                                @if($announcement->expires_at)
                                    &middot; Expires {{ $announcement->expires_at->format('M d, Y g:i A') }}
                                @endif
                                @if($announcement->isExpired())
                                    <span class="text-rose-500 font-semibold">&middot; Expired</span>
                                @endif
                            </p>
                            <p class="text-xs text-bark-500 dark:text-bark-400 mt-1 line-clamp-2">{{ $announcement->message }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement for everyone?');" class="flex-shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs font-semibold text-rose-700 hover:underline">Delete</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $announcements->links() }}</div>
        @endif
    </div>

    <script>
        function librasyncPreviewAnnouncementImage(input) {
            const wrap = document.getElementById('announcement-image-preview-wrap');
            const preview = document.getElementById('announcement-image-preview');
            if (input.files && input.files[0]) {
                preview.src = URL.createObjectURL(input.files[0]);
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
                preview.src = '';
            }
        }

        function librasyncPreviewAnnouncementVideo(input) {
            const wrap = document.getElementById('announcement-video-preview-wrap');
            const preview = document.getElementById('announcement-video-preview');
            if (input.files && input.files[0]) {
                preview.src = URL.createObjectURL(input.files[0]);
                wrap.classList.remove('hidden');
            } else {
                wrap.classList.add('hidden');
                preview.src = '';
            }
        }

        function librasyncClearAnnouncementFile(type) {
            const input = document.getElementById('announcement-' + type + '-input');
            const wrap = document.getElementById('announcement-' + type + '-preview-wrap');
            const preview = document.getElementById('announcement-' + type + '-preview');
            input.value = '';
            preview.src = '';
            wrap.classList.add('hidden');
        }
    </script>
</x-admin-layout>
