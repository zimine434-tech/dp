@props([
    'selected' => 10,
    'inputId' => 'per_page_bottom',
    'values' => [10, 25, 50, 100],
    'htmlForm' => null,
    'name' => null,
])

@php
    $perPageOptions = collect($values)
        ->map(fn ($n) => ['value' => (string) $n, 'label' => (string) $n])
        ->values()
        ->all();
@endphp

<x-filter-combobox
    :name="$name"
    :selected="(string) $selected"
    :options="$perPageOptions"
    :input-id="$inputId"
    :html-form="$htmlForm"
    variant="filter"
/>
