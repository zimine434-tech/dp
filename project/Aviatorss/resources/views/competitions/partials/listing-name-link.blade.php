@props([
    'competition',
    'href',
    'linkClass' => 'text-blue-600 hover:text-blue-800',
])

<a
    href="{{ $href }}"
    title="{{ $competition->name }}"
    {{ $attributes->merge(['class' => trim($linkClass.' line-clamp-2 break-words text-sm font-medium')]) }}
>
    {{ $competition->name }}
</a>
