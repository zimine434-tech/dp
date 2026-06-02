@extends($layout ?? (auth()->user()->role === 'teacher' ? 'layouts.teacher' : 'layouts.student'))

@section('title', 'Результаты соревнований')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Результаты соревнований</h1>
            </div>
            @if(auth()->user()->role === 'teacher')
            <div class="flex flex-wrap gap-2 justify-end">
                <form
                    action="{{ route('competitions.results.report') }}"
                    method="GET"
                    target="_blank"
                    class="inline-flex items-center gap-2"
                >
                    @php
                        $reportMonth = request()->query('month');
                        if (! is_string($reportMonth) || ! preg_match('/^\d{4}-\d{2}$/', $reportMonth)) {
                            $reportMonth = now()->format('Y-m');
                        }
                        $maxMonth = now()->format('Y-m');
                    @endphp
                    <input type="hidden" name="format" value="pdf" />
                    <input
                        type="month"
                        name="month"
                        value="{{ $reportMonth }}"
                        max="{{ $maxMonth }}"
                        class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 outline-none focus:border-blue-500 focus:ring-0"
                        aria-label="Месяц для отчета"
                    />
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium whitespace-nowrap"
                    >
                        PDF-отчет
                    </button>
                </form>
                <a
                    href="{{ route('competitions.photo-archive') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    <span class="hidden sm:inline">Архив соревнований</span>
                    <span class="sm:hidden">Архив</span>
                </a>
            </div>
            @endif
        </div>

        <!-- Сообщения об успехе -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @php
            $resultsView = $view ?? (auth()->user()->role === 'teacher' ? 'list' : 'cards');
            if (! in_array($resultsView, ['list', 'cards'], true)) {
                $resultsView = auth()->user()->role === 'teacher' ? 'list' : 'cards';
            }
        @endphp

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex h-full flex-col rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md">
                    <form
                        id="competitions-results-filters-form"
                        method="get"
                        action="{{ route('competitions.results') }}"
                        class="flex flex-1 flex-col justify-end"
                        data-ajax-listing-filters="1"
                    >
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="view" value="{{ $resultsView }}">
                        <input type="hidden" name="per_page" value="{{ (int) ($perPage ?? 25) }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end lg:flex-nowrap lg:gap-3 xl:gap-4">
                            <div class="min-w-0 w-full sm:min-w-[12rem] sm:flex-1 lg:w-52 lg:flex-none lg:shrink-0 xl:w-60">
                                <label for="competitions-results-filters-form-q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Поиск</label>
                                <input
                                    id="competitions-results-filters-form-q"
                                    type="search"
                                    name="q"
                                    value="{{ $q ?? '' }}"
                                    placeholder="Название соревнования"
                                    autocomplete="off"
                                    class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                                >
                            </div>
                            @php
                                $placeFilterOptions = collect($placesForResultsFilter ?? [])
                                    ->map(fn ($placeOption) => ['value' => (string) $placeOption, 'label' => (string) $placeOption])
                                    ->values()
                                    ->all();
                                if (auth()->user()->role === 'teacher') {
                                    array_unshift($placeFilterOptions, ['value' => '__none__', 'label' => 'Без места']);
                                }
                            @endphp
                            <div class="min-w-0 w-full sm:w-28 sm:shrink sm:min-w-[7rem] lg:w-32 lg:flex-none">
                                <label for="competitions-results-filters-form-place_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Место</label>
                                <x-filter-combobox
                                    name="place"
                                    :selected="$place ?? ''"
                                    :options="$placeFilterOptions"
                                    empty-label="Все места"
                                    input-id="competitions-results-filters-form-place"
                                    variant="filter"
                                />
                            </div>
                            <div class="min-w-0 w-full sm:w-44 sm:shrink sm:min-w-[8rem] lg:min-w-[7rem] lg:flex-1 lg:max-w-[12rem]">
                                <label for="competitions-results-sport_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Вид спорта</label>
                                <x-teacher-sport-combobox
                                    :sports="$sportsForResultsFilter ?? collect()"
                                    :selected="$sportId ?? null"
                                    name="sport_id"
                                    input-id="competitions-results-sport"
                                    empty-label="Все виды"
                                    variant="filter"
                                />
                            </div>
                            <div class="grid min-w-0 w-full grid-cols-2 gap-3 sm:flex sm:min-w-0 sm:shrink sm:gap-3 lg:flex-[0.9] lg:min-w-0 lg:max-w-[20rem]">
                                <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                                    <label for="competitions-results-filters-form-date-from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата с</label>
                                    <input
                                        id="competitions-results-filters-form-date-from"
                                        type="date"
                                        name="date_from"
                                        value="{{ $dateFrom ?? '' }}"
                                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                                    >
                                </div>
                                <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                                    <label for="competitions-results-filters-form-date-to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата по</label>
                                    <input
                                        id="competitions-results-filters-form-date-to"
                                        type="date"
                                        name="date_to"
                                        value="{{ $dateTo ?? '' }}"
                                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                                    >
                                </div>
                            </div>
                            <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                                <button type="submit" class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700 sm:flex-none">Применить</button>
                                <a href="{{ route('competitions.results') }}" data-competitions-results-ajax="1" class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none">Сбросить</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="flex shrink-0 flex-col">
                @include('competitions.partials.results-view-toolbar', ['view' => $resultsView])
            </div>
        </div>

        <div
            id="competitions-results-content"
            class="space-y-6 transition-opacity duration-150"
            role="region"
            aria-label="Результаты соревнований"
        >
        <!-- Завершенные соревнования (включая без мест) -->
        @php
            $finishedWithResults = $allFinishedCompetitionsForDisplay ?? collect();
            $finishedWithoutResults = $allFinishedCompetitionsWithoutResultsForDisplay ?? collect();
            $allFinishedCompetitionsMerged = $finishedWithResults
                ->concat($finishedWithoutResults)
                ->unique('id')
                ->sortByDesc('end_date')
                ->values();
            $finishedWithPlaces = $allFinishedCompetitionsMerged->filter(fn ($c) => $c->results->count() > 0);
            $finishedWithoutPlaces = $allFinishedCompetitionsMerged->filter(fn ($c) => $c->results->count() === 0);
        @endphp
        @if(auth()->user()->role === 'student' || $allFinishedCompetitionsMerged->count() > 0)
            <div>
                @if(auth()->user()->role === 'student')
                    @if($hasSearchFilters && $finishedWithPlaces->count() === 0)
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
                            По заданным условиям поиска и фильтров ничего не найдено. <a href="{{ route('competitions.results') }}" data-competitions-results-ajax="1" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>.
                        </div>
                    @elseif($finishedWithPlaces->count() > 0)
                        <div data-results-list-wrap class="{{ $resultsView === 'cards' ? 'hidden' : '' }}">
                            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Соревнование</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden sm:table-cell">Спорт</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Место</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden md:table-cell">Даты</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($finishedWithPlaces as $competition)
                                            @php
                                                $sortedResults = $competition->results->sortBy(function ($r) {
                                                    if (is_numeric($r->place)) {
                                                        return (int) $r->place;
                                                    }
                                                    return 9999 + ord($r->place[0] ?? 'z');
                                                })->values();
                                            @endphp
                                            @foreach($sortedResults as $result)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $competition->name }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-700 hidden sm:table-cell">{{ $competition->sport?->name ?? '—' }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $result->place }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">
                                                        {{ $competition->start_date->format('d.m.Y') }} – {{ $competition->end_date->format('d.m.Y') }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right text-sm">
                                                        <a href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}" class="font-medium text-blue-600 hover:text-blue-800">Подробнее</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div data-results-cards-wrap class="{{ $resultsView === 'list' ? 'hidden' : '' }}">
                            @include('competitions.partials.results-cards-grid', [
                                'competitionsWithResults' => $finishedWithPlaces,
                                'competitionShowQuery' => ['from' => 'results'],
                            ])
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
                            Нет завершённых соревнований с распределёнными местами.
                        </div>
                    @endif
                @else
                @if(auth()->user()->role === 'teacher')
                    @if($hasSearchFilters && $finishedWithPlaces->count() === 0 && $finishedWithoutPlaces->count() === 0)
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600 mb-4">
                            По заданным условиям поиска и фильтров ничего не найдено. <a href="{{ route('competitions.results') }}" data-competitions-results-ajax="1" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>.
                        </div>
                    @endif
                @endif
                @if(auth()->user()->role === 'teacher' && ($finishedWithPlaces->count() > 0 || $finishedWithoutPlaces->count() > 0))
                <div data-results-list-wrap class="{{ $resultsView === 'cards' ? 'hidden' : '' }}">
                <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Соревнование</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Спорт</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Даты</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Категория</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участники</th>
                                @if(auth()->user()->role === 'teacher')
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="finished-competitions-container">
                            @foreach($finishedWithPlaces as $competition)
                                @php
                                    $sortedResults = $competition->results->sortBy(function($r) {
                                        if (is_numeric($r->place)) {
                                            return (int) $r->place;
                                        }
                                        return 9999 + ord($r->place[0] ?? 'z');
                                    })->values();
                                    $rowspan = max(1, $sortedResults->count());
                                @endphp

                                @if($sortedResults->count() > 0)
                                    @foreach($sortedResults as $result)
                                        <tr class="competition-row"
                                            data-start-date="{{ $competition->start_date->format('Y-m-d') }}"
                                            data-end-date="{{ $competition->end_date->format('Y-m-d') }}"
                                            data-sport-id="{{ $competition->sport->id }}"
                                            data-name="{{ mb_strtolower($competition->name) }}"
                                        >
                                            @if($loop->first)
                                                <td class="px-4 py-3 align-top" rowspan="{{ $rowspan }}">
                                                    <div class="font-semibold text-gray-900">
                                                        <a href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}" class="hover:text-blue-600 transition">
                                                            {{ $competition->name }}
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500 mt-1 lg:hidden">
                                                        {{ $competition->sport?->name ?? '—' }} • {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 hidden lg:table-cell align-top" rowspan="{{ $rowspan }}">
                                                    {{ $competition->sport?->name ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top" rowspan="{{ $rowspan }}">
                                                    {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top" rowspan="{{ $rowspan }}">
                                                    {{ $competition->category->name_category ?? 'Не указана' }}
                                                </td>
                                            @endif

                                            <td class="px-4 py-3 align-top">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if(is_numeric($result->place) && (int) $result->place === 1) bg-yellow-100 text-yellow-800
                                                    @elseif(is_numeric($result->place) && (int) $result->place === 2) bg-gray-200 text-gray-800
                                                    @elseif(is_numeric($result->place) && (int) $result->place === 3) bg-orange-100 text-orange-800
                                                    @else bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ $result->place }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                                <div class="flex items-center min-h-[32px]">
                                                    <a
                                                        href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}"
                                                        class="inline-flex items-center text-blue-600 hover:text-blue-900 py-1 rounded hover:bg-blue-50 transition"
                                                    >
                                                        Список участников
                                                    </a>
                                                </div>
                                            </td>
                                            @if(auth()->user()->role === 'teacher')
                                                <td class="px-3 sm:px-4 py-3 text-right text-sm font-medium">
                                                    <div class="flex flex-col sm:flex-row sm:flex-wrap items-end sm:items-center justify-end gap-1 sm:gap-2">
                                                        @if($loop->first)
                                                            <a
                                                                href="{{ route('competitions.photos', $competition) }}"
                                                                class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded hover:bg-indigo-50 transition whitespace-nowrap"
                                                            >
                                                                Добавить фотографии
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach

                            <!-- Разделитель: соревнования без мест -->
                            <tr id="finished-without-places-divider" class="{{ $finishedWithoutPlaces->count() > 0 ? '' : 'hidden' }}">
                                <td colspan="{{ auth()->user()->role === 'teacher' ? 7 : 6 }}" class="px-4 py-3 bg-gray-50 text-sm font-semibold text-gray-700">
                                    Соревнования без мест
                                </td>
                            </tr>

                            @foreach($finishedWithoutPlaces as $competition)
                                <tr class="competition-row" data-group="without"
                                    data-start-date="{{ $competition->start_date->format('Y-m-d') }}"
                                    data-end-date="{{ $competition->end_date->format('Y-m-d') }}"
                                    data-sport-id="{{ $competition->sport->id }}"
                                    data-name="{{ mb_strtolower($competition->name) }}"
                                >
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-semibold text-gray-900">
                                            <a href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}" class="hover:text-blue-600 transition">
                                                {{ $competition->name }}
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1 lg:hidden">
                                            {{ $competition->sport?->name ?? '—' }} • {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 hidden lg:table-cell align-top">
                                        {{ $competition->sport?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top">
                                        {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top">
                                        {{ $competition->category->name_category ?? 'Не указана' }}
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Нет места
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap align-middle">
                                        <div class="flex items-center min-h-[32px]">
                                            <a
                                                href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}"
                                                class="inline-flex items-center text-blue-600 hover:text-blue-900 py-1 rounded hover:bg-blue-50 transition"
                                            >
                                                Список участников
                                            </a>
                                        </div>
                                    </td>
                                    @if(auth()->user()->role === 'teacher')
                                        <td class="px-3 sm:px-4 py-3 text-right text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row sm:flex-wrap items-end sm:items-center justify-end gap-1 sm:gap-2">
                                                @include('competitions.partials.add-result-action', ['competition' => $competition])
                                                <a
                                                    href="{{ route('competitions.photos', $competition) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded hover:bg-indigo-50 transition whitespace-nowrap"
                                                >
                                                    Добавить фотографии
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                </div>
                <div data-results-cards-wrap class="{{ $resultsView === 'list' ? 'hidden' : '' }}">
                    @include('competitions.partials.results-cards-grid', [
                        'competitionsWithResults' => $finishedWithPlaces,
                        'competitionsWithoutResults' => $finishedWithoutPlaces,
                        'competitionShowQuery' => ['from' => 'results'],
                    ])
                </div>
                @endif
                @endif
            </div>
        @endif

        @php
            $finishedPaginatorInstance = $finishedPaginator ?? null;
            $canRenderFinishedPagination = $finishedPaginatorInstance
                && method_exists($finishedPaginatorInstance, 'total')
                && (int) $finishedPaginatorInstance->total() > 0;
        @endphp
        @if($canRenderFinishedPagination)
            <div id="competitions-results-pagination" class="flex justify-end pt-2">
                <div class="mr-auto flex items-center gap-2">
                    <label for="competitions-results-per-page-bottom_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                    <x-per-page-combobox :selected="(int)($perPage ?? 25)" input-id="competitions-results-per-page-bottom" />
                </div>
                @if($finishedPaginatorInstance->hasPages())
                    {{ $finishedPaginatorInstance->links() }}
                @endif
            </div>
        @endif

        <!-- Текущие соревнования с результатами (только для преподавателей) -->
        @if(auth()->user()->role === 'teacher' && isset($allOngoingCompetitionsForDisplay) && $allOngoingCompetitionsForDisplay->count() > 0)
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Текущие соревнования</h2>
                @php
                    $ongoingWithResults = $allOngoingCompetitionsForDisplay->filter(fn ($c) => $c->results->count() > 0);
                    $ongoingWithoutResults = $allOngoingCompetitionsForDisplay->filter(fn ($c) => $c->results->count() === 0);
                @endphp
                <div data-results-list-wrap class="{{ $resultsView === 'cards' ? 'hidden' : '' }}">
                <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Соревнование</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Спорт</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Даты</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Категория</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Место</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участники</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($allOngoingCompetitionsForDisplay as $competition)
                                @php
                                    $sortedResults = $competition->results->sortBy(function($r) {
                                        if (is_numeric($r->place)) {
                                            return (int) $r->place;
                                        }
                                        return 9999 + ord($r->place[0] ?? 'z');
                                    })->values();
                                    $rowspan = max(1, $sortedResults->count());
                                @endphp

                                @if($sortedResults->count() > 0)
                                    @foreach($sortedResults as $result)
                                        <tr>
                                            @if($loop->first)
                                                <td class="px-4 py-3 align-top" rowspan="{{ $rowspan }}">
                                                    <div class="font-semibold text-gray-900">
                                                        <a href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}" class="hover:text-blue-600 transition">
                                                            {{ $competition->name }}
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500 mt-1 lg:hidden">
                                                        {{ $competition->sport?->name ?? '—' }} • {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 hidden lg:table-cell align-top" rowspan="{{ $rowspan }}">
                                                    {{ $competition->sport?->name ?? '—' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top" rowspan="{{ $rowspan }}">
                                                    {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top" rowspan="{{ $rowspan }}">
                                                    {{ $competition->category->name_category ?? 'Не указана' }}
                                                </td>
                                            @endif

                                            <td class="px-4 py-3 align-top">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if(is_numeric($result->place) && (int) $result->place === 1) bg-yellow-100 text-yellow-800
                                                    @elseif(is_numeric($result->place) && (int) $result->place === 2) bg-gray-200 text-gray-800
                                                    @elseif(is_numeric($result->place) && (int) $result->place === 3) bg-orange-100 text-orange-800
                                                    @else bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ $result->place }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 whitespace-nowrap align-middle">
                                                <div class="flex items-center min-h-[32px]">
                                                    <a
                                                        href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}"
                                                        class="inline-flex items-center text-blue-600 hover:text-blue-900 py-1 rounded hover:bg-blue-50 transition"
                                                    >
                                                        Список участников
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="px-3 sm:px-4 py-3 text-right text-sm font-medium">
                                                <div class="flex flex-col sm:flex-row sm:flex-wrap items-end sm:items-center justify-end gap-1 sm:gap-2">
                                                    @if($loop->first)
                                                        <a
                                                            href="{{ route('competitions.photos', $competition) }}"
                                                            class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded hover:bg-indigo-50 transition whitespace-nowrap"
                                                        >
                                                            Добавить фотографии
                                                        </a>
                                                    @endif
                                                    <a
                                                        href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}"
                                                        class="text-gray-700 hover:text-gray-900 px-3 py-1 rounded hover:bg-gray-100 transition"
                                                    >
                                                        Подробнее
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-semibold text-gray-900">
                                                <a href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}" class="hover:text-blue-600 transition">
                                                    {{ $competition->name }}
                                                </a>
                                            </div>
                                            <div class="text-sm text-gray-500 mt-1 lg:hidden">
                                                {{ $competition->sport?->name ?? '—' }} • {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 hidden lg:table-cell align-top">
                                            {{ $competition->sport?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top">
                                            {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 hidden xl:table-cell align-top">
                                            {{ $competition->category->name_category ?? 'Не указана' }}
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Нет места
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap align-middle">
                                            <div class="flex items-center min-h-[32px]">
                                                <a
                                                    href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}"
                                                    class="inline-flex items-center text-blue-600 hover:text-blue-900 py-1 rounded hover:bg-blue-50 transition"
                                                >
                                                    Список участников
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 text-right text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row sm:flex-wrap items-end sm:items-center justify-end gap-1 sm:gap-2">
                                                @include('competitions.partials.add-result-action', ['competition' => $competition])
                                                <a
                                                    href="{{ route('competitions.photos', $competition) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded hover:bg-indigo-50 transition whitespace-nowrap"
                                                >
                                                    Добавить фотографии
                                                </a>
                                                <a
                                                    href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results']) }}"
                                                    class="text-gray-700 hover:text-gray-900 px-3 py-1 rounded hover:bg-gray-100 transition"
                                                >
                                                    Подробнее
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div data-results-cards-wrap class="{{ $resultsView === 'list' ? 'hidden' : '' }}">
                    @include('competitions.partials.results-cards-grid', [
                        'competitionsWithResults' => $ongoingWithResults,
                        'competitionsWithoutResults' => $ongoingWithoutResults,
                        'competitionShowQuery' => ['from' => 'results'],
                    ])
                </div>

                @php
                    $ongoingPaginatorInstance = $ongoingPaginator ?? null;
                    $canRenderOngoingPagination = $ongoingPaginatorInstance
                        && method_exists($ongoingPaginatorInstance, 'total')
                        && (int) $ongoingPaginatorInstance->total() > 0;
                @endphp
                @if($canRenderOngoingPagination)
                    <div id="competitions-results-ongoing-pagination" class="flex justify-end pt-2">
                        <div class="mr-auto flex items-center gap-2">
                            <label for="competitions-results-per-page-bottom-ongoing_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                            <x-per-page-combobox :selected="(int)($perPage ?? 25)" input-id="competitions-results-per-page-bottom-ongoing" />
                        </div>
                        @if($ongoingPaginatorInstance->hasPages())
                            {{ $ongoingPaginatorInstance->appends(request()->except('ongoing_page'))->links() }}
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Пустое состояние -->
        @php
            $hasOngoing = auth()->user()->role === 'teacher' && isset($allOngoingCompetitionsForDisplay) && $allOngoingCompetitionsForDisplay->count() > 0;
            $hasFinished = isset($allFinishedCompetitionsMerged) && $allFinishedCompetitionsMerged->count() > 0;
        @endphp
        @if(!$hasOngoing && !$hasFinished)
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет результатов</h3>
                <p class="mt-1 text-sm text-gray-500">Результаты соревнований появятся здесь после их добавления.</p>
            </div>
        @endif
        </div>

    </div>
@endsection

@push('scripts')
<script>
(function () {
    const VIEW_STORAGE_KEY = 'competitions_results_view';
    const PER_PAGE_STORAGE_KEY = 'competitions_results_per_page';
    const form = document.getElementById('competitions-results-filters-form');

    function getStoredViewMode() {
        try {
            return localStorage.getItem(VIEW_STORAGE_KEY) === 'cards' ? 'cards' : 'list';
        } catch (e) {
            return 'list';
        }
    }

    function persistViewMode(mode) {
        try {
            localStorage.setItem(VIEW_STORAGE_KEY, mode === 'cards' ? 'cards' : 'list');
        } catch (e) {}
    }

    function syncViewToForm(mode) {
        const el = form && form.elements.namedItem('view');
        if (el) {
            el.value = mode === 'cards' ? 'cards' : 'list';
        }
    }

    function getServerViewMode() {
        const el = form && form.elements.namedItem('view');
        return el && el.value === 'cards' ? 'cards' : 'list';
    }

    function applyResultsViewMode(mode) {
        const isCards = mode === 'cards';
        document.querySelectorAll('[data-results-list-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', isCards);
        });
        document.querySelectorAll('[data-results-cards-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', !isCards);
        });
        const btnList = document.getElementById('competitions-results-view-list');
        const btnCards = document.getElementById('competitions-results-view-cards');
        if (btnList && btnCards) {
            btnList.setAttribute('aria-selected', !isCards ? 'true' : 'false');
            btnCards.setAttribute('aria-selected', isCards ? 'true' : 'false');
            btnList.classList.toggle('bg-white', !isCards);
            btnList.classList.toggle('shadow-sm', !isCards);
            btnList.classList.toggle('text-gray-900', !isCards);
            btnList.classList.toggle('text-gray-600', isCards);
            btnCards.classList.toggle('bg-white', isCards);
            btnCards.classList.toggle('shadow-sm', isCards);
            btnCards.classList.toggle('text-gray-900', isCards);
            btnCards.classList.toggle('text-gray-600', !isCards);
        }
    }

    function updateResultsViewInUrl(mode) {
        const url = new URL(window.location.href);
        if (mode === 'list') {
            url.searchParams.delete('view');
        } else {
            url.searchParams.set('view', 'cards');
        }
        const path = url.pathname + (url.search ? url.search : '');
        if (window.location.pathname + window.location.search !== path) {
            history.replaceState(null, '', path);
        }
    }

    if (!form || !form.dataset.ajaxListingFilters) {
        applyResultsViewMode(getServerViewMode());
        return;
    }

    const input = form.querySelector('input[type="search"][name="q"]');
    const resultsPath = new URL(form.action, window.location.origin).pathname;
    let debounceTimer = null;
    let abortController = null;

    function isResultsHref(href) {
        try {
            const u = new URL(href, window.location.origin);
            return u.origin === window.location.origin && u.pathname === resultsPath;
        } catch (e) {
            return false;
        }
    }

    function buildResultsUrl() {
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));
        params.forEach(function (value, key) {
            if (value === '') params.delete(key);
        });
        if (params.get('view') === 'list') {
            params.delete('view');
        }
        url.search = params.toString();
        return url;
    }

    function applyQueryToForm(params) {
        const viewEl = form.elements.namedItem('view');
        if (viewEl) {
            viewEl.value = params.get('view') === 'cards' ? 'cards' : 'list';
        }
        ['q', 'sport_id', 'date_from', 'date_to', 'place', 'per_page'].forEach(function (name) {
            const el = form.elements.namedItem(name);
            if (!el) return;
            el.value = params.has(name) ? params.get(name) : '';
        });
    }

    async function refreshResults(targetUrl) {
        const url = targetUrl || buildResultsUrl();
        const resultsEl = document.getElementById('competitions-results-content');
        if (!resultsEl) return;

        if (abortController) abortController.abort();
        abortController = new AbortController();

        resultsEl.classList.add('opacity-60', 'pointer-events-none');
        resultsEl.setAttribute('aria-busy', 'true');

        try {
            const res = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                signal: abortController.signal,
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error(String(res.status));

            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextResults = doc.getElementById('competitions-results-content');
            if (!nextResults) return;

            resultsEl.replaceWith(document.importNode(nextResults, true));
            applyQueryToForm(url.searchParams);
            document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
            applyResultsViewMode(getServerViewMode());

            const path = url.pathname + (url.search ? url.search : '');
            if (window.location.pathname + window.location.search !== path) {
                history.replaceState(null, '', path);
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
        } finally {
            const el = document.getElementById('competitions-results-content');
            if (el) {
                el.classList.remove('opacity-60', 'pointer-events-none');
                el.removeAttribute('aria-busy');
            }
        }
    }

    function scheduleDebounced() {
        clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () {
            debounceTimer = null;
            refreshResults(buildResultsUrl());
        }, 320);
    }

    function scheduleNow() {
        clearTimeout(debounceTimer);
        debounceTimer = null;
        refreshResults(buildResultsUrl());
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;

        const viewToggle = e.target.closest('#competitions-results-view-toolbar .competitions-results-view-toggle');
        if (viewToggle) {
            const mode = viewToggle.getAttribute('data-view');
            if (mode !== 'list' && mode !== 'cards') return;
            e.preventDefault();
            persistViewMode(mode);
            syncViewToForm(mode);
            applyResultsViewMode(mode);
            updateResultsViewInUrl(mode);
            return;
        }

        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const a = e.target.closest('a');
        if (!a || !a.href || !isResultsHref(a.href)) return;
        if (a.hasAttribute('data-turbo')) return;
        if (a.hasAttribute('data-competitions-results-ajax') || a.closest('#competitions-results-pagination') || a.closest('#competitions-results-ongoing-pagination')) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            debounceTimer = null;
            refreshResults(new URL(a.href, window.location.origin));
        }
    });

    const perPageHidden = form.elements.namedItem('per_page');
    const perPageBottom = document.getElementById('competitions-results-per-page-bottom');
    const perPageBottomOngoing = document.getElementById('competitions-results-per-page-bottom-ongoing');

    if (perPageHidden) {
        try {
            const u = new URL(window.location.href);
            if (!u.searchParams.get('per_page')) {
                const stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
                if (stored && stored !== perPageHidden.value) {
                    perPageHidden.value = stored;
                    if (perPageBottom) perPageBottom.value = stored;
                    if (perPageBottomOngoing) perPageBottomOngoing.value = stored;
                }
            } else if (perPageBottom) {
                perPageBottom.value = u.searchParams.get('per_page');
                if (perPageBottomOngoing) perPageBottomOngoing.value = u.searchParams.get('per_page');
            }
        } catch (e) {}
    }

    if (perPageBottom && perPageHidden) {
        perPageBottom.addEventListener('change', function () {
            perPageHidden.value = String(perPageBottom.value || '25');
            try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(perPageHidden.value || '25')); } catch (e) {}
            scheduleNow();
        });
    }

    if (perPageBottomOngoing && perPageHidden) {
        perPageBottomOngoing.addEventListener('change', function () {
            perPageHidden.value = String(perPageBottomOngoing.value || '25');
            if (perPageBottom) perPageBottom.value = perPageHidden.value;
            try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(perPageHidden.value || '25')); } catch (e) {}
            scheduleNow();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        scheduleNow();
    });

    const serverView = getServerViewMode();
    const storedView = getStoredViewMode();
    if (storedView !== serverView) {
        syncViewToForm(storedView);
        applyResultsViewMode(storedView);
        updateResultsViewInUrl(storedView);
    } else {
        persistViewMode(serverView);
        applyResultsViewMode(serverView);
    }
})();
</script>
@endpush
