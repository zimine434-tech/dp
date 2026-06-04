@php
    $active = $active ?? false;
    $order = $order ?? 'asc';
    $showInactive = $showInactive ?? false;
@endphp
@if($active)
    @if($order === 'asc')
        <svg class="ml-1 h-4 w-4 shrink-0 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
    @else
        <svg class="ml-1 h-4 w-4 shrink-0 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    @endif
@elseif($showInactive)
    <svg class="ml-1 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
    </svg>
@endif
