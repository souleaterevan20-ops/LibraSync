<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistantSession;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentAssistantController extends Controller
{
    public function index()
    {
        $assistants = User::where('role', 'student_assistant')->orderBy('name')->get()->map(function ($assistant) {
            $lastLogin = \App\Models\AuditLog::where('subject_type', User::class)
                ->where('subject_id', $assistant->id)
                ->where('action', 'Logged In')
                ->latest()->first();
            $lastLogout = \App\Models\AuditLog::where('subject_type', User::class)
                ->where('subject_id', $assistant->id)
                ->where('action', 'Logged Out')
                ->latest()->first();
            $lastTransaction = \App\Models\BorrowRecord::where('checked_out_by', $assistant->id)
                ->orWhere('checked_in_by', $assistant->id)
                ->latest('updated_at')->first();

            $assistant->last_login_at = $lastLogin?->created_at;
            $assistant->last_logout_at = $lastLogout?->created_at;
            $assistant->last_transaction_at = $lastTransaction?->updated_at;
            $assistant->last_transaction_label = $lastTransaction ? ($lastTransaction->checked_in_by === $assistant->id ? 'Checked in a return' : 'Checked out a loan') : null;

            return $assistant;
        });

        return view('admin.assistants.index', compact('assistants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $assistant = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student_assistant',
            'is_verified' => true,
            'is_active' => true,
        ]);

        AuditLogger::log('Created Library Staff', $assistant);

        return redirect()->back()->with('status', "Library Staff account created for {$assistant->name}.");
    }

    public function update(Request $request, User $assistant)
    {
        abort_unless($assistant->role === 'student_assistant', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$assistant->id],
        ]);

        $assistant->update($validated);

        AuditLogger::log('Edited Library Staff', $assistant);

        return redirect()->back()->with('status', "Library Staff account for {$assistant->name} updated.");
    }

    public function toggleActive(User $assistant)
    {
        abort_unless($assistant->role === 'student_assistant', 404);

        $assistant->update(['is_active' => !$assistant->is_active]);

        AuditLogger::log($assistant->is_active ? 'Enabled Library Staff' : 'Deactivated Library Staff', $assistant);

        return redirect()->back()->with('status', $assistant->is_active ? "{$assistant->name} has been enabled." : "{$assistant->name} has been deactivated.");
    }

    public function destroy(User $assistant)
    {
        abort_unless($assistant->role === 'student_assistant', 404);

        $name = $assistant->name;
        AuditLogger::log('Deleted Library Staff', $assistant, "Deleted Library Staff account for {$name}");
        $assistant->delete();

        return redirect()->back()->with('status', "Library Staff account for {$name} has been deleted.");
    }

    public function resetPassword(Request $request, User $assistant)
    {
        abort_unless($assistant->role === 'student_assistant', 404);

        $validated = $request->validate(['password' => ['required', 'string', 'min:8']]);

        $assistant->update(['password' => Hash::make($validated['password'])]);

        AuditLogger::log('Reset Password', $assistant);
        Notifier::send($assistant, 'account', 'Password reset', 'Your password was reset by the Super Admin.');

        return redirect()->back()->with('status', "Password for {$assistant->name} has been reset.");
    }

    public function activity(User $assistant)
    {
        abort_unless($assistant->role === 'student_assistant', 404);

        $logs = AuditLog::where('actor_id', $assistant->id)->latest()->paginate(30);
        $sessions = AssistantSession::where('user_id', $assistant->id)->latest('login_at')->take(30)->get();

        return view('admin.assistants.activity', compact('assistant', 'logs', 'sessions'));
    }
}
