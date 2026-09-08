@props(['href' => null, 'active' => false, 'soon' => false, 'badge' => null])

@if($soon || !$href)
    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-maroon-300/50 cursor-not-allowed select-none">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">{{ $icon }}</svg>
        <span class="truncate" x-show="!sidebarCollapsed">{{ $slot }}</span>
        <span class="ml-auto text-[10px] font-semibold uppercase tracking-wide bg-white/5 text-maroon-300/50 px-1.5 py-0.5 rounded" x-show="!sidebarCollapsed">Soon</span>
    </div>
@else
    <a href="{{ $href }}" :title="sidebarCollapsed ? '{{ $slot }}'.trim() : ''"
       @class([
            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors',
            'bg-white/10 text-white font-medium' => $active,
            'text-maroon-100/80 hover:bg-white/5 hover:text-white' => !$active,
       ])>
        <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-amber-300' : 'text-maroon-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">{{ $icon }}</svg>
        <span class="truncate" x-show="!sidebarCollapsed">{{ $slot }}</span>
        @if($badge)
            <span class="ml-auto text-[10px] font-bold bg-red-500 text-white rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center" x-show="!sidebarCollapsed">{{ $badge > 9 ? '9+' : $badge }}</span>
        @endif
    </a>
@endif
