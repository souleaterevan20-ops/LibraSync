<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCommitteeMember;
use App\Models\LibrarySetting;
use App\Models\LibraryStaffDirectory;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    /**
     * Spec #34: sections are Library Information, Library Committee,
     * Library Staff, Contact Information, Operating Hours, Notification
     * Settings. Contact Information and Operating Hours live on the same
     * settings row as Library Information, so they're one form.
     *
     * Both Super Admin and Library Staff can reach this page (it's in both
     * sidebars), but only Super Admin gets the edit forms — the view checks
     * auth()->user()->role and hides forms/action buttons for Library Staff,
     * matching the read-only oversight pattern already used elsewhere in
     * this app (Super Admin's view-only Returns/Borrow Requests).
     */
    public function index()
    {
        $settings = LibrarySetting::current();
        $committee = LibraryCommitteeMember::ordered()->get();
        $staff = LibraryStaffDirectory::ordered()->get();

        return view('admin.settings.index', compact('settings', 'committee', 'staff'));
    }

    public function updateLibraryInfo(Request $request)
    {
        $validated = $request->validate([
            'library_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = LibrarySetting::current();
        $settings->update($validated);

        AuditLogger::log('Updated Library Information', $settings, 'Library information, contact details, and operating hours were updated.');

        return redirect()->route('admin.settings.index')->with('status', 'Library information updated. The About System page reflects this immediately.');
    }

    // ---- Library Committee ------------------------------------------------

    public function storeCommitteeMember(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $member = LibraryCommitteeMember::create([
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
            'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('committee', 'public') : null,
            'display_order' => (int) LibraryCommitteeMember::max('display_order') + 1,
            'is_active' => true,
        ]);

        AuditLogger::log('Added Committee Member', $member, "Added \"{$member->name}\" to the Library Committee.");

        return redirect()->route('admin.settings.index')->with('status', "\"{$member->name}\" added to the Library Committee.");
    }

    public function updateCommitteeMember(Request $request, LibraryCommitteeMember $committeeMember)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $committeeMember->name = $validated['name'];
        $committeeMember->position = $validated['position'] ?? null;

        if ($request->hasFile('photo')) {
            $committeeMember->photo_path = $request->file('photo')->store('committee', 'public');
        }

        $committeeMember->save();

        AuditLogger::log('Updated Committee Member', $committeeMember, "Updated \"{$committeeMember->name}\".");

        return redirect()->route('admin.settings.index')->with('status', "\"{$committeeMember->name}\" updated.");
    }

    public function toggleCommitteeMember(LibraryCommitteeMember $committeeMember)
    {
        $committeeMember->is_active = ! $committeeMember->is_active;
        $committeeMember->save();

        AuditLogger::log(
            $committeeMember->is_active ? 'Activated Committee Member' : 'Deactivated Committee Member',
            $committeeMember,
            "\"{$committeeMember->name}\" is now ".($committeeMember->is_active ? 'visible' : 'hidden')." on the About System page."
        );

        return redirect()->route('admin.settings.index')->with('status', "\"{$committeeMember->name}\" is now ".($committeeMember->is_active ? 'active.' : 'inactive.'));
    }

    public function reorderCommitteeMember(Request $request, LibraryCommitteeMember $committeeMember)
    {
        $validated = $request->validate(['direction' => ['required', 'in:up,down']]);

        $swapWith = $validated['direction'] === 'up'
            ? LibraryCommitteeMember::where('display_order', '<', $committeeMember->display_order)->orderByDesc('display_order')->first()
            : LibraryCommitteeMember::where('display_order', '>', $committeeMember->display_order)->orderBy('display_order')->first();

        if ($swapWith) {
            [$a, $b] = [$committeeMember->display_order, $swapWith->display_order];
            $committeeMember->update(['display_order' => $b]);
            $swapWith->update(['display_order' => $a]);
        }

        return redirect()->route('admin.settings.index');
    }

    public function destroyCommitteeMember(LibraryCommitteeMember $committeeMember)
    {
        $name = $committeeMember->name;
        AuditLogger::log('Removed Committee Member', $committeeMember, "Removed \"{$name}\" from the Library Committee.");
        $committeeMember->delete();

        return redirect()->route('admin.settings.index')->with('status', "\"{$name}\" removed.");
    }

    // ---- Library Staff Directory -------------------------------------------

    public function storeStaffMember(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $member = LibraryStaffDirectory::create([
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
            'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('staff-directory', 'public') : null,
            'display_order' => (int) LibraryStaffDirectory::max('display_order') + 1,
            'is_active' => true,
        ]);

        AuditLogger::log('Added Staff Directory Entry', $member, "Added \"{$member->name}\" to the Library Staff directory.");

        return redirect()->route('admin.settings.index')->with('status', "\"{$member->name}\" added to the Library Staff directory.");
    }

    public function updateStaffMember(Request $request, LibraryStaffDirectory $staffMember)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $staffMember->name = $validated['name'];
        $staffMember->position = $validated['position'] ?? null;

        if ($request->hasFile('photo')) {
            $staffMember->photo_path = $request->file('photo')->store('staff-directory', 'public');
        }

        $staffMember->save();

        AuditLogger::log('Updated Staff Directory Entry', $staffMember, "Updated \"{$staffMember->name}\".");

        return redirect()->route('admin.settings.index')->with('status', "\"{$staffMember->name}\" updated.");
    }

    public function toggleStaffMember(LibraryStaffDirectory $staffMember)
    {
        $staffMember->is_active = ! $staffMember->is_active;
        $staffMember->save();

        AuditLogger::log(
            $staffMember->is_active ? 'Activated Staff Directory Entry' : 'Deactivated Staff Directory Entry',
            $staffMember,
            "\"{$staffMember->name}\" is now ".($staffMember->is_active ? 'visible' : 'hidden')." on the About System page."
        );

        return redirect()->route('admin.settings.index')->with('status', "\"{$staffMember->name}\" is now ".($staffMember->is_active ? 'active.' : 'inactive.'));
    }

    public function reorderStaffMember(Request $request, LibraryStaffDirectory $staffMember)
    {
        $validated = $request->validate(['direction' => ['required', 'in:up,down']]);

        $swapWith = $validated['direction'] === 'up'
            ? LibraryStaffDirectory::where('display_order', '<', $staffMember->display_order)->orderByDesc('display_order')->first()
            : LibraryStaffDirectory::where('display_order', '>', $staffMember->display_order)->orderBy('display_order')->first();

        if ($swapWith) {
            [$a, $b] = [$staffMember->display_order, $swapWith->display_order];
            $staffMember->update(['display_order' => $b]);
            $swapWith->update(['display_order' => $a]);
        }

        return redirect()->route('admin.settings.index');
    }

    public function destroyStaffMember(LibraryStaffDirectory $staffMember)
    {
        $name = $staffMember->name;
        AuditLogger::log('Removed Staff Directory Entry', $staffMember, "Removed \"{$name}\" from the Library Staff directory.");
        $staffMember->delete();

        return redirect()->route('admin.settings.index')->with('status', "\"{$name}\" removed.");
    }
}
