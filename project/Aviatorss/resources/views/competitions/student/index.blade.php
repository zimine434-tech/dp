@extends('layouts.student')

@section('title', 'Соревнования')

@section('content')
    @php
        use App\Support\StudentCompetitionListingSort;

        $filter = $filter ?? 'upcoming';
        $listingFilters = $listingFilters ?? [];
        $cardsSortStack = $cardsSortStack ?? StudentCompetitionListingSort::defaultStack();
        $listSortStack = $listSortStack ?? StudentCompetitionListingSort::defaultListStack();
        $studentView = $view ?? 'cards';
        if (! in_array($studentView, ['list', 'cards'], true)) {
            $studentView = 'cards';
        }
        $categoryFilterOptions = collect($categoriesForFilter ?? [])
            ->map(fn ($category) => ['value' => (string) $category->id, 'label' => (string) $category->name_category])
            ->values()
            ->all();
        $baseListingParams = array_filter([
            'sport_id' => $listingFilters['sport_id'] ?? null,
            'competition_category_id' => $listingFilters['competition_category_id'] ?? null,
            'date_from' => $listingFilters['date_from'] ?? null,
            'date_to' => $listingFilters['date_to'] ?? null,
            'q' => filled($listingFilters['q'] ?? null) ? $listingFilters['q'] : null,
            'view' => $studentView === 'list' ? 'list' : null,
            'per_page' => ($perPage ?? 50) !== 50 ? (string) ($perPage ?? 50) : null,
        ], fn ($v) => $v !== null && $v !== '');
        $listingParams = StudentCompetitionListingSort::mergeQueryParams(
            array_merge($baseListingParams, ['filter' => $filter]),
            $cardsSortStack,
            $listSortStack
        );
        $statusRoute = fn (string $f) => StudentCompetitionListingSort::listingUrl(
            'competitions.index',
            array_merge($baseListingParams, ['filter' => $f]),
            $cardsSortStack,
            $listSortStack,
            ['page' => 1]
        );
        $resetListingUrl = StudentCompetitionListingSort::listingUrl(
            'competitions.index',
            ['filter' => $filter],
            $cardsSortStack,
            $listSortStack,
            ['page' => 1]
        );
    @endphp
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="flex items-start justify-between gap-3">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Соревнования</h1>
            <a
                href="{{ route('competitions.student.my') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
            >
                Мои соревнования
            </a>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                @php $lf = $listingFilters; @endphp
                <form id="competitions-student-filters-form" method="GET" action="{{ route('competitions.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <input type="hidden" name="view" id="comp_filter_view" value="{{ $studentView }}">
                    <input type="hidden" name="per_page" id="comp_filter_per_page" value="{{ $perPage ?? 50 }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                        <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                            <label for="comp_sport_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                            <x-teacher-sport-combobox
                                :sports="$sportsForFilter"
                                :selected="$lf['sport_id'] ?? null"
                                name="sport_id"
                                input-id="comp_sport"
                                empty-label="Все виды"
                                variant="filter"
                            />
                        </div>
                        <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                            <label for="comp_category_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Категория</label>
                            <x-filter-combobox
                                name="competition_category_id"
                                :selected="$lf['competition_category_id'] ?? ''"
                                :options="$categoryFilterOptions"
                                empty-label="Все категории"
                                input-id="comp_category"
                                variant="filter"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="comp_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск по названию</label>
                            <input type="search" id="comp_q" name="q" value="{{ $lf['q'] ?? '' }}" maxlength="200" placeholder="Введите название..." autocomplete="off" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                            <div class="min-w-0 sm:w-40">
                                <label for="comp_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                                <input type="date" id="comp_date_from" name="date_from" value="{{ $lf['date_from'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="min-w-0 sm:w-40">
                                <label for="comp_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                                <input type="date" id="comp_date_to" name="date_to" value="{{ $lf['date_to'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                            <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Применить</button>
                            <a href="{{ $resetListingUrl }}" data-competitions-student-ajax="1" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Сбросить</a>
                        </div>
                    </div>
                </form>
                <div id="competitions-student-sort-hidden-inputs" class="hidden" aria-hidden="true">
                    @include('competitions.student.partials.sort-hidden-inputs', compact('cardsSortStack', 'listSortStack'))
                </div>
            </div>
            <div class="flex shrink-0 flex-col">
                @include('competitions.partials.view-toolbar', ['view' => $studentView])
            </div>
        </div>

        @include('competitions.student.partials.listing-ajax-region', [
            'listingPage' => 'index',
            'listingRoute' => 'competitions.index',
            'filter' => $filter,
            'listingFilters' => $listingFilters,
            'baseListingParams' => $baseListingParams,
            'cardsSortStack' => $cardsSortStack,
            'listSortStack' => $listSortStack,
            'view' => $studentView,
            'competitions' => $competitions,
            'perPage' => $perPage,
            'statusRoute' => $statusRoute,
            'perPageSelectId' => 'comp_per_page_select',
            'perPageComboboxTriggerId' => 'comp_per_page_select_combobox_trigger',
        ])
    </div>
@endsection

@push('scripts')
    @include('competitions.student.partials.listing-ajax-scripts', [
        'formId' => 'competitions-student-filters-form',
        'viewHiddenId' => 'comp_filter_view',
        'perPageHiddenId' => 'comp_filter_per_page',
        'perPageSelectId' => 'comp_per_page_select',
        'sortHiddenWrapId' => 'competitions-student-sort-hidden-inputs',
        'sortStorageKey' => 'index',
    ])
@endpush
