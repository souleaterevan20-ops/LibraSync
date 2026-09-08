<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AboutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\Admin\BorrowRequestController;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\Admin\StudentAssistantController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\ArchiveController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\PenaltyPaymentController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\AnnouncementDismissController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\Student\MyBorrowingsController;
use App\Http\Controllers\Student\FinesController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');

// The pending approval page for unverified users
Route::get('/pending-approval', function () {
    if (auth()->check() && auth()->user()->is_verified) {
        return redirect()->route('dashboard');
    }
    return view('pending-approval', ['user' => auth()->user()]);
})->middleware(['auth'])->name('pending.approval');

// Protected Routes - Only verified users can pass this gate!
Route::middleware(['auth', 'verified.user', 'password.change'])->group(function () {
    Route::get('/password/force-change', [ForcePasswordChangeController::class, 'show'])->name('password.force-change');
    Route::post('/password/force-change', [ForcePasswordChangeController::class, 'update'])->name('password.force-change.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Shared across all roles ------------------------------------------
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/about', [AboutController::class, 'index'])->name('about.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');

    Route::post('/announcements/{announcement}/dismiss', [AnnouncementDismissController::class, 'store'])->name('announcements.dismiss');
    Route::get('/announcements/{announcement}', [AnnouncementDismissController::class, 'show'])->name('announcements.show');
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');

    Route::get('/my-borrowings', [MyBorrowingsController::class, 'index'])->name('my-borrowings.index');
    Route::get('/fines', [FinesController::class, 'index'])->name('fines.index');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{type}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{type}/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/quick-actions', [ProfileController::class, 'updateQuickActions'])->name('profile.quick-actions');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---- Student Catalog & Borrowing (Students / Teachers) ----------------
    Route::get('/catalog', [BorrowController::class, 'catalog'])->name('catalog.index');
    Route::post('/books/{book}/request', [BorrowController::class, 'requestBook'])->name('books.request');

    // ---- Super Admin + Library Staff: Circulation — VIEWING only ----------
    // Super Admin has read-only oversight here; the actual approve/reject/
    // return/mark-overdue actions below are Library Staff's routine operational
    // responsibility, kept separate so the two roles never perform conflicting
    // actions on the same record.
    Route::middleware(['role:super_admin,student_assistant'])->group(function () {
        Route::get('/admin/pending-users', [UserController::class, 'pendingUsers'])->name('admin.pending-users');
        Route::get('/admin/rejected-users', [UserController::class, 'rejectedUsers'])->name('admin.rejected-users');
        Route::get('/admin/borrow-requests', [BorrowRequestController::class, 'index'])->name('admin.borrows.index');
        Route::get('/admin/returns', [BorrowRequestController::class, 'activeLoans'])->name('admin.returns.index');
        Route::get('/admin/borrows/{record}', [BorrowRequestController::class, 'show'])->name('admin.borrows.show');
    });

    // ---- Library Staff only: Circulation — OPERATIONAL actions ------------
    Route::middleware(['role:student_assistant'])->group(function () {
        Route::patch('/admin/users/{user}/approve', [UserController::class, 'approveUser'])->name('admin.users.approve');
        Route::patch('/admin/users/{user}/reject', [UserController::class, 'rejectUser'])->name('admin.users.reject');

        Route::patch('/admin/borrows/{record}/approve', [BorrowRequestController::class, 'approve'])->name('admin.borrows.approve');
        Route::patch('/admin/borrows/{record}/reject', [BorrowRequestController::class, 'reject'])->name('admin.borrows.reject');

        Route::patch('/admin/returns/{record}/process', [BorrowRequestController::class, 'returnBook'])->name('admin.returns.process');
        Route::patch('/admin/returns/{record}/mark-overdue', [BorrowRequestController::class, 'markOverdue'])->name('admin.returns.mark-overdue');
    });

    // ---- Super Admin + Library Staff shared operations ----------------
    Route::middleware(['role:super_admin,student_assistant'])->group(function () {
        Route::get('/admin/books', [BookController::class, 'index'])->name('admin.books.index');

        // Internal chat — Super Admin <-> Library Staff only.
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{contact}', [ChatController::class, 'show'])->name('chat.show');
        Route::get('/chat/{contact}/poll', [ChatController::class, 'poll'])->name('chat.poll');
        Route::post('/chat/{contact}', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/admin/books/available', [BookController::class, 'available'])->name('admin.books.available');
        Route::get('/admin/books/borrowed', [BookController::class, 'borrowed'])->name('admin.books.borrowed');
        Route::get('/admin/books/{book}', [BookController::class, 'show'])->name('admin.books.show');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/activity', [UserController::class, 'activity'])->name('admin.users.activity');

        // Re-enabling a disabled account — Super Admin or Library Staff.
        Route::patch('/admin/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('admin.users.reactivate');
        Route::patch('/admin/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('admin.users.toggle-active');
        Route::post('/admin/borrow-records/{record}/penalty-payment', [PenaltyPaymentController::class, 'store'])->name('admin.penalties.settle');
        Route::get('/admin/penalties', [PenaltyPaymentController::class, 'index'])->name('admin.penalties.index');
    });

    // ---- System Settings: Super Admin + Library Staff can VIEW ------------
    Route::middleware(['role:super_admin,student_assistant'])->group(function () {
        Route::get('/admin/settings', [SystemSettingsController::class, 'index'])->name('admin.settings.index');
    });

    // ---- Super Admin only --------------------------------------------------
    Route::middleware(['role:super_admin'])->group(function () {
        Route::put('/admin/settings/library-info', [SystemSettingsController::class, 'updateLibraryInfo'])->name('admin.settings.library-info.update');

        Route::post('/admin/settings/committee', [SystemSettingsController::class, 'storeCommitteeMember'])->name('admin.settings.committee.store');
        Route::put('/admin/settings/committee/{committeeMember}', [SystemSettingsController::class, 'updateCommitteeMember'])->name('admin.settings.committee.update');
        Route::patch('/admin/settings/committee/{committeeMember}/toggle', [SystemSettingsController::class, 'toggleCommitteeMember'])->name('admin.settings.committee.toggle');
        Route::patch('/admin/settings/committee/{committeeMember}/reorder', [SystemSettingsController::class, 'reorderCommitteeMember'])->name('admin.settings.committee.reorder');
        Route::delete('/admin/settings/committee/{committeeMember}', [SystemSettingsController::class, 'destroyCommitteeMember'])->name('admin.settings.committee.destroy');

        Route::post('/admin/settings/staff', [SystemSettingsController::class, 'storeStaffMember'])->name('admin.settings.staff.store');
        Route::put('/admin/settings/staff/{staffMember}', [SystemSettingsController::class, 'updateStaffMember'])->name('admin.settings.staff.update');
        Route::patch('/admin/settings/staff/{staffMember}/toggle', [SystemSettingsController::class, 'toggleStaffMember'])->name('admin.settings.staff.toggle');
        Route::patch('/admin/settings/staff/{staffMember}/reorder', [SystemSettingsController::class, 'reorderStaffMember'])->name('admin.settings.staff.reorder');
        Route::delete('/admin/settings/staff/{staffMember}', [SystemSettingsController::class, 'destroyStaffMember'])->name('admin.settings.staff.destroy');

        Route::patch('/admin/borrow-records/{record}/adjust-penalty', [PenaltyPaymentController::class, 'adjustPenalty'])->name('admin.penalties.adjust');
        Route::post('/admin/books', [BookController::class, 'store'])->name('admin.books.store');
        Route::delete('/admin/books/bulk-destroy', [BookController::class, 'bulkDestroy'])->name('admin.books.bulk-destroy');
        Route::patch('/admin/books/{book}', [BookController::class, 'update'])->name('admin.books.update');
        Route::patch('/admin/books/{book}/adjust-stock', [BookController::class, 'adjustStock'])->name('admin.books.adjust-stock');
        Route::delete('/admin/books/{book}', [BookController::class, 'destroy'])->name('admin.books.destroy');
        Route::post('/admin/books/import', [BookController::class, 'import'])->name('admin.books.import');

        Route::get('/admin/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');

        // Backup & Restore — Super Admin only.
        Route::get('/admin/backup', [BackupController::class, 'index'])->name('admin.backup.index');
        Route::patch('/admin/backup/books/{id}/restore', [BackupController::class, 'restoreBook'])->name('admin.backup.restore-book');
        Route::patch('/admin/backup/records/{id}/restore', [BackupController::class, 'restoreBorrowRecord'])->name('admin.backup.restore-record');

        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/admin/users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
        Route::patch('/admin/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::patch('/admin/users/bulk-disable', [UserController::class, 'bulkDisable'])->name('admin.users.bulk-disable');
        Route::patch('/admin/users/bulk-enable', [UserController::class, 'bulkEnable'])->name('admin.users.bulk-enable');

        Route::get('/admin/archive', [ArchiveController::class, 'index'])->name('admin.archive.index');
        Route::post('/admin/archive/end-semester', [ArchiveController::class, 'endSemester'])->name('admin.archive.end-semester');
        Route::get('/admin/archive/{archive}', [ArchiveController::class, 'show'])->name('admin.archive.show');
        Route::get('/admin/archive/{archive}/download-zip', [ArchiveController::class, 'downloadZip'])->name('admin.archive.download-zip');
        Route::get('/admin/archive/{archive}/download-pdf', [ArchiveController::class, 'downloadPdf'])->name('admin.archive.download-pdf');
        Route::get('/admin/archive/{archive}/view-report', [ArchiveController::class, 'viewReport'])->name('admin.archive.view-report');
        Route::get('/admin/archive/{archive}/download-csv', [ArchiveController::class, 'downloadCsv'])->name('admin.archive.download-csv');

        Route::patch('/admin/backup/users/{id}/restore', [BackupController::class, 'restoreUser'])->name('admin.backup.restore-user');
        Route::post('/admin/backup/create', [BackupController::class, 'createBackup'])->name('admin.backup.create');
        Route::post('/admin/backup/restore', [BackupController::class, 'restoreBackup'])->name('admin.backup.restore');
        Route::delete('/admin/backup/delete', [BackupController::class, 'deleteBackup'])->name('admin.backup.delete');

        Route::get('/admin/announcements', [AnnouncementController::class, 'index'])->name('admin.announcements.index');
        Route::post('/admin/announcements', [AnnouncementController::class, 'store'])->name('admin.announcements.store');
        Route::delete('/admin/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

        Route::get('/admin/super-admins', [SuperAdminController::class, 'index'])->name('admin.super-admins.index');
        Route::post('/admin/super-admins', [SuperAdminController::class, 'store'])->name('admin.super-admins.store');
        Route::patch('/admin/super-admins/{admin}', [SuperAdminController::class, 'update'])->name('admin.super-admins.update');
        Route::patch('/admin/super-admins/{admin}/toggle-active', [SuperAdminController::class, 'toggleActive'])->name('admin.super-admins.toggle-active');
        Route::delete('/admin/super-admins/{admin}', [SuperAdminController::class, 'destroy'])->name('admin.super-admins.destroy');

        Route::get('/admin/assistants', [StudentAssistantController::class, 'index'])->name('admin.assistants.index');
        Route::post('/admin/assistants', [StudentAssistantController::class, 'store'])->name('admin.assistants.store');
        Route::patch('/admin/assistants/{assistant}', [StudentAssistantController::class, 'update'])->name('admin.assistants.update');
        Route::patch('/admin/assistants/{assistant}/toggle-active', [StudentAssistantController::class, 'toggleActive'])->name('admin.assistants.toggle-active');
        Route::patch('/admin/assistants/{assistant}/reset-password', [StudentAssistantController::class, 'resetPassword'])->name('admin.assistants.reset-password');
        Route::delete('/admin/assistants/{assistant}', [StudentAssistantController::class, 'destroy'])->name('admin.assistants.destroy');
        Route::get('/admin/assistants/{assistant}/activity', [StudentAssistantController::class, 'activity'])->name('admin.assistants.activity');
    });
});

require __DIR__.'/auth.php';
