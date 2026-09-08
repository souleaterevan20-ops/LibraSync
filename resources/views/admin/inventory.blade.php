@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
    $isSuperAdmin = auth()->user()->role === 'super_admin';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Inventory / Stock">

    @if (session('status'))
        <div class="mb-4 bg-green-100 dark:bg-emerald-900/30 border border-green-300 dark:border-emerald-800 text-green-800 dark:text-emerald-300 px-4 py-3 rounded-lg text-sm" id="import-success">
            {{ session('status') }}
            <button type="button" onclick="document.getElementById('import-success').remove()" class="ml-3 font-semibold underline">Done</button>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 bg-rose-100 dark:bg-rose-900/30 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stat summary (spec #19 — TOTAL = AVAILABLE + BORROWED + LOST + DAMAGED) -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
        @foreach([
            ['label' => 'Titles', 'value' => $stats['titles']],
            ['label' => 'Total Copies', 'value' => $stats['total_copies']],
            ['label' => 'Available', 'value' => $stats['available_copies']],
            ['label' => 'Lost', 'value' => $stats['lost_copies']],
            ['label' => 'Damaged', 'value' => $stats['damaged_copies']],
            ['label' => 'Out of Stock', 'value' => $stats['low_stock_titles']],
        ] as $card)
            <div class="bg-white dark:bg-bark-900 rounded-xl border border-bark-200/70 dark:border-bark-800 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-bark-400">{{ $card['label'] }}</p>
                <p class="text-xl font-extrabold text-bark-800 dark:text-parchment-100 mt-1">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <!-- Excel/CSV upload -->
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5 mb-6">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-1">Bulk Import Inventory</h3>
        <p class="text-xs text-bark-400 mb-4">
            Upload an .xlsx or .csv file with columns for Title, Author, ISBN, Genre, Type, and Total Copies (column names are matched flexibly).
            New ISBNs are added as new titles; existing ISBNs are restocked (copies added to what's already on the shelf).
        </p>
        <form method="POST" action="{{ route('admin.books.import') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            @csrf
            <input type="file" name="file" accept=".xlsx,.csv" required
                class="text-sm text-bark-600 dark:text-bark-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-maroon-50 file:text-maroon-700 dark:file:bg-maroon-900/30 dark:file:text-maroon-300 file:text-xs file:font-semibold">
            <button type="submit" class="bg-maroon-700 hover:bg-maroon-800 text-white text-sm font-semibold px-5 py-2 rounded-lg whitespace-nowrap">Upload</button>
            <a href="{{ route('admin.books.index') }}" class="text-sm font-medium text-bark-500 hover:text-bark-700 dark:hover:text-parchment-100 whitespace-nowrap">Back to Book Management</a>
        </form>
    </div>

    <!-- Full inventory table -->
    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5" x-data="{ adjustModal: false, adjustBookId: null, adjustTitle: '', adjustCurrent: 0 }">
        <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100">Full Inventory</h3>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search title or ISBN..." class="rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-900 dark:text-parchment-100 text-sm">
                <button class="bg-bark-700 hover:bg-bark-800 text-white text-sm font-medium px-3 py-2 rounded-lg">Search</button>
                @if($search)
                    <a href="{{ route('admin.inventory.index') }}" class="text-sm font-medium text-bark-500 px-2 py-2">Reset</a>
                @endif
            </form>
        </div>

        @if($books->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">No books found.</p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Title</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">ISBN</th>
                            <th class="px-2 pb-2 font-medium hidden md:table-cell">Genre</th>
                            <th class="px-2 pb-2 font-medium text-center">Total</th>
                            <th class="px-2 pb-2 font-medium text-center">Available</th>
                            <th class="px-2 pb-2 font-medium text-center hidden lg:table-cell">Borrowed</th>
                            <th class="px-2 pb-2 font-medium text-center hidden lg:table-cell">Lost</th>
                            <th class="px-2 pb-2 font-medium text-center hidden lg:table-cell">Damaged</th>
                            @if($isSuperAdmin)
                                <th class="px-2 pb-2 font-medium text-center">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($books as $book)
                            <tr>
                                <td class="px-2 py-2">
                                    <a href="{{ route('admin.books.show', $book) }}" class="font-medium text-bark-800 dark:text-parchment-100 hover:text-maroon-700 dark:hover:text-maroon-300">{{ $book->title }}</a>
                                    <div class="text-xs text-bark-400">{{ $book->author }}</div>
                                </td>
                                <td class="px-2 py-2 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $book->isbn }}</td>
                                <td class="px-2 py-2 text-bark-600 dark:text-bark-300 hidden md:table-cell">{{ $book->genre }}</td>
                                <td class="px-2 py-2 text-center font-semibold text-bark-700 dark:text-bark-200">{{ $book->total_copies }}</td>
                                <td class="px-2 py-2 text-center">
                                    @if($book->available_copies === 0 && $book->total_copies > 0)
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">Out of Stock</span>
                                    @else
                                        {{ $book->available_copies }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center hidden lg:table-cell">{{ $book->borrowedCopies() }}</td>
                                <td class="px-2 py-2 text-center hidden lg:table-cell {{ $book->lost_copies > 0 ? 'text-rose-600 font-semibold' : '' }}">{{ $book->lost_copies }}</td>
                                <td class="px-2 py-2 text-center hidden lg:table-cell {{ $book->damaged_copies > 0 ? 'text-amber-600 font-semibold' : '' }}">{{ $book->damaged_copies }}</td>
                                @if($isSuperAdmin)
                                    <td class="px-2 py-2 text-center">
                                        <button type="button"
                                            @click="adjustModal = true; adjustBookId = {{ $book->id }}; adjustTitle = '{{ addslashes($book->title) }}'; adjustCurrent = {{ $book->total_copies }}"
                                            class="text-xs font-semibold text-maroon-700 dark:text-maroon-300 underline">Adjust</button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $books->links() }}</div>
        @endif

        <!-- Inventory adjustment modal (spec #21) -->
        <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-black/50" @click="adjustModal = false"></div>
            <div class="relative bg-white dark:bg-bark-900 rounded-2xl shadow-xl w-full max-w-sm p-6" @click.stop>
                <h3 class="text-lg font-bold text-bark-800 dark:text-parchment-100 mb-1">Inventory Adjustment</h3>
                <p class="text-xs text-bark-400 mb-4" x-text="adjustTitle"></p>

                <form :action="'/admin/books/' + adjustBookId + '/adjust-stock'" method="POST" onsubmit="return confirm('Apply this inventory adjustment? This will be logged.');">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">Previous Total Copies</label>
                            <p class="text-sm font-medium text-bark-800 dark:text-parchment-100" x-text="adjustCurrent"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">New Total Copies</label>
                            <input type="number" name="new_total_copies" min="0" required :value="adjustCurrent"
                                class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bark-500 mb-1">Reason</label>
                            <input type="text" name="reason" required maxlength="255" placeholder="e.g. Permanently removed from collection"
                                class="w-full rounded-lg border-bark-200 dark:border-bark-700 dark:bg-bark-800 dark:text-parchment-100 text-sm">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-5">
                        <button type="button" @click="adjustModal = false" class="flex-1 text-sm font-medium border border-bark-200 dark:border-bark-700 text-bark-600 dark:text-bark-300 px-3 py-2 rounded-lg hover:bg-bark-50 dark:hover:bg-bark-800">Cancel</button>
                        <button type="submit" class="flex-1 text-sm font-semibold bg-maroon-700 hover:bg-maroon-800 text-white px-3 py-2 rounded-lg">Confirm Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-dynamic-component>
