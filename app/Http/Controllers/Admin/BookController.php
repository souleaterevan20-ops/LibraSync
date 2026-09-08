<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\AuditLogger;
use App\Services\SpreadsheetReader;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display the book catalog and creation form, with search/filter/pagination (spec #18).
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $genreFilter = $request->input('genre');
        $typeFilter = $request->input('type');
        $availabilityFilter = $request->input('availability');

        $query = Book::query();

        $query->when($search, function ($q) use ($search) {
            $q->where(function ($q2) use ($search) {
                $q2->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        });

        $query->when($genreFilter, fn ($q) => $q->where('genre', $genreFilter));
        $query->when($typeFilter, fn ($q) => $q->where('type', $typeFilter));

        if ($availabilityFilter === 'available') {
            $query->where('available_copies', '>', 0);
        } elseif ($availabilityFilter === 'unavailable') {
            $query->where('available_copies', 0);
        }

        $books = $query->orderBy('title')->paginate(25)->withQueryString();
        $genres = Book::distinct()->orderBy('genre')->pluck('genre');

        return view('admin.books', compact('books', 'search', 'genres'));
    }

    /**
     * Store a newly created book in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1400|max:'.(date('Y') + 1),
            'isbn' => 'required|string|unique:books,isbn|max:13',
            'genre' => 'required|string|max:100',
            'type' => 'required|in:standard,reference',
            'total_copies' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $validated['available_copies'] = $validated['total_copies'];

        $book = Book::create($validated);

        AuditLogger::log('Added Book', $book);

        return redirect()->back()->with('status', "Book '{$request->title}' successfully added to the catalog!");
    }

    /**
     * "Total Books" card detail — full book profile.
     */
    public function show(Book $book)
    {
        $borrowers = $book->borrowRecords()->with('user')->latest()->take(20)->get();

        return view('admin.books.show', compact('book', 'borrowers'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:50|unique:books,isbn,' . $book->id,
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1400|max:'.(date('Y') + 1),
            'genre' => 'required|string|max:100',
            'type' => 'required|in:standard,reference',
            'total_copies' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Keep available_copies consistent if total_copies increases/decreases
        $borrowed = $book->borrowedCopies();
        $validated['available_copies'] = max($validated['total_copies'] - $borrowed, 0);

        $book->update($validated);

        AuditLogger::log('Edited Book', $book);

        return redirect()->back()->with('status', "Book '{$book->title}' updated.");
    }

    /**
     * Super Admin manual inventory adjustment (spec #21) — e.g. permanently
     * removing damaged/lost copies from the collection, or correcting a
     * physical count discrepancy found during a shelf audit. Always requires
     * a reason and is fully audited with the before/after values.
     */
    public function adjustStock(Request $request, Book $book)
    {
        $validated = $request->validate([
            'new_total_copies' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $previousTotal = $book->total_copies;
        $newTotal = (int) $validated['new_total_copies'];
        $borrowed = $book->borrowedCopies();

        if ($newTotal < $borrowed + $book->lost_copies + $book->damaged_copies) {
            return redirect()->back()->with('error', "Cannot set total copies below what's currently borrowed, lost, or damaged ({$borrowed} borrowed + {$book->lost_copies} lost + {$book->damaged_copies} damaged = " . ($borrowed + $book->lost_copies + $book->damaged_copies) . ' minimum).');
        }

        $book->update([
            'total_copies' => $newTotal,
            'available_copies' => max($newTotal - $borrowed - $book->lost_copies - $book->damaged_copies, 0),
        ]);

        AuditLogger::log('Inventory Adjustment', $book, "'{$book->title}': {$previousTotal} -> {$newTotal} copies. Reason: {$validated['reason']}");

        return redirect()->back()->with('status', "Inventory for '{$book->title}' adjusted from {$previousTotal} to {$newTotal} copies.");
    }

    public function destroy(Book $book)
    {
        $title = $book->title;
        AuditLogger::log('Deleted Book', $book, "Deleted '{$title}' from catalog");
        $book->delete();

        return redirect()->route('admin.books.index')->with('status', "Book '{$title}' has been removed from the catalog.");
    }

    /**
     * Delete multiple selected books at once (Super Admin only).
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'book_ids' => ['required', 'array', 'min:1'],
            'book_ids.*' => ['exists:books,id'],
        ]);

        $books = Book::whereIn('id', $validated['book_ids'])->get();

        foreach ($books as $book) {
            AuditLogger::log('Deleted Book (Bulk)', $book, "Deleted '{$book->title}' from catalog");
            $book->delete();
        }

        return redirect()->route('admin.books.index')->with('status', $books->count() . ' book(s) have been removed from the catalog.');
    }

    /**
     * "Total Available" card — books currently available.
     */
    public function available()
    {
        $books = Book::where('available_copies', '>', 0)->get();

        return view('admin.books.available', compact('books'));
    }

    /**
     * "Total Borrowed" card — every book currently checked out, with
     * borrower, borrow date, due date, and status.
     */
    public function borrowed()
    {
        $records = \App\Models\BorrowRecord::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->orderBy('due_at')
            ->get();

        return view('admin.books.borrowed', compact('records'));
    }

    /**
     * Bulk import books from an uploaded .xlsx or .csv file.
     * Column headers are matched flexibly (e.g. "Book Title", "Title", "Name" all map to title).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $rows = $extension === 'xlsx'
                ? SpreadsheetReader::readXlsx($file->getRealPath())
                : $this->readCsv($file->getRealPath());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Could not read the file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'The file appears to be empty.');
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->matchColumns($headerRow);

        if (! isset($columnMap['title']) || ! isset($columnMap['isbn'])) {
            return redirect()->back()->with('error', 'Could not find "Title" and "ISBN" columns in the file. Please make sure your spreadsheet has headers for at least Title, Author, ISBN, Genre, Type, and Total Copies.');
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $get = fn (string $field) => isset($columnMap[$field]) ? ($row[$columnMap[$field]] ?? null) : null;

            $title = trim((string) $get('title'));
            $isbn = trim((string) $get('isbn'));

            if ($title === '' || $isbn === '') {
                $skipped++;
                continue;
            }

            $type = strtolower(trim((string) ($get('type') ?? 'standard')));
            $type = in_array($type, ['standard', 'reference'], true) ? $type : 'standard';

            $totalCopies = max(1, (int) ($get('total_copies') ?? 1));

            $existing = Book::withTrashed()->where('isbn', $isbn)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                // Treat re-imports of an existing ISBN as restocking: add the imported quantity.
                $existing->increment('total_copies', $totalCopies);
                $existing->increment('available_copies', $totalCopies);

                if ($genre = $get('genre')) $existing->genre = $genre;
                if ($author = $get('author')) $existing->author = $author;
                if ($publisher = $get('publisher')) $existing->publisher = $publisher;
                $existing->save();

                $updated++;
            } else {
                Book::create([
                    'title' => $title,
                    'author' => $get('author') ?: 'Unknown',
                    'isbn' => $isbn,
                    'genre' => $get('genre') ?: 'General',
                    'type' => $type,
                    'publisher' => $get('publisher'),
                    'publication_year' => is_numeric($get('publication_year')) ? (int) $get('publication_year') : null,
                    'total_copies' => $totalCopies,
                    'available_copies' => $totalCopies,
                    'remarks' => $get('remarks'),
                ]);
                $inserted++;
            }
        }

        AuditLogger::log('Imported Books', null, "Inventory import: {$inserted} new, {$updated} restocked, {$skipped} skipped.");

        return redirect()->back()->with('status', "Import complete: {$inserted} new book(s) added, {$updated} existing book(s) restocked, {$skipped} row(s) skipped (missing title/ISBN).");
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('Could not open the CSV file.');
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Flexibly match an uploaded header row to our known book fields.
     *
     * @return array<string, int> field name => column index
     */
    private function matchColumns(array $headerRow): array
    {
        $synonyms = [
            'title' => ['title', 'booktitle', 'name'],
            'author' => ['author', 'writer'],
            'isbn' => ['isbn', 'isbnnumber', 'isbn13', 'isbn10'],
            'genre' => ['genre', 'category', 'subject'],
            'type' => ['type', 'booktype'],
            'total_copies' => ['totalcopies', 'copies', 'quantity', 'qty', 'stock'],
            'publisher' => ['publisher'],
            'publication_year' => ['publicationyear', 'year', 'yearpublished'],
            'remarks' => ['remarks', 'notes', 'description'],
        ];

        $map = [];

        foreach ($headerRow as $index => $header) {
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $header));

            foreach ($synonyms as $field => $aliases) {
                if (in_array($normalized, $aliases, true) && ! isset($map[$field])) {
                    $map[$field] = $index;
                }
            }
        }

        return $map;
    }
}
