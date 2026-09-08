@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="My Profile">

    <div class="space-y-6 max-w-3xl">
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        @if(auth()->user()->role === 'super_admin')
            <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 sm:p-6">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-bark-900 dark:text-parchment-100">Dashboard Quick Actions</h2>
                    <p class="mt-1 text-sm text-bark-500 dark:text-bark-400">Choose up to 6 shortcuts to show on your dashboard's Quick Actions panel.</p>

                    @if (session('status') === 'quick-actions-updated')
                        <p class="text-sm text-emerald-600 mt-2">Quick Actions updated.</p>
                    @endif

                    <form method="POST" action="{{ route('profile.quick-actions') }}" class="mt-4">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-2 gap-2">
                            @php $selected = auth()->user()->quick_actions ?? ['add-book', 'add-announcement', 'archive']; @endphp
                            @foreach(\App\Http\Controllers\ProfileController::availableQuickActions() as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-bark-700 dark:text-bark-200">
                                    <input type="checkbox" name="quick_actions[]" value="{{ $key }}" @checked(in_array($key, $selected)) class="rounded border-bark-300">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="mt-4 bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">Save Quick Actions</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 sm:p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-dynamic-component>
