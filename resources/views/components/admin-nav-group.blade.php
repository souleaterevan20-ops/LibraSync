@props(['label', 'active' => false, 'badge' => null])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button @click="sidebarCollapsed ? (sidebarCollapsed = false) : (open = !open)" type="button" :title="sidebarCollapsed ? '{{ $label }}' : ''"
        @class([
            'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors',
            'bg-white/10 text-white font-medium' => $active,
            'text-maroon-100/80 hover:bg-white/5 hover:text-white' => !$active,
        ])>
        <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-amber-300' : 'text-maroon-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">{{ $icon }}</svg>
        <span class="truncate" x-show="!sidebarCollapsed">{{ $label }}</span>
        @if($badge)
            <span class="text-[10px] font-bold bg-red-500 text-white rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center" x-show="!sidebarCollapsed">{{ $badge > 9 ? '9+' : $badge }}</span>
        @endif
        <svg class="w-4 h-4 {{ $badge ? '' : 'ml-auto' }} shrink-0 transition-transform" x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
    <div x-show="open && !sidebarCollapsed" x-transition class="ml-6 mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
        {{ $slot }}
    </div>
</div>