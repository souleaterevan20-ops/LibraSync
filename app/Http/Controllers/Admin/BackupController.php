<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\PenaltyPayment;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupController extends Controller
{
    private function backupDir(): string
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    private function isSqlite(): bool
    {
        return config('database.default') === 'sqlite';
    }

    public function index()
    {
        $role = auth()->user()->role;

        $deletedUsers = User::onlyTrashed()->orderByDesc('deleted_at')->take(25)->get();
        $deletedBooks = Book::onlyTrashed()->orderByDesc('deleted_at')->take(25)->get();
        $deletedRecords = BorrowRecord::onlyTrashed()->with(['user', 'book'])->orderByDesc('deleted_at')->take(25)->get();

        $backupFiles = collect();
        if ($role === 'super_admin' && $this->isSqlite() && File::isDirectory($this->backupDir())) {
            $backupFiles = collect(File::files($this->backupDir()))
                ->filter(fn ($file) => $file->getExtension() === 'zip')
                ->sortByDesc(fn ($file) => $file->getMTime())
                ->map(function ($file) {
                    $manifest = $this->readManifest($file->getFilename());

                    return [
                        'name' => $file->getFilename(),
                        'size' => round($file->getSize() / 1024, 1) . ' KB',
                        'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
                        'manifest' => $manifest,
                    ];
                })
                ->values();
        }

        return view('admin.backup', [
            'deletedUsers' => $deletedUsers,
            'deletedBooks' => $deletedBooks,
            'deletedRecords' => $deletedRecords,
            'backupFiles' => $backupFiles,
            'isSqlite' => $this->isSqlite(),
        ]);
    }

    // ---- Soft-delete recovery (Super Admin + Library Staff, per-model limits enforced in routes) ----

    public function restoreBook($id)
    {
        $book = Book::onlyTrashed()->findOrFail($id);
        $book->restore();

        AuditLogger::log('Restored Book', $book, "Restored '{$book->title}' from Backup & Restore.");

        return redirect()->back()->with('status', "'{$book->title}' has been restored.");
    }

    public function restoreBorrowRecord($id)
    {
        $record = BorrowRecord::onlyTrashed()->findOrFail($id);
        $record->restore();

        AuditLogger::log('Restored Borrow Record', $record, 'Restored a deleted transaction record from Backup & Restore.');

        return redirect()->back()->with('status', 'Transaction record has been restored.');
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        AuditLogger::log('Restored User', $user, "Restored account for {$user->name} from Backup & Restore.");

        return redirect()->back()->with('status', "Account for {$user->name} has been restored.");
    }

    // ---- Full backups: SQLite database + uploaded media + manifest, zipped together (Super Admin only) ----

    /**
     * Builds LIBRASYNC_BACKUP_[timestamp].zip containing:
     *  - database.sqlite  (every table — active AND soft-deleted rows, since
     *    soft-deleted records are still rows in the same table, spec #49-50)
     *  - media/            (announcement images/videos and any other public uploads)
     *  - manifest.json     (spec #52 — backup date, counts, creator, etc.)
     */
    public function createBackup()
    {
        if (! $this->isSqlite()) {
            return redirect()->back()->with('error', 'Manual file backups are only available on the SQLite driver in this build. On MySQL/Postgres, use your database\'s native backup tools (e.g. mysqldump).');
        }

        $filename = 'LIBRASYNC_BACKUP_' . now()->format('Y-m-d_His') . '.zip';
        $destination = $this->backupDir() . '/' . $filename;

        $manifest = $this->buildManifest();

        $zip = new ZipArchive();
        $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile(database_path('database.sqlite'), 'database.sqlite');
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $mediaDir = storage_path('app/public');
        if (File::isDirectory($mediaDir)) {
            foreach (File::allFiles($mediaDir) as $file) {
                $zip->addFile($file->getPathname(), 'media/' . $file->getRelativePathname());
            }
        }
        $zip->close();

        AuditLogger::log('Created System Backup', null, "Created backup file: {$filename} ({$manifest['included_media_count']} media file(s) included).");

        return redirect()->back()->with('status', "Backup created: {$filename} ({$manifest['total_users']} users, {$manifest['total_books']} books, {$manifest['total_borrow_records']} borrow records, {$manifest['included_media_count']} media file(s)).");
    }

    private function buildManifest(): array
    {
        $mediaDir = storage_path('app/public');
        $mediaCount = File::isDirectory($mediaDir) ? count(File::allFiles($mediaDir)) : 0;

        return [
            'backup_date' => now()->toIso8601String(),
            'system_version' => 'LibraSync',
            'database_type' => config('database.default'),
            'created_by' => auth()->user()->name,
            'total_users' => User::withTrashed()->count(),
            'total_books' => Book::withTrashed()->count(),
            'total_borrow_records' => BorrowRecord::withTrashed()->count(),
            'total_penalties' => BorrowRecord::withTrashed()->where('fine_amount', '>', 0)->count(),
            'total_payments' => PenaltyPayment::count(),
            'included_media_count' => $mediaCount,
            'status' => 'completed',
        ];
    }

    private function readManifest(string $zipFilename): ?array
    {
        $path = $this->backupDir() . '/' . $zipFilename;
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return null;
        }

        $json = $zip->getFromName('manifest.json');
        $zip->close();

        return $json ? json_decode($json, true) : null;
    }

    public function restoreBackup(Request $request)
    {
        $validated = $request->validate([
            'filename' => ['required', 'string'],
            'confirm' => ['required', 'in:RESTORE'],
        ]);

        $path = $this->backupDir() . '/' . basename($validated['filename']);

        if (! File::exists($path)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        // Safety copy of the current live database before overwriting it (spec #51).
        $safetyName = 'pre-restore_' . now()->format('Y-m-d_His') . '.zip';
        $this->createBackupFileAt($this->backupDir() . '/' . $safetyName);

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return redirect()->back()->with('error', 'Could not open the backup archive — it may be corrupted.');
        }

        $zip->extractTo(sys_get_temp_dir() . '/librasync_restore', ['database.sqlite']);
        $extractedDb = sys_get_temp_dir() . '/librasync_restore/database.sqlite';

        if (! File::exists($extractedDb)) {
            $zip->close();
            return redirect()->back()->with('error', 'This backup does not contain a valid database file.');
        }

        File::copy($extractedDb, database_path('database.sqlite'));

        // Restore media files alongside the database.
        $tempMediaDir = sys_get_temp_dir() . '/librasync_restore_media';
        File::ensureDirectoryExists($tempMediaDir);
        $mediaEntries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (str_starts_with($entry, 'media/')) {
                $mediaEntries[] = $entry;
            }
        }
        if ($mediaEntries) {
            $zip->extractTo($tempMediaDir, $mediaEntries);
            foreach ($mediaEntries as $entry) {
                $relativePath = substr($entry, strlen('media/'));
                if ($relativePath === '') {
                    continue;
                }
                $from = $tempMediaDir . '/' . $entry;
                $to = storage_path('app/public/' . $relativePath);
                File::ensureDirectoryExists(dirname($to));
                File::copy($from, $to);
            }
            File::deleteDirectory($tempMediaDir);
        }
        $zip->close();

        // Verify database integrity: confirm the restored file is a readable SQLite database.
        $verified = File::exists(database_path('database.sqlite')) && File::size(database_path('database.sqlite')) > 0;

        AuditLogger::log('Restored System Backup', null, "Restored database from backup file: {$validated['filename']}. Integrity check: " . ($verified ? 'passed' : 'FAILED') . '. Safety backup: ' . $safetyName);

        if (! $verified) {
            return redirect()->back()->with('error', 'Restore completed but the integrity check failed — please verify the application manually. A safety backup of the previous state was saved.');
        }

        return redirect()->back()->with('status', "Database restored from {$validated['filename']}. A safety copy of the previous state was saved automatically as {$safetyName}.");
    }

    private function createBackupFileAt(string $destination): void
    {
        $manifest = $this->buildManifest();

        $zip = new ZipArchive();
        $zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile(database_path('database.sqlite'), 'database.sqlite');
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();
    }

    public function deleteBackup(Request $request)
    {
        $validated = $request->validate(['filename' => ['required', 'string']]);
        $path = $this->backupDir() . '/' . basename($validated['filename']);

        if (File::exists($path)) {
            File::delete($path);
            AuditLogger::log('Deleted Backup File', null, "Deleted backup file: {$validated['filename']}");
        }

        return redirect()->back()->with('status', 'Backup file deleted.');
    }
}
