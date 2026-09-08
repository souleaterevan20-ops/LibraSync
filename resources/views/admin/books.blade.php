@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp
<x-dynamic-component :component="$layoutComponent" title="Book Management">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Book List -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Current Inventory</h3>
                <a href="{{ route('admin.inventory.index') }}" class="text-xs font-medium text-maroon-700 dark:text-maroon-300 hover:underline">Full Inventory / Stock &rarr;</a>
            </div>

            <!-- Search & filters (spec #18) -->
            <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-4">
                <input type="text" name="search" value="{{ $search }}" placeholder="Title, author, ISBN, or ID..."
                    class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm lg:col-span-2">
                <select name="genre" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                    <option value="">All Genres</option>
                    @foreach($genres as $g)
                        <option value="{{ $g }}" @selected(request('genre') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
                <select name="type" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                    <option value="">All Types</option>
                    <option value="standard" @selected(request('type') === 'standard')>Standard</option>
                    <option value="reference" @selected(request('type') === 'reference')>Reference</option>
                </select>
                <select name="availability" class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                    <option value="">Any Availability</option>
                    <option value="available" @selected(request('availability') === 'available')>Available</option>
                    <option value="unavailable" @selected(request('availability') === 'unavailable')>Out of Stock</option>
                </select>
                <div class="flex gap-2">
                    <button class="flex-1 bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Search</button>
                    <a href="{{ route('admin.books.index') }}" class="flex-1 text-center bg-bark-100 dark:bg-bark-800 text-bark-600 dark:text-bark-300 text-sm font-medium px-3 py-2 rounded-lg">Reset</a>
                </div>
            </form>

            @if($books->isEmpty())
                <p class="text-sm text-bark-400 text-center py-8">No books found.</p>
            @else
                <form method="POST" action="{{ route('admin.books.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected book(s)? This cannot be undone.');">
                    @csrf
                    @method('DELETE')

                    @if(auth()->user()->role === 'super_admin')
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 text-xs text-bark-500">
                                <input type="checkbox" onclick="document.querySelectorAll('.book-row-check').forEach(cb => cb.checked = this.checked)" class="rounded border-bark-300">
                                Select All
                            </label>
                            <button type="submit" class="text-xs font-semibold text-rose-700 hover:underline">Delete Selected</button>
                        </div>
                    @endif

                    <div class="overflow-x-auto -mx-1">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                                    @if(auth()->user()->role === 'super_admin')
                                        <th class="px-2 pb-2 font-medium w-6"></th>
                                    @endif
                                    <th class="px-2 pb-2 font-medium">Title / Author</th>
                                    <th class="px-2 pb-2 font-medium hidden sm:table-cell">ISBN</th>
                                    <th class="px-2 pb-2 font-medium hidden md:table-cell">Genre</th>
                                    <th class="px-2 pb-2 font-medium">Type</th>
                                    <th class="px-2 pb-2 font-medium text-center">Copies</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                                @foreach($books as $book)
                                    <tr class="hover:bg-parchment-50 dark:hover:bg-bark-800/50 cursor-pointer" onclick="if(!event.target.closest('input')) window.location='{{ route('admin.books.show', $book) }}'">
                                        @if(auth()->user()->role === 'super_admin')
                                            <td class="px-2 py-2.5" onclick="event.stopPropagation()">
                                                <input type="checkbox" name="book_ids[]" value="{{ $book->id }}" class="book-row-check rounded border-bark-300">
                                            </td>
                                        @endif
                                        <td class="px-2 py-2.5">
                                            <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $book->title }}</div>
                                            <div class="text-xs text-bark-400">by {{ $book->author }}</div>
                                        </td>
                                        <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $book->isbn }}</td>
                                        <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden md:table-cell">{{ $book->genre }}</td>
                                        <td class="px-2 py-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $book->type === 'reference' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                                {{ ucfirst($book->type) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2.5 text-center font-semibold text-bark-700 dark:text-bark-200">
                                            {{ $book->available_copies }} / {{ $book->total_copies }}
                                            @if($book->lost_copies > 0 || $book->damaged_copies > 0)
                                                <span class="block text-[10px] font-normal text-rose-500">
                                                    @if($book->lost_copies > 0) {{ $book->lost_copies }} lost @endif
                                                    @if($book->damaged_copies > 0) {{ $book->damaged_copies }} damaged @endif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                    </table>
                </div>
                </form>
                <div class="mt-4">{{ $books->links() }}</div>
            @endif
        </div>

        <!-- Add Book Form -->
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Add New Book</h3>

            <form action="{{ route('admin.books.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Book Title</label>
                    <input type="text" name="title" required class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Author</label>
                    <input type="text" name="author" required class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">ISBN (13 Digits)</label>
                    <input type="text" name="isbn" required class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Genre</label>
                    <input type="text" name="genre" required placeholder="e.g., Fiction, Science" class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Book Type</label>
                    <select name="type" required class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                        <option value="standard">Standard (Borrowable)</option>
                        <option value="reference">Reference (In-Library Only)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-bark-700 dark:text-bark-200 mb-1">Total Copies</label>
                    <input type="number" name="total_copies" min="1" required class="block w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 focus:border-maroon-500 focus:ring-maroon-500 text-sm">
                </div>

                <button type="submit" class="w-full bg-maroon-700 hover:bg-maroon-800 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    Add Book to Inventory
                </button>
            </form>
        </div>
    </div>
</x-dynamic-component>
