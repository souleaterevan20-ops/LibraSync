@props(['user' => null, 'size' => 'w-8 h-8', 'text' => 'text-xs'])

@php
    $u = $user ?? auth()->user();
@endphp

@if($u && $u->avatar)
    <img src="{{ \Illuminate\Support\Facades\Storage::url($u->avatar) }}"
         alt="{{ $u->name }}"
         class="{{ $size }} rounded-full object-cover flex-shrink-0">
@else
    <div {{ $attributes->merge(['class' => "$size rounded-full bg-maroon-700 text-white flex items-center justify-center font-semibold flex-shrink-0 $text"]) }}>
        {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
    </div>
@endif
