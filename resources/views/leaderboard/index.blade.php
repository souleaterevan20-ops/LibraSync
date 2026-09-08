@php
    $layoutComponent = match(auth()->user()->role) {
        'super_admin' => 'admin-layout',
        'student_assistant' => 'assistant-layout',
        default => 'student-layout',
    };
@endphp

<x-dynamic-component :component="$layoutComponent" title="Leaderboard">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-bark-800 dark:text-parchment-100">Reading Leaderboard</h2>
            @if($myRank)
                <p class="text-sm text-bark-500 dark:text-bark-400 mt-0.5">You are currently rank #{{ $myRank }}.</p>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-parchment-100 dark:bg-bark-800 text-bark-500 dark:text-bark-400 text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Rank</th>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-right px-4 py-3">Points</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bark-100 dark:divide-bark-800">
                @forelse($rows as $row)
                    <tr class="{{ $row['is_self'] ? 'bg-amber-50 dark:bg-amber-900/20' : '' }}">
                        <td class="px-4 py-3 font-semibold text-bark-800 dark:text-parchment-100">{{ $row['rank'] }}</td>
                        <td class="px-4 py-3 text-bark-700 dark:text-bark-200">
                            {{ $row['name'] }}
                            @if($row['is_self'])
                                <span class="ml-1 text-[10px] font-semibold uppercase bg-amber-400/80 text-bark-900 px-1.5 py-0.5 rounded">You</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-bark-800 dark:text-parchment-100">{{ number_format($row['points']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-10 text-center text-sm text-bark-400">No leaderboard activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-bark-400 dark:text-bark-500 mt-3">To protect user privacy, students and teachers only see their own name on the leaderboard — everyone else appears with initials.</p>

</x-dynamic-component>
