@props([
    'action',
    'resetUrl',
    'listingFilters' => [],
    'sportsForFilter',
    'idPrefix' => 'filters',
    'listingSearchRootId' => null,
    'trainingTableSearch' => false,
    'debounceSearchSubmit' => false,
])

@php
    $lf = $listingFilters;
    $jsTitleSearch = $listingSearchRootId || $trainingTableSearch;
@endphp

<form
    method="GET"
    action="{{ $action }}"
    @if($jsTitleSearch && $listingSearchRootId && ! $trainingTableSearch)
        data-listing-js-search-root="#{{ $listingSearchRootId }}"
    @endif
    @if($jsTitleSearch && $trainingTableSearch)
        data-listing-js-training-table="1"
    @endif
    class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
>
    {{ $slot }}
    <button type="submit" class="sr-only">Применить фильтры по дате и спорту</button>

    <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
        <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
            <label for="{{ $idPrefix }}_sport_id" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
            <select
                id="{{ $idPrefix }}_sport_id"
                name="sport_id"
                onchange="this.form.requestSubmit()"
                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
                <option value="">Все</option>
                @foreach($sportsForFilter as $sportOption)
                    <option value="{{ $sportOption->id }}" @selected((string)($lf['sport_id'] ?? '') === (string) $sportOption->id)>{{ $sportOption->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0 flex-1">
            <label for="{{ $idPrefix }}_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск по названию</label>
            @if($jsTitleSearch)
                <input
                    type="search"
                    id="{{ $idPrefix }}_q"
                    data-listing-js-search-input
                    maxlength="200"
                    value=""
                    placeholder="Введите название..."
                    autocomplete="off"
                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            @else
                <input
                    type="search"
                    id="{{ $idPrefix }}_q"
                    name="q"
                    value="{{ $lf['q'] ?? '' }}"
                    maxlength="200"
                    placeholder="Введите название..."
                    autocomplete="off"
                    @if($debounceSearchSubmit)
                        oninput="if(this._deb)clearTimeout(this._deb);var f=this.form;if(!f)return;this._deb=setTimeout(function(){f.requestSubmit()},400);"
                    @endif
                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
            <div class="min-w-0 sm:w-40">
                <label for="{{ $idPrefix }}_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                <input
                    type="date"
                    id="{{ $idPrefix }}_date_from"
                    name="date_from"
                    value="{{ $lf['date_from'] ?? '' }}"
                    onchange="this.form.requestSubmit()"
                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>
            <div class="min-w-0 sm:w-40">
                <label for="{{ $idPrefix }}_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                <input
                    type="date"
                    id="{{ $idPrefix }}_date_to"
                    name="date_to"
                    value="{{ $lf['date_to'] ?? '' }}"
                    onchange="this.form.requestSubmit()"
                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                >
            </div>
        </div>

        <div class="flex w-full shrink-0 lg:w-auto lg:max-w-[9rem]">
            <a
                href="{{ $resetUrl }}"
                class="inline-flex h-10 w-full min-w-[7.5rem] items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
            >
                Сбросить
            </a>
        </div>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                var debounceMs = 120;

                function needleFromForm(form) {
                    var input = form.querySelector('[data-listing-js-search-input]');
                    return input ? input.value.trim().toLowerCase() : '';
                }

                function applyScopedListingTitleSearch(form) {
                    var sel = form.getAttribute('data-listing-js-search-root');
                    if (!sel) {
                        return;
                    }
                    var root = document.querySelector(sel);
                    if (!root) {
                        return;
                    }
                    var needle = needleFromForm(form);
                    var nodes = root.querySelectorAll('[data-listing-search-haystack]');
                    var any = false;
                    nodes.forEach(function (node) {
                        var hay = node.getAttribute('data-listing-search-haystack') || '';
                        var ok = !needle || hay.indexOf(needle) !== -1;
                        node.classList.toggle('hidden', !ok);
                        if (ok) {
                            any = true;
                        }
                    });
                    var emptyEl = root.id ? document.getElementById(root.id + '-js-empty') : null;
                    if (emptyEl && nodes.length) {
                        emptyEl.classList.toggle('hidden', any);
                    }
                }

                function bindListingTitleSearchForm(form) {
                    if (form._listingTitleSearchBound) {
                        return;
                    }
                    var input = form.querySelector('[data-listing-js-search-input]');
                    if (!input) {
                        return;
                    }
                    form._listingTitleSearchBound = true;

                    var run = function () {
                        if (form.hasAttribute('data-listing-js-training-table')) {
                            if (typeof window.applyStudentTrainingTableTitleFilter === 'function') {
                                window.applyStudentTrainingTableTitleFilter();
                            }
                            return;
                        }
                        applyScopedListingTitleSearch(form);
                    };

                    var t;
                    input.addEventListener('input', function () {
                        clearTimeout(t);
                        t = setTimeout(run, debounceMs);
                    });
                    input.addEventListener('search', function () {
                        clearTimeout(t);
                        run();
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('form[data-listing-js-search-root], form[data-listing-js-training-table]').forEach(function (form) {
                        bindListingTitleSearchForm(form);
                    });
                    document.querySelectorAll('form[data-listing-js-search-root]').forEach(applyScopedListingTitleSearch);
                });
            })();
        </script>
    @endpush
@endonce
