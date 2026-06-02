@props([
    'value' => 'all',
])

@php
    $allowed = ['all', 'published', 'draft'];
    $current = in_array((string) $value, $allowed, true) ? (string) $value : 'all';
    $statusOptions = [
        ['value' => 'all', 'label' => 'Все'],
        ['value' => 'published', 'label' => 'Опубликованные'],
        ['value' => 'draft', 'label' => 'Черновики'],
    ];
@endphp

<x-filter-combobox
    name="news_status"
    :selected="$current"
    :options="$statusOptions"
    input-id="news_status_hidden"
    trigger-id="news_status_combobox_trigger"
    variant="filter"
/>
