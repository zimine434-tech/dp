@props([
    'value' => 'all',
])

@php
    $allowed = ['all', 'published', 'draft'];
    $current = in_array((string) $value, $allowed, true) ? (string) $value : 'all';
    $labels = [
        'all' => 'Все',
        'published' => 'Опубликованные',
        'draft' => 'Черновики',
    ];
    $labelText = $labels[$current];
    $triggerId = 'news_status_combobox_trigger';

    $optionBaseClass = 'flex w-full px-4 py-2.5 text-left text-sm text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none';
    $optionSelectedClass = 'bg-sky-100 font-semibold';
    $triggerClass = 'flex h-10 w-full items-center justify-between gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 text-left text-sm text-gray-900 outline-none transition hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-0';
@endphp

<div class="relative" data-sport-combobox data-combobox-variant="filter">
    <input
        type="hidden"
        name="news_status"
        value="{{ $current }}"
        data-combobox-hidden
        autocomplete="off"
        id="news_status_hidden"
    />

    <button
        type="button"
        id="{{ $triggerId }}"
        data-combobox-trigger
        aria-haspopup="listbox"
        aria-expanded="false"
        class="{{ $triggerClass }}"
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
            class="overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5"
            role="listbox"
            aria-labelledby="{{ $triggerId }}"
        >
            <div class="max-h-52 overflow-y-auto overscroll-contain py-1 [scrollbar-width:thin] [scrollbar-color:#9ca3af_transparent] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400/80">
                @foreach($allowed as $key)
                    <button
                        type="button"
                        role="option"
                        data-combobox-option
                        data-value="{{ $key }}"
                        aria-selected="{{ $current === $key ? 'true' : 'false' }}"
                        class="{{ $optionBaseClass }} {{ $current === $key ? $optionSelectedClass : '' }}"
                    >
                        {{ $labels[$key] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

@include('components.teacher-filter-combobox-script')
