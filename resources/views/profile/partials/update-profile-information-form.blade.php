<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-parchment-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-bark-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-maroon-100 dark:bg-maroon-900/40 flex items-center justify-center text-maroon-700 dark:text-amber-300 font-bold text-xl overflow-hidden shrink-0">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" class="w-full h-full object-cover" alt="{{ $user->name }}">
                @else
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1">
                <x-input-label for="avatar" :value="__('Profile Picture')" />
                <input id="avatar" name="avatar" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 dark:text-bark-300">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-bark-300">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-maroon-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="contact_number" :value="__('Contact Number')" />
            <x-text-input id="contact_number" name="contact_number" type="text" class="mt-1 block w-full" :value="old('contact_number', $user->contact_number)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('contact_number')" />
        </div>

        <div>
            <x-input-label :value="__('Notification Preferences')" />
            <p class="text-xs text-bark-400 dark:text-bark-500 mt-1 mb-2">In-app notifications are always on. Choose which other channels you'd also like to receive important updates on. Account approval and password reset notices are always sent regardless of these settings.</p>
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm text-bark-700 dark:text-bark-200">
                    <input type="checkbox" name="email_notifications" value="1" {{ old('email_notifications', $user->email_notifications) ? 'checked' : '' }}
                        class="rounded border-bark-300 dark:border-bark-700 text-maroon-700 focus:ring-maroon-500">
                    Email notifications
                </label>
                <label class="flex items-center gap-2 text-sm text-bark-700 dark:text-bark-200">
                    <input type="checkbox" name="sms_notifications" value="1" {{ old('sms_notifications', $user->sms_notifications) ? 'checked' : '' }}
                        class="rounded border-bark-300 dark:border-bark-700 text-maroon-700 focus:ring-maroon-500">
                    SMS notifications (uses your Contact Number above)
                </label>
            </div>
        </div>

        @if(in_array($user->role, ['student', 'teacher']))
            <div>
                <x-input-label for="favorite_genre" :value="__('Favorite Genre')" />
                <x-text-input id="favorite_genre" name="favorite_genre" type="text" class="mt-1 block w-full" :value="old('favorite_genre', $user->favorite_genre)" />
                <x-input-error class="mt-2" :messages="$errors->get('favorite_genre')" />
            </div>
        @endif

        <div>
            <x-input-label for="bio" :value="__('Biography')" />
            <textarea id="bio" name="bio" rows="3" class="mt-1 block w-full rounded-md border-bark-300 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 shadow-sm focus:border-maroon-500 focus:ring-maroon-500 text-sm">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-bark-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
