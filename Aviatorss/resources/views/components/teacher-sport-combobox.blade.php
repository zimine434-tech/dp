@props([
    'sports',
    'selected' => null,
    'name' => 'sport_id',
    'emptyLabel' => '— не выбран —',
    'variant' => 'form',
    'inputId' => null,
])

@php
    $current = (string) old($name, $selected !== null && $selected !== '' ? (string) $selected : '');
    $sportsList = $sports instanceof \Illuminate\Support\Collection ? $sports : collect($sports);
    $selectedSport = $current !== '' ? $sportsList->firstWhere('id', $current) : null;
    $labelText = $selectedSport?->name ?? $emptyLabel;
    $triggerId = ($inputId ?: $name) . '_combobox_trigger';
    $hiddenId = $inputId ?: null;
    $isFilter = $variant === 'filter';

    $triggerClass = $isFilter
        ? 'flex h-10 w-full items-center justify-between gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 text-left text-sm text-gray-900 outline-none transition hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-0'
        : 'flex w-full items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-left text-gray-900 shadow-sm transition hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25';

    $optionBaseClass = $isFilter
        ? 'flex w-full px-4 py-2.5 text-left text-sm text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none'
        : 'flex w-full px-3 py-2 text-left text-sm text-gray-900 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none';

    $optionSelectedClass = $isFilter ? 'bg-sky-100 font-semibold' : 'bg-blue-50/80 font-medium';
@endphp

<div class="relative" data-sport-combobox data-combobox-variant="{{ $variant }}">
    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ $current }}"
        data-combobox-hidden
        autocomplete="off"
        @if($hiddenId) id="{{ $hiddenId }}" @endif
    />

    <button
        type="button"
        id="{{ $triggerId }}"
        data-combobox-trigger
        aria-haspopup="listbox"
        aria-expanded="false"
        class="{{ $triggerClass }} @error($name) border-red-500 ring-1 ring-red-200 @enderror"
    >
        <span class="min-w-0 truncate" data-combobox-label>{{ $labelText }}</span>
        <svg data-combobox-chevron class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div
        data-combobox-panel
        class="absolute left-0 right-0 z-50 mt-1 hidden min-w-full"
        role="presentation"
    >
        <div
            data-combobox-list
            class="{{ $isFilter ? 'overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5' : 'overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg' }}"
            role="listbox"
            aria-labelledby="{{ $triggerId }}"
        >
            <div class="{{ $isFilter ? 'max-h-52 overflow-y-auto overscroll-contain py-1 [scrollbar-width:thin] [scrollbar-color:#9ca3af_transparent] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400/80' : 'max-h-60 overflow-y-auto py-1' }}">
                <button
                    type="button"
                    role="option"
                    data-combobox-option
                    data-value=""
                    aria-selected="{{ $current === '' ? 'true' : 'false' }}"
                    class="{{ $optionBaseClass }} {{ $current === '' ? $optionSelectedClass : '' }}"
                >
                    {{ $emptyLabel }}
                </button>
                @foreach($sportsList as $sport)
                    <button
                        type="button"
                        role="option"
                        data-combobox-option
                        data-value="{{ $sport->id }}"
                        aria-selected="{{ $current === (string) $sport->id ? 'true' : 'false' }}"
                        class="{{ $optionBaseClass }} @if($current === (string) $sport->id) {{ $optionSelectedClass }} @endif"
                    >
                        {{ $sport->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

@error($name)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror

@include('components.teacher-filter-combobox-script')
