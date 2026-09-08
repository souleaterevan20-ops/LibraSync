@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
@endphp
<x-dynamic-component :component="$layoutComponent" :title="$book->title">

    <x-back-button />
    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <div class="aspect-[2/3] bg-parchment-100 dark:bg-bark-800 rounded-lg mb-4 flex items-center justify-center overflow-hidden">
                @if($book->cover_image)
                    <img src="{{ Storage::url($book->cover_image) }}" class="w-full h-full object-cover" alt="{{ $book->title }}">
                @else
                    <span class="text-bark-300 text-xs">No cover uploaded</span>
                @endif
            </div>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-bark-400">Title</dt><dd class="text-bark-700 dark:text-bark-200 text-right">{{ $book->title }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Author</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->author }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Publisher</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->publisher ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Publication Year</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->publication_year ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Category</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->genre }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Circulation Type</dt><dd class="text-bark-700 dark:text-bark-200">{{ ucfirst($book->type) }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Total Copies</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->total_copies }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Borrowed Copies</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->borrowedCopies() }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Available Copies</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->available_copies }}</dd></div>
                <div class="flex justify-between"><dt class="text-bark-400">Number of Borrowers</dt><dd class="text-bark-700 dark:text-bark-200">{{ $book->numberOfBorrowers() }}</dd></div>
            </dl>
            @if($book->remarks)
                <p class="text-sm text-bark-500 dark:text-bark-400 mt-4 border-t border-bark-100 dark:border-bark-800 pt-3">{{ $book->remarks }}</p>
            @endif

            @if(auth()->user()->role === 'super_admin')
                <details class="mt-4 border-t border-bark-100 dark:border-bark-800 pt-4">
                    <summary class="text-sm font-semibold text-maroon-700 dark:text-amber-300 cursor-pointer select-none">Edit Book Details</summary>
                    <form action="{{ route('admin.books.update', $book) }}" method="POST" class="mt-3 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">Title</label>
                            <input type="text" name="title" value="{{ old('title', $book->title) }}" required class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">Author</label>
                            <input type="text" name="author" value="{{ old('author', $book->author) }}" required class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">ISBN</label>
                            <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                            <x-input-error :messages="$errors->get('isbn')" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-bark-500 mb-1">Genre</label>
                                <input type="text" name="genre" value="{{ old('genre', $book->genre) }}" required class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bark-500 mb-1">Type</label>
                                <select name="type" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                                    <option value="standard" @selected(old('type', $book->type) === 'standard')>Standard</option>
                                    <option value="reference" @selected(old('type', $book->type) === 'reference')>Reference</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-bark-500 mb-1">Publisher</label>
                                <input type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bark-500 mb-1">Publication Year</label>
                                <input type="number" name="publication_year" value="{{ old('publication_year', $book->publication_year) }}" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">Total Copies</label>
                            <input type="number" name="total_copies" min="1" value="{{ old('total_copies', $book->total_copies) }}" required class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                            <p class="text-[10px] text-bark-400 mt-1">Available copies auto-adjust based on how many are currently borrowed.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">Remarks</label>
                            <textarea name="remarks" rows="2" class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">{{ old('remarks', $book->remarks) }}</textarea>
                        </div>
                        <button type="submit" class="w-full text-sm font-semibold bg-maroon-700 hover:bg-maroon-800 text-white px-3 py-2 rounded-lg">Save Changes</button>
                    </form>
                </details>

                <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="mt-4" onsubmit="return confirm('Remove this book from the catalog?');">
                    @csrf @method('DELETE')
                    <button class="w-full text-sm font-medium bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg">Delete Book</button>
                </form>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Recent Borrowers</h3>
            @forelse($borrowers as $record)
                <div class="flex justify-between text-sm py-2 border-b border-bark-100 dark:border-bark-800 last:border-0">
                    <span class="text-bark-700 dark:text-bark-200">{{ $record->user->name ?? 'Deleted User' }}</span>
                    <span class="text-bark-400">{{ ucfirst($record->status) }} — {{ optional($record->borrowed_at)->format('M d, Y') }}</span>
                </div>
            @empty
                <p class="text-sm text-bark-400">This book hasn't been borrowed yet.</p>
            @endforelse
        </div>
    </div>
</x-dynamic-component>
