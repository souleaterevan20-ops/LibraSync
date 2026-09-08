<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use App\Models\PenaltyPayment;
use App\Models\SemesterArchive;
use App\Models\User;
use App\Services\ArchiveService;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function index()
    {
        $archived = BorrowRecord::with(['user', 'book'])
            ->where('is_archived', true)
            ->orderByDesc('returned_at')
            ->paginate(20);

        $eligibleForArchive = BorrowRecord::where('is_archived', false)
            ->where('status', 'returned')
            ->count();

        $activeStudentTeacherCount = User::whereIn('role', ['student', 'teacher'])
            ->where('is_active', true)
            ->count();

        $semesters = BorrowRecord::where('is_archived', true)
            ->select('archived_semester')
            ->distinct()
            ->whereNotNull('archived_semester')
            ->pluck('archived_semester');

        // Preview counts shown in the confirmation modal before running End of Semester (spec #45).
        $preview = [
            'records' => $eligibleForArchive,
            'users' => $activeStudentTeacherCount,
            'penalties' => BorrowRecord::where('is_archived', false)->where('status', 'returned')->where('fine_amount', '>', 0)->count(),
            'payments' => PenaltyPayment::whereDate('created_at', '>=', now()->subMonths(4))->count(),
        ];

        $archiveHistory = SemesterArchive::with('createdBy')->latest()->get();

        return view('admin.archive', compact('archived', 'eligibleForArchive', 'activeStudentTeacherCount', 'semesters', 'preview', 'archiveHistory'));
    }

    /**
     * End of Semester: archive completed transactions, disable every Student/Teacher
     * account, and reset their leaderboard points. Accounts stay disabled until a
     * Super Admin/Library Staff directly reactivates them (see UserController::reactivate).
     * Also generates a full downloadable archive package (spec #46).
     */
    public function endSemester(Request $request)
    {
        $validated = $request->validate([
            'semester' => ['required', 'string', 'max:100'],
        ]);

        $semester = $validated['semester'];

        DB::transaction(function () use ($semester) {
            $archivedCount = BorrowRecord::where('is_archived', false)
                ->where('status', 'returned')
                ->update(['is_archived' => true, 'archived_semester' => $semester]);

            $affectedUsers = User::whereIn('role', ['student', 'teacher'])->where('is_active', true)->get();

            foreach ($affectedUsers as $user) {
                $user->update([
                    'is_active' => false,
                    'points' => 0,
                ]);

                Notifier::send($user, 'system', 'Semester ended', "The {$semester} has ended. Your account is temporarily disabled. Please visit the library circulation desk to settle any outstanding balance and request reactivation.");
            }

            AuditLogger::log('End of Semester Archive', null, "Archived {$archivedCount} record(s); disabled and reset points for {$affectedUsers->count()} student/teacher account(s) for {$semester}.");
        });

        $archive = ArchiveService::generatePackage($semester, auth()->user());
        AuditLogger::log('Generated Archive Package', $archive, "{$archive->sizeHuman()} package for {$semester}.");

        return redirect()->back()->with('status', "End of Semester complete for {$semester}: records archived, accounts disabled, and points reset. The archive package is ready to download below. A Super Admin or Library Staff can reactivate each account from the Users page once ready.");
    }

    public function downloadZip(SemesterArchive $archive)
    {
        AuditLogger::log('Downloaded Archive ZIP', $archive);

        return Storage::disk('local')->download($archive->zip_path, basename($archive->zip_path));
    }

    public function downloadPdf(SemesterArchive $archive)
    {
        AuditLogger::log('Downloaded Archive Summary', $archive);

        return Storage::disk('local')->download($archive->pdf_path, basename($archive->pdf_path));
    }

    /**
     * VIEW REPORT (spec #41) — opens the summary PDF/HTML inline in a new
     * browser tab instead of forcing a download, so Super Admin can quickly
     * check an archive without saving a file to disk every time.
     */
    public function viewReport(SemesterArchive $archive)
    {
        AuditLogger::log('Viewed Archive Report', $archive);

        $isPdf = str_ends_with($archive->pdf_path, '.pdf');

        return response(Storage::disk('local')->get($archive->pdf_path))
            ->header('Content-Type', $isPdf ? 'application/pdf' : 'text/html')
            ->header('Content-Disposition', 'inline; filename="'.basename($archive->pdf_path).'"');
    }

    /**
     * VIEW (spec #41) — lists every CSV table bundled in this archive so
     * Super Admin can see what's included and download any one of them
     * individually, without unzipping the whole package.
     */
    public function show(SemesterArchive $archive)
    {
        $dir = dirname($archive->zip_path);
        $csvFiles = collect(Storage::disk('local')->files($dir))
            ->filter(fn ($path) => str_ends_with($path, '.csv'))
            ->map(fn ($path) => [
                'name' => basename($path, '.csv'),
                'path' => $path,
                'size' => self::humanSize(Storage::disk('local')->size($path)),
            ])
            ->sortBy('name')
            ->values();

        return view('admin.archive.show', compact('archive', 'csvFiles'));
    }

    /**
     * DOWNLOAD CSV (spec #41) — a single table's CSV from a given archive,
     * not the whole zip. $file is validated against files that actually
     * belong to this archive's directory so a request can't be crafted to
     * read an arbitrary path on disk.
     */
    public function downloadCsv(Request $request, SemesterArchive $archive)
    {
        $validated = $request->validate(['file' => ['required', 'string']]);

        // Reject anything with a path separator so this can only ever
        // resolve to a bare filename inside this archive's own directory —
        // never an arbitrary path elsewhere on disk.
        abort_if(str_contains($validated['file'], '/') || str_contains($validated['file'], '\\'), 404);

        $dir = dirname($archive->zip_path);
        $path = "{$dir}/{$validated['file']}";

        abort_unless(
            str_ends_with($path, '.csv') && Storage::disk('local')->exists($path),
            404
        );

        AuditLogger::log('Downloaded Archive CSV', $archive, basename($path));

        return Storage::disk('local')->download($path, basename($path));
    }

    private static function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
