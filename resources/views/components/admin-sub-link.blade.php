@props(['href' => null, 'active' => false, 'soon' => false, 'badge' => null])

@if($soon || !$href)
    <div class="flex items-center px-3 py-2 rounded-md text-[13px] text-maroon-300/40 cursor-not-allowed select-none">
        <span class="truncate">{{ $slot }}</span>
        <span class="ml-auto text-[9px] font-semibold uppercase tracking-wide bg-white/5 text-maroon-300/40 px-1.5 py-0.5 rounded">Soon</span>
    </div>
@else
    <a href="{{ $href }}"
       @class([
            'flex items-center px-3 py-2 rounded-md text-[13px] transition-colors',
            'bg-white/10 text-white font-medium' => $active,
            'text-maroon-200/70 hover:bg-white/5 hover:text-white' => !$active,
       ])>
        <span class="truncate">{{ $slot }}</span>
        @if($badge)
            <span class="ml-auto text-[10px] font-bold bg-red-500 text-white rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center">{{ $badge > 9 ? '9+' : $badge }}</span>
        @endif
    </a>
@endif