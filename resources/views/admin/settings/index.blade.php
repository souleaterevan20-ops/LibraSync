@php
    $isSuperAdmin = auth()->user()->role === 'super_admin';
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="System Settings">

    <div class="max-w-4xl mx-auto space-y-6">

        @if (session('status'))
            <div class="bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if(!$isSuperAdmin)
            <div class="bg-bark-100 dark:bg-bark-800 border border-bark-200 dark:border-bark-700 text-bark-500 dark:text-bark-300 px-4 py-3 rounded-lg text-xs">
                You're viewing System Settings in read-only mode. Only Super Admin can make changes here.
            </div>
        @endif

        <!-- ============ Library Information / Contact / Operating Hours ============ -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-1">Library Information</h2>
            <p class="text-xs text-bark-400 mb-4">Shown on the About System page and used throughout the app.</p>

            @if($isSuperAdmin)
                <form method="POST" action="{{ route('admin.settings.library-info.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Library Name</label>
                        <input type="text" name="library_name" value="{{ old('library_name', $settings->library_name) }}"
                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm" required>
                        @error('library_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">{{ old('description', $settings->description) }}</textarea>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Address</label>
                            <input type="text" name="address" value="{{ old('address', $settings->address) }}"
                                class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Operating Hours</label>
                            <input type="text" name="operating_hours" value="{{ old('operating_hours', $settings->operating_hours) }}"
                                placeholder="Mon–Fri, 8:00 AM – 5:00 PM"
                                class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Contact Email</label>
                            <input type="email" name="email" value="{{ old('email', $settings->email) }}"
                                class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                            @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-600 dark:text-bark-300 mb-1">Contact Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}"
                                class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                    </div>

                    <button class="bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">Save Library Information</button>
                </form>
            @else
                <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-bark-400">Library Name</dt><dd class="text-bark-700 dark:text-bark-200">{{ $settings->library_name }}</dd></div>
                    <div><dt class="text-xs text-bark-400">Operating Hours</dt><dd class="text-bark-700 dark:text-bark-200">{{ $settings->operating_hours ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-bark-400">Address</dt><dd class="text-bark-700 dark:text-bark-200">{{ $settings->address ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-bark-400">Contact</dt><dd class="text-bark-700 dark:text-bark-200">{{ $settings->email ?: '—' }} {{ $settings->phone ? '· '.$settings->phone : '' }}</dd></div>
                </dl>
                @if($settings->description)
                    <p class="text-sm text-bark-600 dark:text-bark-300 mt-4">{{ $settings->description }}</p>
                @endif
            @endif
        </div>

        <!-- ============ Library Committee ============ -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-1">Library Committee</h2>
            <p class="text-xs text-bark-400 mb-4">Shown on the About System page. Inactive members stay saved but are hidden there.</p>

            <ul class="space-y-2 mb-4">
                @forelse($committee as $member)
                    <li class="flex items-center gap-3 bg-parchment-50 dark:bg-bark-800/60 rounded-xl border border-bark-100 dark:border-bark-800 p-3">
                        @if($member->photo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($member->photo_path) }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                        @else
                            <div class="w-9 h-9 rounded-full bg-maroon-700 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-bark-700 dark:text-bark-200">{{ $member->name }} @if(!$member->is_active)<span class="text-[10px] font-bold uppercase tracking-wide bg-bark-200 dark:bg-bark-700 text-bark-500 px-2 py-0.5 rounded-full ml-1">Hidden</span>@endif</p>
                            <p class="text-xs text-bark-400">{{ $member->position ?: '—' }}</p>
                        </div>
                        @if($isSuperAdmin)
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <form method="POST" action="{{ route('admin.settings.committee.reorder', $member) }}"><input type="hidden" name="direction" value="up">@csrf @method('PATCH')<button class="text-bark-400 hover:text-bark-700 dark:hover:text-parchment-100 p-1" title="Move up">↑</button></form>
                                <form method="POST" action="{{ route('admin.settings.committee.reorder', $member) }}"><input type="hidden" name="direction" value="down">@csrf @method('PATCH')<button class="text-bark-400 hover:text-bark-700 dark:hover:text-parchment-100 p-1" title="Move down">↓</button></form>
                                <form method="POST" action="{{ route('admin.settings.committee.toggle', $member) }}">@csrf @method('PATCH')<button class="text-xs font-medium text-amber-600 hover:text-amber-800 px-2">{{ $member->is_active ? 'Hide' : 'Show' }}</button></form>
                                <form method="POST" action="{{ route('admin.settings.committee.destroy', $member) }}" onsubmit="return confirm('Remove {{ $member->name }} from the Library Committee?');">@csrf @method('DELETE')<button class="text-xs font-medium text-rose-600 hover:text-rose-800 px-2">Remove</button></form>
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">No committee members added yet.</li>
                @endforelse
            </ul>

            @if($isSuperAdmin)
                <form method="POST" action="{{ route('admin.settings.committee.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-4 gap-2 items-end border-t border-bark-100 dark:border-bark-800 pt-4">
                    @csrf
                    <div class="sm:col-span-2"><input type="text" name="name" placeholder="Full name" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm" required></div>
                    <div><input type="text" name="position" placeholder="Position" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm"></div>
                    <div><input type="file" name="photo" accept="image/*" class="w-full text-xs"></div>
                    <button class="sm:col-span-4 bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Add Committee Member</button>
                </form>
            @endif
        </div>

        <!-- ============ Library Staff Directory ============ -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-1">Library Staff (About Page Directory)</h2>
            <p class="text-xs text-bark-400 mb-4">Public-facing "who works here" listing for the About System page. Separate from actual Library Staff user accounts in User Management.</p>

            <ul class="space-y-2 mb-4">
                @forelse($staff as $member)
                    <li class="flex items-center gap-3 bg-parchment-50 dark:bg-bark-800/60 rounded-xl border border-bark-100 dark:border-bark-800 p-3">
                        @if($member->photo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($member->photo_path) }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                        @else
                            <div class="w-9 h-9 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-bark-700 dark:text-bark-200">{{ $member->name }} @if(!$member->is_active)<span class="text-[10px] font-bold uppercase tracking-wide bg-bark-200 dark:bg-bark-700 text-bark-500 px-2 py-0.5 rounded-full ml-1">Hidden</span>@endif</p>
                            <p class="text-xs text-bark-400">{{ $member->position ?: '—' }}</p>
                        </div>
                        @if($isSuperAdmin)
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <form method="POST" action="{{ route('admin.settings.staff.reorder', $member) }}"><input type="hidden" name="direction" value="up">@csrf @method('PATCH')<button class="text-bark-400 hover:text-bark-700 dark:hover:text-parchment-100 p-1" title="Move up">↑</button></form>
                                <form method="POST" action="{{ route('admin.settings.staff.reorder', $member) }}"><input type="hidden" name="direction" value="down">@csrf @method('PATCH')<button class="text-bark-400 hover:text-bark-700 dark:hover:text-parchment-100 p-1" title="Move down">↓</button></form>
                                <form method="POST" action="{{ route('admin.settings.staff.toggle', $member) }}">@csrf @method('PATCH')<button class="text-xs font-medium text-amber-600 hover:text-amber-800 px-2">{{ $member->is_active ? 'Hide' : 'Show' }}</button></form>
                                <form method="POST" action="{{ route('admin.settings.staff.destroy', $member) }}" onsubmit="return confirm('Remove {{ $member->name }} from the Library Staff directory?');">@csrf @method('DELETE')<button class="text-xs font-medium text-rose-600 hover:text-rose-800 px-2">Remove</button></form>
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-bark-400 text-center py-4">No staff directory entries added yet.</li>
                @endforelse
            </ul>

            @if($isSuperAdmin)
                <form method="POST" action="{{ route('admin.settings.staff.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-4 gap-2 items-end border-t border-bark-100 dark:border-bark-800 pt-4">
                    @csrf
                    <div class="sm:col-span-2"><input type="text" name="name" placeholder="Full name" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm" required></div>
                    <div><input type="text" name="position" placeholder="Position" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm"></div>
                    <div><input type="file" name="photo" accept="image/*" class="w-full text-xs"></div>
                    <button class="sm:col-span-4 bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Add Staff Directory Entry</button>
                </form>
            @endif
        </div>

        <!-- ============ Notification Settings ============ -->
        <div class="bg-white dark:bg-bark-900 rounded-2xl border border-bark-200/70 dark:border-bark-800 p-6 shadow-sm">
            <h2 class="font-bold text-bark-800 dark:text-parchment-100 mb-1">Notification Settings</h2>
            <p class="text-xs text-bark-400">
                In-app notifications are always on for everyone. Email and SMS are opt-in per user —
                each person turns these on/off for themselves from their own Profile page.
                Account approval and password reset notices are always sent regardless of that
                choice, since those are account-security events. SMS requires an SMS provider to be
                configured in <code class="text-[11px]">.env</code> (SMS_PROVIDER, SMS_API_KEY) —
                until then, SMS sends are logged but not actually delivered.
            </p>
        </div>

    </div>

</x-dynamic-component>
