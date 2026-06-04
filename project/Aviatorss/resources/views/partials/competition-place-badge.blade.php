@props([
    'place' => null,
    'empty' => '—',
])

@if($place === null || $place === '')
    <span class="text-sm text-gray-500">{{ $empty }}</span>
@else
    <span @class([
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
        'bg-yellow-100 text-yellow-800' => is_numeric($place) && (int) $place === 1,
        'bg-gray-200 text-gray-800' => is_numeric($place) && (int) $place === 2,
        'bg-orange-100 text-orange-800' => is_numeric($place) && (int) $place === 3,
        'bg-blue-100 text-blue-800' => ! (is_numeric($place) && in_array((int) $place, [1, 2, 3], true)),
    ])>
        {{ $place }}
    </span>
@endif
