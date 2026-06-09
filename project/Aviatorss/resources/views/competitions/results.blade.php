@extends($layout ?? (auth()->user()->role === 'teacher' ? 'layouts.teacher' : 'layouts.student'))

@section('title', 'Результаты соревнований')

@push('styles')
<style>
    .results-listing-table tbody tr.results-listing-row:hover > td {
        background-color: #f9fafb;
    }
</style>
@endpush

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
            use App\Support\StudentCompetitionListingSort;

            $resultsView = $view ?? (auth()->user()->role === 'teacher' ? 'list' : 'cards');
            if (! in_array($resultsView, ['list', 'cards'], true)) {
                $resultsView = auth()->user()->role === 'teacher' ? 'list' : 'cards';
            }
            $cardsSortStack = $cardsSortStack ?? StudentCompetitionListingSort::defaultStack();
            $listSortStack = $listSortStack ?? StudentCompetitionListingSort::defaultListStack();
            $categoryFilterOptions = collect($categoriesForResultsFilter ?? [])
                ->map(fn ($category) => ['value' => (string) $category->id, 'label' => (string) $category->name_category])
                ->values()
                ->all();
            $resultsBaseParams = array_filter([
                'q' => filled($q ?? null) ? $q : null,
                'sport_id' => $sportId ?? null,
                'competition_category_id' => $categoryId ?? null,
                'date_from' => $dateFrom ?? null,
                'date_to' => $dateTo ?? null,
                'place' => filled($place ?? null) ? $place : null,
                'view' => $resultsView === 'list' ? 'list' : null,
                'per_page' => ($perPage ?? 25) !== 25 ? (string) ($perPage ?? 25) : null,
            ], fn ($v) => $v !== null && $v !== '');
            $resetResultsUrl = StudentCompetitionListingSort::listingUrl(
                'competitions.results',
                [],
                $cardsSortStack,
                $listSortStack,
                ['page' => 1, 'ongoing_page' => 1]
            );
            $resultsSortAjaxAttr = 'data-competitions-results-ajax';
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
                            <div class="min-w-0 w-full sm:w-44 sm:shrink sm:min-w-[8rem] lg:min-w-[7rem] lg:flex-1 lg:max-w-[12rem]">
                                <label for="competitions-results-category_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Категория</label>
                                <x-filter-combobox
                                    name="competition_category_id"
                                    :selected="$categoryId ?? ''"
                                    :options="$categoryFilterOptions"
                                    empty-label="Все категории"
                                    input-id="competitions-results-category"
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
                                <a href="{{ $resetResultsUrl }}" data-competitions-results-ajax="1" class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none">Сбросить</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="competitions-results-sort-hidden-inputs" class="hidden" aria-hidden="true">
                    @include('competitions.student.partials.sort-hidden-inputs', compact('cardsSortStack', 'listSortStack'))
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
        @include('competitions.partials.results-sort-tools', [
            'resultsView' => $resultsView,
            'cardsSortStack' => $cardsSortStack,
            'listSortStack' => $listSortStack,
            'resultsBaseParams' => $resultsBaseParams,
        ])

        <!-- Завершенные соревнования (включая без мест) -->
        @php
            $finishedWithResults = $allFinishedCompetitionsForDisplay ?? collect();
            $finishedWithoutResults = $allFinishedCompetitionsWithoutResultsForDisplay ?? collect();
            $allFinishedCompetitionsMerged = $finishedWithResults
                ->concat($finishedWithoutResults)
                ->unique('id')
                ->values();
            $finishedResultsListingItems = $finishedResultsListingItems ?? collect();
            $finishedWithoutPlacesPage = $allFinishedCompetitionsWithoutResultsForDisplay ?? collect();
            $hasFinishedWithPlaces = $finishedResultsListingItems->count() > 0;
            $resultsParticipantColumnLabel = $finishedResultsListingItems->contains(
                fn ($item) => ($item['competition'] ?? null)?->isPersonalCompetition()
            ) ? 'Участник' : 'Участники';
        @endphp
        @php
            $isStudentResultsViewer = auth()->user()->role === 'student';
            $hasFinishedWithoutPlaces = ! $isStudentResultsViewer && $finishedWithoutPlacesPage->count() > 0;
        @endphp
        @if($isStudentResultsViewer || $allFinishedCompetitionsMerged->count() > 0)
            <div>
                @if($hasSearchFilters && ! $hasFinishedWithPlaces && ! $hasFinishedWithoutPlaces)
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600 mb-4">
                        По заданным условиям поиска и фильтров ничего не найдено. <a href="{{ route('competitions.results') }}" data-competitions-results-ajax="1" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>.
                    </div>
                @elseif($isStudentResultsViewer && ! $hasFinishedWithPlaces)
                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
                        Нет завершённых соревнований с распределёнными местами.
                    </div>
                @endif
                @if($hasFinishedWithPlaces || $hasFinishedWithoutPlaces)
                <div data-results-list-wrap class="{{ $resultsView === 'cards' ? 'hidden' : '' }}">
                <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                    <table class="results-listing-table min-w-full border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    @include('competitions.student.partials.table-sort-header', [
                                        'listingRoute' => 'competitions.results',
                                        'baseListingParams' => $resultsBaseParams,
                                        'cardsSortStack' => $cardsSortStack,
                                        'listSortStack' => $listSortStack,
                                        'field' => 'name',
                                        'label' => 'Соревнование',
                                        'defaultOrder' => 'asc',
                                        'listingAjaxAttr' => $resultsSortAjaxAttr,
                                        'sortLinkExtra' => ['page' => 1, 'ongoing_page' => 1],
                                    ])
                                </th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 lg:table-cell">Спорт</th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 xl:table-cell">
                                    @include('competitions.student.partials.table-sort-header', [
                                        'listingRoute' => 'competitions.results',
                                        'baseListingParams' => $resultsBaseParams,
                                        'cardsSortStack' => $cardsSortStack,
                                        'listSortStack' => $listSortStack,
                                        'field' => 'start_date',
                                        'label' => 'Даты',
                                        'defaultOrder' => 'desc',
                                        'listingAjaxAttr' => $resultsSortAjaxAttr,
                                        'sortLinkExtra' => ['page' => 1, 'ongoing_page' => 1],
                                    ])
                                </th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 xl:table-cell">Категория</th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 xl:table-cell">Вид участия</th>
                                <th class="border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Место</th>
                                <th class="border-b border-gray-300 pl-6 pr-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ $resultsParticipantColumnLabel ?? 'Участники' }}</th>
                                @if(auth()->user()->role === 'teacher')
                                    <th class="border-b border-gray-300 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Действия</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white" id="finished-competitions-container">
                            @foreach($finishedResultsListingItems as $listingItem)
                                @include('competitions.partials.results-list-row', [
                                    'competition' => $listingItem['competition'],
                                    'result' => $listingItem['result'],
                                ])
                            @endforeach

                            <!-- Разделитель: соревнования без мест -->
                            <tr id="finished-without-places-divider" class="{{ $finishedWithoutPlacesPage->count() > 0 ? '' : 'hidden' }}">
                                <td colspan="{{ auth()->user()->role === 'teacher' ? 8 : 7 }}" class="px-4 py-3 bg-gray-50 text-sm font-semibold text-gray-700">
                                    Соревнования без мест
                                </td>
                            </tr>

                            @foreach($finishedWithoutPlacesPage as $competition)
                                @include('competitions.partials.results-list-row', [
                                    'competition' => $competition,
                                    'result' => null,
                                    'rowGroup' => 'without',
                                ])
                            @endforeach

                        </tbody>
                    </table>
                </div>
                </div>
                <div data-results-cards-wrap class="{{ $resultsView === 'list' ? 'hidden' : '' }}">
                    @include('competitions.partials.results-cards-grid', [
                        'competitionsWithResults' => $allFinishedCompetitionsForDisplay,
                        'competitionsWithoutResults' => $finishedWithoutPlacesPage,
                        'competitionShowQuery' => ['from' => 'results'],
                    ])
                </div>
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
                    <table class="results-listing-table min-w-full border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    @include('competitions.student.partials.table-sort-header', [
                                        'listingRoute' => 'competitions.results',
                                        'baseListingParams' => $resultsBaseParams,
                                        'cardsSortStack' => $cardsSortStack,
                                        'listSortStack' => $listSortStack,
                                        'field' => 'name',
                                        'label' => 'Соревнование',
                                        'defaultOrder' => 'asc',
                                        'listingAjaxAttr' => $resultsSortAjaxAttr,
                                        'sortLinkExtra' => ['page' => 1, 'ongoing_page' => 1],
                                    ])
                                </th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 lg:table-cell">Спорт</th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 xl:table-cell">
                                    @include('competitions.student.partials.table-sort-header', [
                                        'listingRoute' => 'competitions.results',
                                        'baseListingParams' => $resultsBaseParams,
                                        'cardsSortStack' => $cardsSortStack,
                                        'listSortStack' => $listSortStack,
                                        'field' => 'start_date',
                                        'label' => 'Даты',
                                        'defaultOrder' => 'desc',
                                        'listingAjaxAttr' => $resultsSortAjaxAttr,
                                        'sortLinkExtra' => ['page' => 1, 'ongoing_page' => 1],
                                    ])
                                </th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 xl:table-cell">Категория</th>
                                <th class="hidden border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 xl:table-cell">Вид участия</th>
                                <th class="border-b border-gray-300 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Место</th>
                                <th class="border-b border-gray-300 pl-6 pr-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Участники</th>
                                <th class="border-b border-gray-300 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($allOngoingCompetitionsForDisplay as $competition)
                                @php
                                    $sortedResults = \App\Support\CompetitionResultPage::sortedResultsForListing($competition);
                                @endphp

                                @if($sortedResults->count() > 0)
                                    @foreach($sortedResults as $result)
                                        @include('competitions.partials.results-list-row', [
                                            'competition' => $competition,
                                            'result' => $result,
                                            'showDetailLink' => true,
                                            'linkNameToCompetitionShow' => true,
                                            'competitionShowQuery' => ['from' => 'results'],
                                        ])
                                    @endforeach
                                @else
                                    @include('competitions.partials.results-list-row', [
                                        'competition' => $competition,
                                        'result' => null,
                                        'showDetailLink' => true,
                                    ])
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
    const SORT_STORAGE_KEY = 'competitions_results_sort';
    const sortHiddenWrapId = 'competitions-results-sort-hidden-inputs';
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
        document.querySelectorAll('[data-results-cards-sort-wrap]').forEach(function (el) {
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

    function getSortWrap() {
        return document.getElementById(sortHiddenWrapId);
    }

    function removeSortParams(params) {
        ['cards_sort', 'cards_order', 'list_sort', 'list_order'].forEach(function (key) {
            while (params.has(key)) {
                params.delete(key);
            }
        });
    }

    function appendSortParams(params) {
        const wrap = getSortWrap();
        if (!wrap) {
            return;
        }
        removeSortParams(params);
        wrap.querySelectorAll('input[name]').forEach(function (input) {
            if (!input.name) {
                return;
            }
            if (input.name.endsWith('[]')) {
                params.append(input.name, input.value);
            } else {
                params.set(input.name, input.value);
            }
        });
    }

    function replaceSortHiddenInputs(doc) {
        const nextWrap = doc.getElementById(sortHiddenWrapId);
        const liveWrap = getSortWrap();
        if (nextWrap && liveWrap) {
            liveWrap.replaceWith(document.importNode(nextWrap, true));
        }
    }

    function readStacksFromWrap() {
        const wrap = getSortWrap();
        if (!wrap) {
            return { cards: null, list: null };
        }

        function readPrefix(prefix) {
            const scalar = wrap.querySelector('input[name="' + prefix + '_sort"]');
            if (scalar && scalar.value === 'none') {
                return [];
            }
            const fields = Array.from(wrap.querySelectorAll('input[name="' + prefix + '_sort[]"]')).map(function (el) {
                return el.value;
            });
            const orders = Array.from(wrap.querySelectorAll('input[name="' + prefix + '_order[]"]')).map(function (el) {
                return el.value;
            });
            if (fields.length === 0) {
                return null;
            }

            return fields.map(function (field, index) {
                return { field: field, order: orders[index] || 'asc' };
            });
        }

        return { cards: readPrefix('cards'), list: readPrefix('list') };
    }

    function renderStacksToWrap(stored) {
        const wrap = getSortWrap();
        if (!wrap || !stored) {
            return;
        }

        wrap.innerHTML = '';

        function render(prefix, stack) {
            if (stack === null || stack === undefined) {
                return;
            }
            if (!stack.length) {
                const none = document.createElement('input');
                none.type = 'hidden';
                none.name = prefix + '_sort';
                none.value = 'none';
                wrap.appendChild(none);
                return;
            }
            stack.forEach(function (item) {
                const fieldInput = document.createElement('input');
                fieldInput.type = 'hidden';
                fieldInput.name = prefix + '_sort[]';
                fieldInput.value = item.field;
                const orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = prefix + '_order[]';
                orderInput.value = item.order === 'desc' ? 'desc' : 'asc';
                wrap.appendChild(fieldInput);
                wrap.appendChild(orderInput);
            });
        }

        render('cards', stored.cards);
        if (stored.list !== null && stored.list !== undefined && stored.list.length > 0) {
            render('list', stored.list);
        }
    }

    function persistSortStacks() {
        const stacks = readStacksFromWrap();
        const payload = { cards: stacks.cards, list: stacks.list };
        if (payload.list && payload.list.length === 1
            && payload.list[0].field === 'start_date'
            && payload.list[0].order === 'desc') {
            payload.list = null;
        }
        try {
            localStorage.setItem(SORT_STORAGE_KEY, JSON.stringify(payload));
        } catch (e) {}
    }

    function loadSortStacksFromStorage() {
        try {
            const raw = localStorage.getItem(SORT_STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function urlHasExplicitSort(params) {
        return params.has('cards_sort') || params.has('list_sort');
    }

    function buildResultsUrl() {
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));
        params.forEach(function (value, key) {
            if (value === '') params.delete(key);
        });
        appendSortParams(params);
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
        ['q', 'sport_id', 'competition_category_id', 'date_from', 'date_to', 'place', 'per_page'].forEach(function (name) {
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
            replaceSortHiddenInputs(doc);
            persistSortStacks();
            applyQueryToForm(url.searchParams);
            document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
            document.dispatchEvent(new CustomEvent('filter-combobox:sync'));
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
            scheduleNow();
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

    let needsInitialRefresh = false;

    (function initSortFromStorage() {
        const url = new URL(window.location.href);
        if (urlHasExplicitSort(url.searchParams)) {
            persistSortStacks();
            return;
        }
        const stored = loadSortStacksFromStorage();
        if (!stored) {
            return;
        }
        const before = JSON.stringify(readStacksFromWrap());
        renderStacksToWrap(stored);
        if (JSON.stringify(readStacksFromWrap()) !== before) {
            needsInitialRefresh = true;
        }
    })();

    const serverView = getServerViewMode();
    const storedView = getStoredViewMode();
    if (storedView !== serverView) {
        syncViewToForm(storedView);
        applyResultsViewMode(storedView);
        updateResultsViewInUrl(storedView);
        needsInitialRefresh = true;
    } else {
        persistViewMode(serverView);
        applyResultsViewMode(serverView);
    }

    if (needsInitialRefresh) {
        scheduleNow();
    }
})();
</script>
@endpush
