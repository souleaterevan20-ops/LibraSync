<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\Notification;
use App\Models\PenaltyPayment;
use App\Models\SemesterArchive;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveService
{
    /**
     * Build the full LIBRASYNC_ARCHIVE_[SEMESTER].zip package: one CSV per
     * table plus a summary document, and record it as a SemesterArchive row
     * for the Archive History page. Called once, right after the End of
     * Semester transaction commits (spec #45-47).
     */
    public static function generatePackage(string $semester, User $creator): SemesterArchive
    {
        $slug = str()->slug($semester);
        $dir = "archives/{$slug}-" . now()->format('Ymd-His');
        Storage::disk('local')->makeDirectory($dir);

        $tables = [
            'USERS' => self::usersCsv(),
            'BOOKS' => self::booksCsv(),
            'BORROW_RECORDS' => self::borrowRecordsCsv($semester),
            'PENALTIES' => self::penaltiesCsv($semester),
            'PAYMENTS' => self::paymentsCsv(),
            'AUDIT_LOGS' => self::auditLogsCsv(),
            'NOTIFICATIONS' => self::notificationsCsv(),
            'ANNOUNCEMENTS' => self::announcementsCsv(),
        ];

        $csvPaths = [];
        foreach ($tables as $name => [$rows, $columns]) {
            $path = "{$dir}/{$name}.csv";
            Storage::disk('local')->put($path, self::toCsvString($rows, $columns));
            $csvPaths[$name] = Storage::disk('local')->path($path);
        }

        $recordCount = count($tables['BORROW_RECORDS'][0]);
        $userCount = count($tables['USERS'][0]);
        $penaltyCount = count($tables['PENALTIES'][0]);
        $paymentCount = count($tables['PAYMENTS'][0]);

        $summaryPath = self::generateSummary($dir, $semester, $creator, $recordCount, $userCount, $penaltyCount, $paymentCount);

        $zipRelativePath = "{$dir}/LIBRASYNC_ARCHIVE_{$slug}.zip";
        $zipFullPath = Storage::disk('local')->path($zipRelativePath);

        $zip = new ZipArchive();
        $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($csvPaths as $name => $fullPath) {
            $zip->addFile($fullPath, "{$name}.csv");
        }
        $zip->addFile(Storage::disk('local')->path($summaryPath), basename($summaryPath));
        $zip->close();

        return SemesterArchive::create([
            'semester' => $semester,
            'created_by' => $creator->id,
            'record_count' => $recordCount,
            'user_count' => $userCount,
            'penalty_count' => $penaltyCount,
            'payment_count' => $paymentCount,
            'zip_path' => $zipRelativePath,
            'pdf_path' => $summaryPath,
            'size_bytes' => Storage::disk('local')->size($zipRelativePath),
            'status' => 'completed',
        ]);
    }

    private static function toCsvString(array $rows, array $columns): string
    {
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, $columns);
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }

    private static function usersCsv(): array
    {
        $users = User::withTrashed()->get();
        $rows = $users->map(fn ($u) => [
            $u->id, $u->name, $u->email, $u->role, $u->is_active ? 'active' : 'disabled',
            $u->points, $u->penalty_balance, $u->trashed() ? 'deleted' : 'active',
        ])->all();

        return [$rows, ['ID', 'Name', 'Email', 'Role', 'Account Status', 'Points', 'Penalty Balance', 'Record Status']];
    }

    private static function booksCsv(): array
    {
        $books = Book::withTrashed()->get();
        $rows = $books->map(fn ($b) => [
            $b->id, $b->title, $b->author, $b->isbn, $b->total_copies, $b->available_copies, $b->trashed() ? 'deleted' : 'active',
        ])->all();

        return [$rows, ['ID', 'Title', 'Author', 'ISBN', 'Total Copies', 'Available Copies', 'Record Status']];
    }

    private static function borrowRecordsCsv(string $semester): array
    {
        $records = BorrowRecord::withTrashed()->where('archived_semester', $semester)->with(['user', 'book'])->get();
        $rows = $records->map(fn ($r) => [
            $r->id, $r->user->name ?? 'Deleted User', $r->book->title ?? 'Deleted Book', $r->status,
            optional($r->borrowed_at)->format('Y-m-d'), optional($r->due_at)->format('Y-m-d'), optional($r->returned_at)->format('Y-m-d'),
        ])->all();

        return [$rows, ['ID', 'User', 'Book', 'Status', 'Borrowed', 'Due', 'Returned']];
    }

    private static function penaltiesCsv(string $semester): array
    {
        $records = BorrowRecord::withTrashed()->where('archived_semester', $semester)->where('fine_amount', '>', 0)->with(['user', 'book'])->get();
        $rows = $records->map(fn ($r) => [
            $r->id, $r->user->name ?? 'Deleted User', $r->book->title ?? 'Deleted Book',
            $r->penaltyType(), $r->fine_amount, $r->fine_paid_amount, $r->fineRemaining(), $r->computedFineStatus(),
        ])->all();

        return [$rows, ['Borrow Record ID', 'User', 'Book', 'Type', 'Penalty', 'Paid', 'Remaining', 'Status']];
    }

    private static function paymentsCsv(): array
    {
        $payments = PenaltyPayment::with(['user', 'borrowRecord.book', 'recordedBy'])->get();
        $rows = $payments->map(fn ($p) => [
            $p->id, $p->user->name ?? 'Deleted User', $p->borrowRecord->book->title ?? '—', $p->amount,
            $p->payment_method, $p->recordedBy->name ?? 'System', $p->created_at->format('Y-m-d H:i'),
        ])->all();

        return [$rows, ['Payment ID', 'User', 'Book', 'Amount', 'Method', 'Recorded By', 'Date']];
    }

    private static function auditLogsCsv(): array
    {
        $logs = AuditLog::latest()->get();
        $rows = $logs->map(fn ($l) => [$l->id, $l->actor_name, $l->actor_role, $l->action, $l->description, $l->created_at->format('Y-m-d H:i')])->all();

        return [$rows, ['ID', 'Actor', 'Role', 'Action', 'Description', 'Date']];
    }

    private static function notificationsCsv(): array
    {
        $notifications = Notification::with('user')->get();
        $rows = $notifications->map(fn ($n) => [
            $n->id, $n->user->name ?? 'Deleted User', $n->type, $n->title, $n->is_read ? 'read' : 'unread', $n->created_at->format('Y-m-d H:i'),
        ])->all();

        return [$rows, ['ID', 'User', 'Type', 'Title', 'Read Status', 'Date']];
    }

    private static function announcementsCsv(): array
    {
        $announcements = Announcement::with('creator')->get();
        $rows = $announcements->map(fn ($a) => [
            $a->id, $a->title, $a->creator->name ?? 'Unknown', optional($a->posted_at)->format('Y-m-d'), optional($a->expires_at)->format('Y-m-d') ?: 'No expiry',
        ])->all();

        return [$rows, ['ID', 'Title', 'Posted By', 'Posted At', 'Expires At']];
    }

    /**
     * ARCHIVE_SUMMARY document. Uses barryvdh/laravel-dompdf for a real PDF
     * when it's installed; otherwise falls back to a printable HTML file
     * (still fully readable and downloadable — just not natively .pdf) so
     * the archive never fails to generate for lack of an optional package.
     */
    private static function generateSummary(string $dir, string $semester, User $creator, int $recordCount, int $userCount, int $penaltyCount, int $paymentCount): string
    {
        $html = view('admin.archive.summary', [
            'semester' => $semester,
            'creator' => $creator,
            'recordCount' => $recordCount,
            'userCount' => $userCount,
            'penaltyCount' => $penaltyCount,
            'paymentCount' => $paymentCount,
            'generatedAt' => now(),
        ])->render();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $path = "{$dir}/ARCHIVE_SUMMARY.pdf";
            Storage::disk('local')->put($path, $pdf->output());

            return $path;
        }

        // Fallback: no PDF library installed. Save a printable HTML summary instead —
        // still opens in any browser and can be printed/saved to PDF with Ctrl+P.
        $path = "{$dir}/ARCHIVE_SUMMARY.html";
        Storage::disk('local')->put($path, $html);

        return $path;
    }
}
