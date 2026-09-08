@php
    $layoutComponent = auth()->user()->role === 'super_admin' ? 'admin-layout' : 'assistant-layout';
@endphp
<x-dynamic-component :component="$layoutComponent" title="Available Books">

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
        <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-4">Currently Available</h3>
        @if($books->isEmpty())
            <p class="text-sm text-bark-400 text-center py-8">No books are currently available.</p>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] uppercase tracking-wide text-bark-400 border-b border-bark-100 dark:border-bark-800">
                            <th class="px-2 pb-2 font-medium">Title / Author</th>
                            <th class="px-2 pb-2 font-medium hidden sm:table-cell">Category</th>
                            <th class="px-2 pb-2 font-medium text-center">Available</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                        @foreach($books as $book)
                            <tr>
                                <td class="px-2 py-2.5">
                                    <a href="{{ route('admin.books.show', $book) }}" class="font-medium text-bark-800 dark:text-parchment-100 hover:underline">{{ $book->title }}</a>
                                    <div class="text-xs text-bark-400">{{ $book->author }}</div>
                                </td>
                                <td class="px-2 py-2.5 text-bark-500 dark:text-bark-400 hidden sm:table-cell">{{ $book->genre }}</td>
                                <td class="px-2 py-2.5 text-center font-semibold text-emerald-600">{{ $book->available_copies }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-dynamic-component>
