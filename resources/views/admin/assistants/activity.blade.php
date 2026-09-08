<x-admin-layout :title="'Activity — ' . $assistant->name">

    <x-back-button />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Audit Trail</h3>
            @forelse($logs as $log)
                <div class="text-sm py-2 border-b border-bark-100 dark:border-bark-800 last:border-0">
                    <div class="font-medium text-bark-800 dark:text-parchment-100">{{ $log->action }}</div>
                    @if($log->description)<div class="text-bark-500 dark:text-bark-400">{{ $log->description }}</div>@endif
                    <div class="text-xs text-bark-400">{{ $log->created_at->format('M d, Y H:i') }}</div>
                </div>
            @empty
                <p class="text-sm text-bark-400">No recorded activity yet.</p>
            @endforelse
            <div class="mt-3">{{ $logs->links() }}</div>
        </div>

        <div class="bg-white dark:bg-bark-900 rounded-xl shadow-sm border border-bark-200/70 dark:border-bark-800 p-5">
            <h3 class="font-semibold text-bark-800 dark:text-parchment-100 mb-3">Desk Sessions</h3>
            @forelse($sessions as $session)
                <div class="flex justify-between text-sm py-2 border-b border-bark-100 dark:border-bark-800 last:border-0">
                    <span class="text-bark-700 dark:text-bark-200">{{ $session->login_at->format('M d, Y H:i') }}</span>
                    <span class="text-bark-400">{{ $session->logout_at ? $session->logout_at->format('M d, Y H:i') : 'Still active' }}</span>
                </div>
            @empty
                <p class="text-sm text-bark-400">No desk sessions recorded yet.</p>
            @endforelse
        </div>
    </div>
</x-admin-layout>
