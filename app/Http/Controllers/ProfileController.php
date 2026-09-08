<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {

 $request->user()->fill($request->validated());

    $request->user()->email_notifications = $request->boolean('email_notifications');
    $request->user()->sms_notifications = $request->boolean('sms_notifications');

        $validated = $request->validated();
        unset($validated['avatar']);
        unset($validated['email_notifications'], $validated['sms_notifications']);

        $request->user()->fill($validated);
        $request->user()->email_notifications = $request->boolean('email_notifications');
        $request->user()->sms_notifications = $request->boolean('sms_notifications');

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $request->user()->avatar = $path;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Available Quick Action shortcuts a Super Admin can choose from for their dashboard.
     */
    public static function availableQuickActions(): array
    {
        return [
            'add-book' => 'Add New Book',
            'add-announcement' => 'Add Announcement',
            'archive' => 'Archive',
            'all-users' => 'All Users',
            'student-assistants' => 'Library Staffs',
            'inventory' => 'Inventory / Stock',
            'reports' => 'Reports & Logs',
            'backup' => 'Backup / Restore',
            'leaderboard' => 'Leaderboard',
        ];
    }

    /**
     * Route name + icon path for each Quick Action, used to render the dashboard panel.
     */
    public static function quickActionMeta(): array
    {
        return [
            'add-book' => ['route' => 'admin.books.index', 'icon' => 'M12 4.5v15m7.5-7.5h-15'],
            'add-announcement' => ['route' => 'admin.announcements.index', 'icon' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09'],
            'archive' => ['route' => 'admin.archive.index', 'icon' => 'm20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6-3h4.5M3.75 7.5h16.5'],
            'all-users' => ['route' => 'admin.users.index', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z'],
            'student-assistants' => ['route' => 'admin.assistants.index', 'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
            'inventory' => ['route' => 'admin.inventory.index', 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z'],
            'reports' => ['route' => 'reports.index', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75'],
            'backup' => ['route' => 'admin.backup.index', 'icon' => 'M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177'],
            'leaderboard' => ['route' => 'leaderboard.index', 'icon' => 'M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3'],
        ];
    }

    /**
     * Save which Quick Action shortcuts a Super Admin wants on their dashboard.
     */
    public function updateQuickActions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quick_actions' => ['nullable', 'array', 'max:6'],
            'quick_actions.*' => ['string', 'in:' . implode(',', array_keys(self::availableQuickActions()))],
        ]);

        $request->user()->update(['quick_actions' => $validated['quick_actions'] ?? []]);

        return Redirect::route('profile.edit')->with('status', 'quick-actions-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
