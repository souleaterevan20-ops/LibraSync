<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssistantSession;
use App\Models\AuditLog;
use App\Models\BorrowRecord;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display the list of registrations awaiting a decision (spec #77).
     * Only genuinely pending registrations show here — once a registration
     * is rejected it moves to rejectedUsers() instead, so this list never
     * mixes "waiting for a decision" with "already decided".
     */
    public function pendingUsers()
    {
        $pendingUsers = User::where('is_verified', false)
            ->whereNull('rejected_at')
            ->latest()
            ->get();

        return view('admin.pending-users', ['pendingUsers' => $pendingUsers, 'view' => 'pending']);
    }

    /**
     * Registration history for rejected sign-ups (spec #79). Kept for
     * administrative reference — the underlying account record is never
     * deleted just because it was rejected.
     */
    public function rejectedUsers()
    {
        $pendingUsers = User::whereNotNull('rejected_at')
            ->latest('rejected_at')
            ->get();

        return view('admin.pending-users', ['pendingUsers' => $pendingUsers, 'view' => 'rejected']);
    }

    /**
     * Approve and verify a pending user account (spec #78).
     */
    public function approveUser(User $user)
    {
        $user->update([
            'is_verified' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLogger::log('Approved Registration', $user);
        Notifier::send($user, 'registration', 'Account approved', 'Your account has been approved. You can now log in to LibraSync.', null, null, external: true);

        return redirect()->back()->with('status', "Account for {$user->name} has been successfully verified!");
    }

    /**
     * Reject a pending registration (spec #79). The account record and its
     * history are preserved — it is marked REJECTED, not deleted, so it
     * stays visible in Registration History and can never be confused with
     * a soft-deleted account. A reason is required and audited.
     */
    public function rejectUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'reason.required' => 'Please provide a reason for rejecting this registration.',
        ]);

        $user->update([
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'],
        ]);

        AuditLogger::log('Rejected Registration', $user, "Reason: {$validated['reason']}");
        Notifier::send($user, 'registration', 'Registration rejected', "Your registration was rejected: {$validated['reason']}");

        return redirect()->back()->with('status', "Registration for {$user->name} has been rejected.");
    }

    /**
     * "Total Users" — full directory of students & teachers, with search/filter/pagination (spec #13, #16).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');
        $departmentFilter = $request->input('department');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Viewing the "Deleted" status shows soft-deleted accounts instead of active ones,
        // so Super Admin can restore them from the same page (spec #12, #16).
        $showingDeleted = $statusFilter === 'deleted';

        $query = User::whereIn('role', ['student', 'teacher']);
        $query = $showingDeleted ? $query->onlyTrashed() : $query;

        $query->when($search, function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhere('school_or_employee_id', 'like', "%{$search}%");
            });
        });

        $query->when($roleFilter, fn ($q) => $q->where('role', $roleFilter));
        $query->when($departmentFilter, fn ($q) => $q->where('department', 'like', "%{$departmentFilter}%"));

        // Spec #77/#80: registrations that are still pending (or were
        // rejected) belong in Pending Approvals / Registration History, not
        // mixed into the normal roster. So unless the Super Admin/Library
        // Staff explicitly asks to see them via the status filter, the
        // default view — and every other status filter — excludes both.
        if (! $showingDeleted && ! in_array($statusFilter, ['pending', 'unverified', 'rejected'], true)) {
            $query->where('is_verified', true)->whereNull('rejected_at');
        }

        if (! $showingDeleted && $statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif (! $showingDeleted && $statusFilter === 'disabled') {
            $query->where('is_active', false);
        } elseif (! $showingDeleted && in_array($statusFilter, ['pending', 'unverified'], true)) {
            $query->where('is_verified', false)->whereNull('rejected_at');
        } elseif (! $showingDeleted && $statusFilter === 'rejected') {
            $query->whereNotNull('rejected_at');
        }

        $query->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom));
        $query->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo));

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        $departments = User::whereIn('role', ['student', 'teacher'])
            ->whereNotNull('department')->distinct()->pluck('department');

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'departments' => $departments,
            'showingDeleted' => $showingDeleted,
        ]);
    }

    /**
     * Full user profile (spec #8): identity, status, borrowing summary, penalty summary, activity.
     */
    public function show(User $user)
    {
        $user->load(['borrowRecords.book', 'penaltyPayments' => fn ($q) => $q->latest()->take(10)]);

        $currentlyBorrowed = $user->borrowRecords->where('status', 'borrowed');
        $borrowHistory = $user->borrowRecords->whereIn('status', ['borrowed', 'returned'])->sortByDesc('borrowed_at');
        $returnHistory = $user->borrowRecords->whereNotNull('returned_at')->sortByDesc('returned_at');

        $loginHistory = AuditLog::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->whereIn('action', ['Logged In', 'Logged Out'])
            ->latest()
            ->take(20)
            ->get();

        // Summary stats for spec #8's field list.
        $totalBorrowed = $user->borrowRecords->count();
        $totalReturned = $user->borrowRecords->whereNotNull('returned_at')->count();
        $overdueCount = $user->borrowRecords->where('status', 'borrowed')
            ->filter(fn ($r) => $r->isOverdue())->count();
        $lastLogin = $loginHistory->firstWhere('action', 'Logged In');

        return view('admin.users.show', compact(
            'user', 'currentlyBorrowed', 'borrowHistory', 'returnHistory', 'loginHistory',
            'totalBorrowed', 'totalReturned', 'overdueCount', 'lastLogin'
        ));
    }

    public function destroy(User $user)
    {
        $name = $user->name;
        AuditLogger::log('Deleted User', $user, "Deleted account for {$name}");
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', "Account for {$name} has been deleted.");
    }

    /**
     * Restore a soft-deleted account (spec #12, #16). Borrowing/payment/penalty/audit
     * history was never touched by the soft delete, so it's all still there afterward.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        AuditLogger::log('Restored User', $user, "Restored account for {$user->name}.");

        return redirect()->back()->with('status', "Account for {$user->name} has been restored.");
    }

    public function toggleActive(User $user)
    {
        if (auth()->user()->role === 'student_assistant' && ! in_array($user->role, ['student', 'teacher'], true)) {
            abort(403, 'Library Staff can only enable/disable Student or Teacher accounts.');
        }

        $wasActive = $user->is_active;
        $user->update(['is_active' => !$user->is_active]);

        AuditLogger::log($user->is_active ? 'Enabled Account' : 'Disabled Account', $user);

        if ($wasActive) {
            Notifier::send($user, 'account', 'Account disabled', 'Your account has been disabled by a library administrator.', null, null, external: true);
        } else {
            Notifier::send($user, 'account', 'Account enabled', 'Your account has been enabled. You can now log in to LibraSync.', null, null, external: true);
        }

        return redirect()->back()->with('status', $user->is_active ? "Account for {$user->name} has been enabled." : "Account for {$user->name} has been disabled.");
    }

    /**
     * Directly re-enable a disabled Student/Teacher account (e.g. after End of Semester).
     * Available to both Super Admin and Library Staff (unlike the full toggle-active
     * action, which is Super Admin only).
     */
    public function reactivate(User $user)
    {
        $user->update(['is_active' => true]);

        AuditLogger::log('Reactivated Account', $user);
        Notifier::send($user, 'system', 'Account reactivated', 'Your account has been reactivated. You can now log in to LibraSync.');

        return redirect()->back()->with('status', "Account for {$user->name} has been reactivated.");
    }

    /**
     * Reset a user's password (spec #9). The administrator never sets or sees the new
     * password — a secure random temporary password is generated, the account is flagged
     * to force a change on next login, and the action is fully audited. The temp password
     * itself is shown once, only to the admin performing the reset, so it can be relayed
     * to the user out-of-band (in person, by phone) — it is never emailed or logged.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['confirm' => ['required', 'accepted']]);

        $tempPassword = Str::password(12);

        $user->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        AuditLogger::log('Reset Password', $user, 'Temporary password issued; user must change it on next login.');
        Notifier::send($user, 'account', 'Password reset', 'Your password was reset by a library administrator. You will be asked to set a new password the next time you log in.', null, null, external: true);

        return redirect()->back()->with('tempPassword', $tempPassword)->with('tempPasswordUser', $user->name);
    }

    /**
     * View Activity — audit trail + desk sessions for this user.
     */
    public function activity(User $user)
    {
        $logs = AuditLog::where('actor_id', $user->id)->orWhere(function ($q) use ($user) {
            $q->where('subject_type', User::class)->where('subject_id', $user->id);
        })->latest()->paginate(30);

        $sessions = AssistantSession::where('user_id', $user->id)->latest('login_at')->take(30)->get();

        return view('admin.users.activity', compact('user', 'logs', 'sessions'));
    }

    /**
     * Disable every selected user account at once (Super Admin only).
     */
    public function bulkDisable(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();

        DB::transaction(function () use ($users) {
            foreach ($users as $user) {
                $user->update(['is_active' => false]);
                AuditLogger::log('Disabled Account (Bulk)', $user);
            }
        });

        return redirect()->back()->with('status', $users->count() . ' user account(s) have been disabled.');
    }

    /**
     * Enable every selected user account at once (spec #16).
     */
    public function bulkEnable(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();

        DB::transaction(function () use ($users) {
            foreach ($users as $user) {
                $user->update(['is_active' => true]);
                AuditLogger::log('Enabled Account (Bulk)', $user);
            }
        });

        return redirect()->back()->with('status', $users->count() . ' user account(s) have been enabled.');
    }
}
