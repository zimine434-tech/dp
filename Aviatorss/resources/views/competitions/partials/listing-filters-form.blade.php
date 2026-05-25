@props([
    'action',
    'resetUrl',
    'q' => '',
    'dateFrom' => null,
    'dateTo' => null,
    'sportId' => null,
    'place' => '',
    'places' => null,
    'showPlaceFilter' => false,
    'showNoPlaceFilter' => false,
    'sports',
    'formId' => 'competitions-filters-form',
    'sportInputId' => 'competitions-sport',
    'liveFilters' => false,
    'liveAjax' => false,
    'hiddenFields' => [],
    'searchPlaceholder' => 'Название соревнования',
    'ajaxResetDataAttribute' => null,
])

<div class="flex h-full flex-col rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md">
    <form
        id="{{ $formId }}"
        method="get"
        action="{{ $action }}"
        class="flex flex-1 flex-col justify-end"
        @if($liveFilters) data-live-filters="1" @endif
    >
        @foreach($hiddenFields as $hiddenName => $hiddenValue)
            <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenValue }}">
        @endforeach
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end lg:flex-nowrap lg:gap-3 xl:gap-4">
            <div class="min-w-0 w-full sm:min-w-[12rem] sm:flex-1 lg:w-52 lg:flex-none lg:shrink-0 xl:w-60">
                <label for="{{ $formId }}-q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Поиск</label>
                <input
                    id="{{ $formId }}-q"
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ $searchPlaceholder }}"
                    autocomplete="off"
                    class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                >
            </div>
            @if($showPlaceFilter)
                <div class="min-w-0 w-full sm:w-28 sm:shrink sm:min-w-[7rem] lg:w-32 lg:flex-none">
                    <label for="{{ $formId }}-place" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Место</label>
                    <select
                        id="{{ $formId }}-place"
                        name="place"
                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                    >
                        <option value="" @selected(($place ?? '') === '')>Все места</option>
                        @if($showNoPlaceFilter)
                            <option value="__none__" @selected(($place ?? '') === '__none__')>Без места</option>
                        @endif
                        @foreach(($places ?? collect()) as $placeOption)
                            <option value="{{ $placeOption }}" @selected((string) ($place ?? '') === (string) $placeOption)>{{ $placeOption }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="min-w-0 w-full sm:w-44 sm:shrink sm:min-w-[8rem] lg:min-w-[7rem] lg:flex-1 lg:max-w-[12rem]">
                <label for="{{ $sportInputId }}_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Вид спорта</label>
                <x-teacher-sport-combobox
                    :sports="$sports"
                    :selected="$sportId"
                    name="sport_id"
                    :input-id="$sportInputId"
                    empty-label="Все виды"
                    variant="filter"
                />
            </div>
            <div class="grid min-w-0 w-full grid-cols-2 gap-3 sm:flex sm:min-w-0 sm:shrink sm:gap-3 lg:flex-[0.9] lg:min-w-0 lg:max-w-[20rem]">
                <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                    <label for="{{ $formId }}-date-from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата с</label>
                    <input
                        id="{{ $formId }}-date-from"
                        type="date"
                        name="date_from"
                        value="{{ $dateFrom }}"
                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                    >
                </div>
                <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                    <label for="{{ $formId }}-date-to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата по</label>
                    <input
                        id="{{ $formId }}-date-to"
                        type="date"
                        name="date_to"
                        value="{{ $dateTo }}"
                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                    >
                </div>
            </div>
            <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                @unless($liveFilters)
                    <button type="submit" class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700 sm:flex-none">
                        Применить
                    </button>
                @endunless
                <a
                    href="{{ $resetUrl }}"
                    @if($liveAjax && filled($ajaxResetDataAttribute))
                        {{ $ajaxResetDataAttribute }}="1"
                    @elseif($liveAjax)
                        data-competitions-results-ajax="1"
                    @endif
                    class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none"
                >
                    Сбросить
                </a>
            </div>
        </div>
    </form>
</div>
