@extends('layouts.teacher')

@section('title', 'Соревнования')

@section('content')
    @php
        $filter = $filter ?? 'all';
        $sort = $sort ?? 'start_date';
        $order = $order ?? 'desc';
        $q = $q ?? '';
        $dateFrom = $dateFrom ?? null;
        $dateTo = $dateTo ?? null;
        $sportId = $sportId ?? null;
        $view = $view ?? 'list';
        $sports = $sports ?? collect();
        $hasSearchFilters = $q !== '' || $dateFrom || $dateTo || $sportId;
        $indexParams = array_filter([
            'from' => 'index',
            'filter' => $filter,
            'sort' => $sort,
            'order' => $order,
            'view' => $view !== 'list' ? $view : null,
            'q' => $q !== '' ? $q : null,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'sport_id' => $sportId,
            'page' => request()->query('page'),
        ], fn ($v) => $v !== null && $v !== '');
        $competitionShowParams = fn ($competition) => array_merge(
            ['competition' => $competition],
            $indexParams,
        );
    @endphp
    <div class="space-y-6">
        @if (request()->boolean('upload_err'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded" role="alert">
                <p class="text-sm text-red-700">Превышен размер файла.</p>
            </div>
        @endif
        <!-- Заголовок и кнопка создания -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Соревнования</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a 
                    href="{{ route('competitions.results') }}" 
                    class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <span class="hidden sm:inline">Результаты</span>
                    <span class="sm:hidden">Результаты</span>
                </a>
                <a 
                    href="{{ route('competitions.create') }}" 
                    class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="hidden sm:inline">Создать соревнование</span>
                    <span class="sm:hidden">Создать</span>
                </a>
            </div>
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

        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-stretch sm:justify-between lg:gap-6">
            <div id="competitions-status-tabs" class="flex flex-wrap items-center gap-2 rounded-lg bg-white p-4 shadow-md">
                <a 
                    href="{{ route('competitions.index', array_merge($indexParams, ['filter' => 'all'])) }}" 
                    data-competitions-index-ajax="1"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    Все
                </a>
                <a 
                    href="{{ route('competitions.index', array_merge($indexParams, ['filter' => 'upcoming'])) }}" 
                    data-competitions-index-ajax="1"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    Предстоящие
                </a>
                <a 
                    href="{{ route('competitions.index', array_merge($indexParams, ['filter' => 'ongoing'])) }}" 
                    data-competitions-index-ajax="1"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'ongoing' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    Идут сейчас
                </a>
                <a 
                    href="{{ route('competitions.index', array_merge($indexParams, ['filter' => 'finished'])) }}" 
                    data-competitions-index-ajax="1"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'finished' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    Завершенные
                </a>
                <a 
                    href="{{ route('competitions.index', array_merge($indexParams, ['filter' => 'cancelled'])) }}" 
                    data-competitions-index-ajax="1"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'cancelled' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    Отмененные
                </a>
            </div>
            <div
                id="competitions-view-toolbar"
                class="flex shrink-0 flex-col gap-1.5 rounded-lg bg-white p-4 shadow-md sm:flex-row sm:items-center sm:gap-3"
                role="group"
                aria-label="Вид списка соревнований"
            >
                <span class="text-xs font-medium uppercase tracking-wide text-gray-500">Вид</span>
                <div class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-gray-50 p-0.5" role="tablist">
                    <button
                        type="button"
                        id="competitions-view-list"
                        class="competitions-view-toggle inline-flex h-9 items-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 {{ $view === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                        data-view="list"
                        role="tab"
                        aria-selected="{{ $view === 'list' ? 'true' : 'false' }}"
                    >
                        Список
                    </button>
                    <button
                        type="button"
                        id="competitions-view-cards"
                        class="competitions-view-toggle inline-flex h-9 items-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 {{ $view === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                        data-view="cards"
                        role="tab"
                        aria-selected="{{ $view === 'cards' ? 'true' : 'false' }}"
                    >
                        Карточки
                    </button>
                </div>
            </div>
        </div>

        <!--Поиск и фильтры-->
        <div class="bg-white rounded-lg shadow-md p-4">
            <form id="competitions-filters-form" method="get" action="{{ route('competitions.index') }}" class="space-y-4" data-live-filters="1">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="order" value="{{ $order }}">
                <input type="hidden" name="view" id="competitions-view-input" value="{{ $view }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end lg:flex-nowrap lg:gap-3 xl:gap-4">
                    <div class="min-w-0 w-full sm:min-w-[12rem] sm:flex-1 lg:w-52 lg:flex-none lg:shrink-0 xl:w-60">
                        <label for="competitions-q" class="mb-1 block min-h-[1rem] text-xs font-medium uppercase tracking-wide text-gray-500">Поиск</label>
                        <input
                            id="competitions-q"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Название соревнования"
                            autocomplete="off"
                            class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                        >
                    </div>
                    <div class="min-w-0 w-full sm:w-44 sm:shrink sm:min-w-[8rem] lg:min-w-[7rem] lg:flex-1 lg:max-w-[12rem]">
                        <label for="competitions-sport_combobox_trigger" class="mb-1 block min-h-[1rem] text-xs font-medium uppercase tracking-wide text-gray-500">Вид спорта</label>
                        <x-teacher-sport-combobox
                            :sports="$sports"
                            :selected="$sportId"
                            name="sport_id"
                            input-id="competitions-sport"
                            empty-label="Все виды"
                            variant="filter"
                        />
                    </div>
                    <div class="grid min-w-0 w-full grid-cols-2 gap-3 sm:flex sm:min-w-0 sm:shrink sm:gap-3 lg:flex-[0.9] lg:min-w-0 lg:max-w-[20rem]">
                        <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                            <label for="competitions-date-from" class="mb-1 block min-h-[1rem] text-xs font-medium uppercase tracking-wide text-gray-500">Дата с</label>
                            <input
                                id="competitions-date-from"
                                type="date"
                                name="date_from"
                                value="{{ $dateFrom }}"
                                class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                            >
                        </div>
                        <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                            <label for="competitions-date-to" class="mb-1 block min-h-[1rem] text-xs font-medium uppercase tracking-wide text-gray-500">Дата по</label>
                            <input
                                id="competitions-date-to"
                                type="date"
                                name="date_to"
                                value="{{ $dateTo }}"
                                class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                            >
                        </div>
                    </div>
                    <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                        <a
                            href="{{ route('competitions.index', array_filter(['filter' => $filter, 'sort' => $sort, 'order' => $order])) }}"
                            data-competitions-index-ajax="1"
                            class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none"
                        >
                            Сбросить
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div
            id="competitions-index-results"
            class="transition-opacity duration-150"
            role="region"
            aria-label="Список соревнований"
        >
        @if($competitions->isNotEmpty())
            <div id="competitions-teacher-list-wrap" class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Вид спорта</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('competitions.index', array_merge($indexParams, ['sort' => 'start_date', 'order' => $sort === 'start_date' && $order === 'asc' ? 'desc' : 'asc'])) }}" data-competitions-index-ajax="1" class="flex items-center hover:text-gray-700">
                                            Даты
                                            @if($sort === 'start_date')
                                                @if($order === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Локация</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('competitions.index', array_merge($indexParams, ['sort' => 'status', 'order' => $sort === 'status' && $order === 'asc' ? 'desc' : 'asc'])) }}" data-competitions-index-ajax="1" class="flex items-center hover:text-gray-700">
                                            Статус
                                            @if($sort === 'status')
                                                @if($order === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($competitions as $competition)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('competitions.show', $competitionShowParams($competition)) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $competition->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                        <div class="text-sm text-gray-900">{{ $competition->sport->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $competition->start_date->format('d.m.Y') }}</div>
                                        <div class="text-sm text-gray-500">до {{ $competition->end_date->format('d.m.Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                        <div class="text-sm text-gray-900">{{ $competition->location->location ?? 'Не указана' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($competition->status === 'upcoming')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Предстоящее
                                            </span>
                                        @elseif($competition->status === 'ongoing')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Идет
                                            </span>
                                        @elseif($competition->status === 'finished')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Завершено
                                            </span>
                                        @elseif($competition->status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Отменено
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a 
                                            href="{{ route('competitions.show', $competitionShowParams($competition)) }}" 
                                            class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded hover:bg-blue-50 transition"
                                        >
                                            Подробнее
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="competitions-teacher-cards-wrap" class="hidden">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($competitions as $competition)
                        <article class="flex flex-col rounded-lg border border-gray-200 bg-white p-5 shadow-md transition hover:border-gray-300 hover:shadow-lg">
                            <div class="flex flex-1 flex-col gap-3">
                                <h2 class="text-base font-semibold leading-snug text-gray-900">
                                    <a href="{{ route('competitions.show', $competitionShowParams($competition)) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $competition->name }}
                                    </a>
                                </h2>
                                <p class="text-sm text-gray-600">{{ $competition->sport->name }}</p>
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium text-gray-800">{{ $competition->start_date->format('d.m.Y') }}</span>
                                    <span class="text-gray-400"> - </span>
                                    <span class="text-gray-600">{{ $competition->end_date->format('d.m.Y') }}</span>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="text-gray-500">Локация:</span>
                                    {{ $competition->location->location ?? 'Не указана' }}
                                </p>
                                <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-2">
                                    <div>
                                        @if($competition->status === 'upcoming')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Предстоящее</span>
                                        @elseif($competition->status === 'ongoing')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Идет</span>
                                        @elseif($competition->status === 'finished')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Завершено</span>
                                        @elseif($competition->status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Отменено</span>
                                        @endif
                                    </div>
                                    <a
                                        href="{{ route('competitions.show', $competitionShowParams($competition)) }}"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-blue-700"
                                    >
                                        Подробнее
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            @if($competitions->hasPages())
                <nav id="competitions-index-pagination" class="flex justify-end pt-2" aria-label="Пагинация">
                    {{ $competitions->links('pagination::tailwind') }}
                </nav>
            @endif
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет соревнований</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($hasSearchFilters)
                        По заданным условиям поиска и фильтров ничего не найдено. Измените запрос или <a href="{{ route('competitions.index', array_filter(['filter' => $filter, 'sort' => $sort, 'order' => $order])) }}" data-competitions-index-ajax="1" class="text-blue-600 hover:text-blue-800 font-medium">сбросьте фильтры</a>.
                    @elseif($filter !== 'all')
                        Нет соревнований с выбранным статусом.
                    @else
                        Начните с создания нового соревнования.
                    @endif
                </p>
                @if($filter === 'all')
                    <div class="mt-6">
                        <a 
                            href="{{ route('competitions.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Создать соревнование
                        </a>
                    </div>
                @endif
            </div>
        @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const VIEW_STORAGE_KEY = 'teacher_competitions_index_view';

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
        const el = document.getElementById('competitions-view-input');
        if (el) {
            el.value = mode === 'cards' ? 'cards' : 'list';
        }
    }

    function getServerViewMode() {
        const el = document.getElementById('competitions-view-input');
        return el && el.value === 'cards' ? 'cards' : 'list';
    }

    function applyTeacherCompetitionsViewMode(mode) {
        const listWrap = document.getElementById('competitions-teacher-list-wrap');
        const cardsWrap = document.getElementById('competitions-teacher-cards-wrap');
        const btnList = document.getElementById('competitions-view-list');
        const btnCards = document.getElementById('competitions-view-cards');
        if (!listWrap || !cardsWrap || !btnList || !btnCards) return;

        const isCards = mode === 'cards';
        listWrap.classList.toggle('hidden', isCards);
        cardsWrap.classList.toggle('hidden', !isCards);

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

    const form = document.getElementById('competitions-filters-form');
    const input = document.getElementById('competitions-q');
    const indexPath = form
        ? new URL(form.getAttribute('action') || '/competitions', window.location.origin).pathname
        : '/competitions';
    let debounceTimer = null;
    let abortController = null;

    function isCompetitionsIndexHref(href) {
        try {
            const u = new URL(href, window.location.origin);
            return u.origin === window.location.origin && u.pathname === indexPath;
        } catch (e) {
            return false;
        }
    }

    function buildListUrl(resetPage) {
        const action = form ? (form.getAttribute('action') || window.location.pathname) : window.location.pathname;
        const url = new URL(action, window.location.origin);
        if (form) {
            url.search = new URLSearchParams(new FormData(form)).toString();
        }
        if (url.searchParams.get('view') === 'list') {
            url.searchParams.delete('view');
        }
        if (resetPage) {
            url.searchParams.delete('page');
        }
        return url;
    }

    function viewModeFromParams(params) {
        return params.get('view') === 'cards' ? 'cards' : 'list';
    }

    function applyQueryToForm(params) {
        if (!form) return;
        ['filter', 'sort', 'order', 'view'].forEach(function (name) {
            const el = form.elements.namedItem(name);
            if (!el) return;
            if (name === 'view') {
                el.value = params.get('view') === 'cards' ? 'cards' : 'list';
                return;
            }
            if (params.has(name)) {
                el.value = params.get(name);
            }
        });
        ['q', 'sport_id', 'date_from', 'date_to'].forEach(function (name) {
            const el = form.elements.namedItem(name);
            if (!el) return;
            el.value = params.has(name) ? params.get(name) : '';
        });
    }

    async function refreshList(targetUrl) {
        const url = targetUrl || (form ? buildListUrl(false) : new URL(window.location.href));
        const resultsEl = document.getElementById('competitions-index-results');
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
            const nextResults = doc.getElementById('competitions-index-results');
            if (!nextResults) return;

            resultsEl.replaceWith(document.importNode(nextResults, true));

            const nextTabs = doc.getElementById('competitions-status-tabs');
            const liveTabs = document.getElementById('competitions-status-tabs');
            if (nextTabs && liveTabs) {
                liveTabs.replaceWith(document.importNode(nextTabs, true));
            }

            if (form) {
                applyQueryToForm(url.searchParams);
                document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
            }

            const mode = viewModeFromParams(url.searchParams);
            syncViewToForm(mode);
            persistViewMode(mode);
            applyTeacherCompetitionsViewMode(mode);

            const path = url.pathname + (url.search ? url.search : '');
            if (window.location.pathname + window.location.search !== path) {
                history.replaceState(null, '', path);
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
        } finally {
            const el = document.getElementById('competitions-index-results');
            if (el) {
                el.classList.remove('opacity-60', 'pointer-events-none');
                el.removeAttribute('aria-busy');
            }
        }
    }

    function scheduleDebounced() {
        if (!form) return;
        clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () {
            debounceTimer = null;
            refreshList(buildListUrl(true));
        }, 320);
    }

    function scheduleNow() {
        if (!form) return;
        clearTimeout(debounceTimer);
        debounceTimer = null;
        refreshList(buildListUrl(true));
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;

        const toggle = e.target.closest('#competitions-view-toolbar .competitions-view-toggle');
        if (toggle) {
            const mode = toggle.getAttribute('data-view');
            if (mode !== 'list' && mode !== 'cards') return;
            e.preventDefault();
            persistViewMode(mode);
            syncViewToForm(mode);
            applyTeacherCompetitionsViewMode(mode);
            if (form && form.dataset.liveFilters) {
                refreshList(buildListUrl(true));
            }
            return;
        }

        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const a = e.target.closest('a[data-competitions-index-ajax]')
            || e.target.closest('#competitions-index-results nav a[href]');
        if (!a || !a.href) return;
        if (!isCompetitionsIndexHref(a.href)) return;
        e.preventDefault();
        clearTimeout(debounceTimer);
        debounceTimer = null;
        refreshList(new URL(a.href, window.location.origin));
    });

    if (form && form.dataset.liveFilters && input) {
        input.addEventListener('input', scheduleDebounced);
        input.addEventListener('search', scheduleDebounced);

        const sport = document.getElementById('competitions-sport');
        const dateFrom = document.getElementById('competitions-date-from');
        const dateTo = document.getElementById('competitions-date-to');
        if (sport) sport.addEventListener('change', scheduleNow);
        if (dateFrom) dateFrom.addEventListener('change', scheduleNow);
        if (dateTo) dateTo.addEventListener('change', scheduleNow);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            scheduleNow();
        });

        const serverView = getServerViewMode();
        const storedView = getStoredViewMode();
        if (storedView !== serverView) {
            syncViewToForm(storedView);
            refreshList(buildListUrl(true));
        } else {
            persistViewMode(serverView);
            applyTeacherCompetitionsViewMode(serverView);
        }
    } else {
        const mode = getServerViewMode();
        persistViewMode(mode);
        applyTeacherCompetitionsViewMode(mode);
    }
})();
</script>
@endpush

