@props([
    'href',
    'routes' => [],
    'label',
    'activeTone' => 'blue',
    'student' => false,
])

@php
    $routeList = is_array($routes) ? $routes : [$routes];
    $isActive = collect($routeList)->contains(fn ($pattern) => request()->routeIs($pattern));

    $linkBase = $student
        ? 'flex items-center justify-start px-4 py-3 rounded-lg transition group sidebar-link'
        : 'flex items-center px-4 py-3 rounded-lg transition';

    $inactive = 'text-gray-700 hover:bg-blue-50 hover:text-blue-600';
    $activeClass = $activeTone === 'green'
        ? 'bg-green-50 text-green-700 font-semibold'
        : 'bg-blue-50 text-blue-700 font-semibold';

    $linkClass = $linkBase.' '.($isActive ? $activeClass : $inactive);
    $iconClass = $student ? 'w-5 h-5 sidebar-icon flex-shrink-0' : 'w-5 h-5 mr-3';
    $labelClass = $student ? 'sidebar-text ml-3' : '';
@endphp

<a href="{{ $href }}" class="{{ $linkClass }}" @if($isActive) aria-current="page" @endif>
    <span class="{{ $iconClass }}">
        {{ $slot }}
    </span>
    <span class="{{ $labelClass }}">{{ $label }}</span>
</a>
