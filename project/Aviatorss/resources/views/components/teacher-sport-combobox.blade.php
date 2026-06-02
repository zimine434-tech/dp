@props([
    'sports',
    'selected' => null,
    'name' => 'sport_id',
    'emptyLabel' => '— не выбран —',
    'variant' => 'form',
    'inputId' => null,
])

@php
    $sportsList = $sports instanceof \Illuminate\Support\Collection ? $sports : collect($sports);
    $sportOptions = $sportsList
        ->map(fn ($sport) => ['value' => (string) $sport->id, 'label' => $sport->name])
        ->values()
        ->all();
@endphp

<x-filter-combobox
    :name="$name"
    :selected="$selected"
    :options="$sportOptions"
    :empty-label="$emptyLabel"
    :variant="$variant"
    :input-id="$inputId"
/>
