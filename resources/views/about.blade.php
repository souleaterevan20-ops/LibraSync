@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="About System">

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Hero -->
        <div class="bg-gradient-to-br from-maroon-800 to-maroon-900 rounded-2xl p-8 text-center text-white shadow-sm">
            <div class="w-16 h-16 mx-auto rounded-full bg-white/10 border border-white/20 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold">{{ $settings->library_name }}</h1>
            <p class="text-maroon-200 text-sm mt-1">{{ $settings->description ?: 'Hybrid Online/Offline E-Library Management System' }}</p>
            <p class="text-maroon-300 text-xs mt-3">Version 1.0</p>
        </div>

        <!-- System Description -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-3">System Description</h2>
            <p class="text-sm text-bark-600 dark:text-bark-300 leading-relaxed">
                {{ $settings->description ?: ($settings->library_name.' is an advanced hybrid online/offline E-Library Management System providing centralized management of library resources, borrowing transactions, inventory, user accounts, notifications, reports, leaderboard rankings, and archival records through a multi-role platform consisting of Super Admin, Library Staff, Student, and Teacher users.') }}
            </p>
            @if($settings->address || $settings->email || $settings->phone || $settings->operating_hours)
                <dl class="grid sm:grid-cols-2 gap-3 mt-4 text-xs text-bark-500 dark:text-bark-400">
                    @if($settings->address)<div><dt class="font-semibold">Address</dt><dd>{{ $settings->address }}</dd></div>@endif
                    @if($settings->operating_hours)<div><dt class="font-semibold">Operating Hours</dt><dd>{{ $settings->operating_hours }}</dd></div>@endif
                    @if($settings->email)<div><dt class="font-semibold">Email</dt><dd>{{ $settings->email }}</dd></div>@endif
                    @if($settings->phone)<div><dt class="font-semibold">Phone</dt><dd>{{ $settings->phone }}</dd></div>@endif
                </dl>
            @endif
        </div>

        <!-- Library Committee & Staff -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-4">Library Committee</h2>
            @if($committee->isEmpty())
                <p class="text-sm text-bark-400 mb-5">No committee members have been added yet.</p>
            @else
                <ul class="grid sm:grid-cols-3 gap-3 mb-5">
                    @foreach($committee as $member)
                        <li class="flex items-center gap-3 bg-parchment-50 dark:bg-bark-800/60 rounded-xl border border-bark-100 dark:border-bark-800 p-3">
                            @if($member->photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($member->photo_path) }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                            @else
                                <div class="w-9 h-9 rounded-full bg-maroon-700 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-bark-700 dark:text-bark-200">{{ $member->name }}</p>
                                <p class="text-xs text-bark-400">{{ $member->position }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="text-[11px] font-semibold uppercase tracking-wide text-maroon-500 mb-2">Library Staffs</p>
            @if($staff->isEmpty())
                <p class="text-sm text-bark-400">No staff directory entries have been added yet.</p>
            @else
                <ul class="grid sm:grid-cols-2 gap-3">
                    @foreach($staff as $member)
                        <li class="flex items-center gap-3 bg-parchment-50 dark:bg-bark-800/60 rounded-xl border border-bark-100 dark:border-bark-800 p-3">
                            @if($member->photo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($member->photo_path) }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                            @else
                                <div class="w-9 h-9 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <span class="text-sm font-medium text-bark-700 dark:text-bark-200">{{ $member->name }}</span>
                                @if($member->position)<p class="text-xs text-bark-400">{{ $member->position }}</p>@endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- People Behind the System -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-4">People Behind the System</h2>

            <p class="text-[11px] font-semibold uppercase tracking-wide text-maroon-500 mb-2">Researchers / Developers</p>
            <ul class="grid sm:grid-cols-3 gap-3 mb-5">
                @foreach(['Junjie A. Adlawan', 'Mitch S. Lombreno', 'Rovic Mar Vincent P. Rivera'] as $name)
                    <li class="flex items-center gap-3 bg-parchment-50 dark:bg-bark-800/60 rounded-xl border border-bark-100 dark:border-bark-800 p-3">
                        <div class="w-9 h-9 rounded-full bg-maroon-700 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-bark-700 dark:text-bark-200">{{ $name }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="text-xs text-bark-400 mb-5">BSIT-4A · Professional Academy of the Philippines</p>

            <p class="text-[11px] font-semibold uppercase tracking-wide text-maroon-500 mb-2">Academic Adviser</p>
            <div class="flex items-center gap-3 bg-parchment-50 dark:bg-bark-800/60 rounded-xl border border-bark-100 dark:border-bark-800 p-3 max-w-sm">
                <div class="w-9 h-9 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">B</div>
                <div>
                    <p class="text-sm font-medium text-bark-700 dark:text-bark-200">Mr. Benjie S. Polo</p>
                    <p class="text-xs text-bark-400">Under the guidance and supervision of the faculty adviser</p>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-bark-400 pb-4">Version 1.0 &middot; &copy; {{ date('Y') }} Professional Academy of the Philippines</p>
    </div>

</x-dynamic-component>
