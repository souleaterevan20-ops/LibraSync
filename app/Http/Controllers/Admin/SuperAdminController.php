<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'super_admin')->orderBy('name')->get();

        return view('admin.super-admins.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
            'is_verified' => true,
            'is_active' => true,
        ]);

        AuditLogger::log('Created Super Admin', $admin);

        return redirect()->back()->with('status', "Super Admin account created for {$admin->name}.");
    }

    public function update(Request $request, User $admin)
    {
        abort_unless($admin->role === 'super_admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$admin->id],
        ]);

        $admin->update($validated);

        AuditLogger::log('Edited Super Admin', $admin);

        return redirect()->back()->with('status', "Super Admin account for {$admin->name} updated.");
    }

    public function toggleActive(User $admin)
    {
        abort_unless($admin->role === 'super_admin', 404);
        abort_if($admin->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        $admin->update(['is_active' => !$admin->is_active]);

        AuditLogger::log($admin->is_active ? 'Enabled Super Admin' : 'Deactivated Super Admin', $admin);

        return redirect()->back()->with('status', $admin->is_active ? "{$admin->name} has been enabled." : "{$admin->name} has been deactivated.");
    }

    public function destroy(User $admin)
    {
        abort_unless($admin->role === 'super_admin', 404);
        abort_if($admin->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = $admin->name;
        AuditLogger::log('Deleted Super Admin', $admin, "Deleted Super Admin account for {$name}");
        $admin->delete();

        return redirect()->back()->with('status', "Super Admin account for {$name} has been deleted.");
    }
}
