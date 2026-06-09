@extends('layouts.student')

@section('title', 'Тренировочные сессии')

@section('content')
    @push('styles')
        <style>
            .filter-chip {
                -webkit-tap-highlight-color: transparent;
            }
            .filter-chip:active {
                transform: scale(0.98);
            }
            .filter-chip:focus-visible {
                outline: none;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.45);
            }
        </style>
    @endpush
    @php
        use App\Support\TrainingSessionListingSort;

        $filter = $filter ?? 'all';
        $listingFilters = $listingFilters ?? [];
        $cardsSortStack = $cardsSortStack ?? TrainingSessionListingSort::defaultStack();
        $listSortStack = $listSortStack ?? TrainingSessionListingSort::defaultListStack();
        $studentView = $view ?? 'cards';
        if (! in_array($studentView, ['list', 'cards'], true)) {
            $studentView = 'cards';
        }
        $baseListingParams = array_filter([
            'sport_id' => $listingFilters['sport_id'] ?? null,
            'date_from' => $listingFilters['date_from'] ?? null,
            'date_to' => $listingFilters['date_to'] ?? null,
            'q' => ($listingFilters['q'] ?? '') !== '' ? $listingFilters['q'] : null,
            'view' => $studentView === 'list' ? 'list' : null,
            'per_page' => ($perPage ?? 50) !== 50 ? (string) ($perPage ?? 50) : null,
        ], fn ($v) => $v !== null && $v !== '');
        $statusRoute = fn (string $f) => TrainingSessionListingSort::listingUrl(
            'training-sessions.index',
            array_merge($baseListingParams, $f === 'all' ? [] : ['filter' => $f]),
            $cardsSortStack,
            $listSortStack,
            ['page' => 1]
        );
        $resetListingUrl = TrainingSessionListingSort::listingUrl(
            'training-sessions.index',
            array_filter(['filter' => $filter !== 'all' ? $filter : null]),
            $cardsSortStack,
            $listSortStack,
            ['page' => 1]
        );
    @endphp
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-3">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Тренировочные сессии</h1>
            <a
                href="{{ route('training-sessions.student.my') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
            >
                Мои тренировки
            </a>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                @php $lf = $listingFilters; @endphp
                <form method="GET" action="{{ route('training-sessions.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="view" id="ts_filter_view" value="{{ $studentView }}">
                    <input type="hidden" name="per_page" id="ts_filter_per_page" value="{{ $perPage ?? 50 }}">
                    @if($filter !== 'all')
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                    @include('training-sessions.partials.sort-hidden-inputs', compact('cardsSortStack', 'listSortStack'))
                    <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                        <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                            <label for="ts_sport_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                            <x-teacher-sport-combobox
                                :sports="$sportsForFilter"
                                :selected="$lf['sport_id'] ?? null"
                                name="sport_id"
                                input-id="ts_sport"
                                empty-label="Все виды"
                                variant="filter"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="ts_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск по названию</label>
                            <input type="search" id="ts_q" name="q" value="{{ $lf['q'] ?? '' }}" maxlength="200" placeholder="Введите название..." autocomplete="off" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                            <div class="min-w-0 sm:w-40">
                                <label for="ts_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                                <input type="date" id="ts_date_from" name="date_from" value="{{ $lf['date_from'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="min-w-0 sm:w-40">
                                <label for="ts_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                                <input type="date" id="ts_date_to" name="date_to" value="{{ $lf['date_to'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                            <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Применить</button>
                            <a href="{{ $resetListingUrl }}" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Сбросить</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex shrink-0 flex-col">
                @include('training-sessions.partials.view-toolbar', ['view' => $studentView])
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="mb-3 text-xs font-medium text-gray-500">Статус</p>
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ $statusRoute('all') }}"
                    class="filter-chip inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'all' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Все
                </a>
                <a
                    href="{{ $statusRoute('upcoming') }}"
                    class="filter-chip inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'upcoming' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Запланированные
                </a>
                <a
                    href="{{ $statusRoute('in_progress') }}"
                    class="filter-chip inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'in_progress' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Идут сейчас
                </a>
                <a
                    href="{{ $statusRoute('completed') }}"
                    class="filter-chip inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'completed' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Завершенные
                </a>
                <a
                    href="{{ $statusRoute('cancelled') }}"
                    class="filter-chip inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'cancelled' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Отмененные
                </a>
            </div>
        </div>

        @if($allTrainingSessions->total() > 0)
            <div>
                @include('training-sessions.partials.listing-sessions-body', [
                    'sessions' => $allTrainingSessions,
                    'view' => $studentView,
                    'perPage' => $perPage ?? 50,
                    'listingRoute' => 'training-sessions.index',
                    'baseListingParams' => array_merge($baseListingParams, $filter !== 'all' ? ['filter' => $filter] : []),
                    'cardsSortStack' => $cardsSortStack,
                    'listSortStack' => $listSortStack,
                    'rowPartial' => 'training-sessions.student.partials.session-row',
                    'cardPartial' => 'training-sessions.student.partials.session-card',
                    'listingAjaxAttr' => '',
                ])
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                @if($hasSearchFilters ?? false)
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Ничего не найдено</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Измените поиск, даты или вид спорта.
                        <a href="{{ route('training-sessions.index', array_filter(['filter' => $filter !== 'all' ? $filter : null, 'view' => $studentView === 'list' ? 'list' : null])) }}" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>
                    </p>
                @else
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        @if($filter === 'upcoming')
                            Нет предстоящих тренировок
                        @elseif($filter === 'in_progress')
                            Нет тренировок, которые идут сейчас
                        @elseif($filter === 'completed')
                            Нет завершенных тренировок
                        @elseif($filter === 'cancelled')
                            Нет отмененных тренировок
                        @else
                            Нет тренировок по заданным условиям
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Измените фильтры по дате, виду спорта или статусу; поиск по названию — в поле выше.
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const VIEW_STORAGE_KEY = 'training_sessions_student_view';
    const viewHidden = document.getElementById('ts_filter_view');
    const PER_PAGE_STORAGE_KEY = 'training_sessions_student_per_page';
    const perPageHidden = document.getElementById('ts_filter_per_page');
    const perPageSelect = document.getElementById('ts_per_page_select');

    function getStoredViewMode() {
        try {
            const v = localStorage.getItem(VIEW_STORAGE_KEY);
            if (v === 'list' || v === 'cards') return v;
        } catch (e) {}
        return null;
    }

    function persistViewMode(mode) {
        try {
            localStorage.setItem(VIEW_STORAGE_KEY, mode === 'cards' ? 'cards' : 'list');
        } catch (e) {}
    }

    function syncViewToForm(mode) {
        if (viewHidden) {
            viewHidden.value = mode === 'cards' ? 'cards' : 'list';
        }
    }

    function getServerViewMode() {
        if (viewHidden && viewHidden.value === 'list') return 'list';
        return 'cards';
    }

    function applyTrainingViewMode(mode) {
        const isCards = mode === 'cards';
        document.querySelectorAll('[data-training-sessions-list-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', isCards);
        });
        document.querySelectorAll('[data-training-sessions-cards-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', !isCards);
        });
        document.querySelectorAll('[data-training-sessions-cards-sort-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', !isCards);
        });
        const btnList = document.getElementById('training-sessions-view-list');
        const btnCards = document.getElementById('training-sessions-view-cards');
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

    function updateTrainingViewInUrl(mode) {
        const url = new URL(window.location.href);
        if (mode === 'cards') {
            url.searchParams.delete('view');
        } else {
            url.searchParams.set('view', 'list');
        }
        const path = url.pathname + (url.search ? url.search : '');
        if (window.location.pathname + window.location.search !== path) {
            history.replaceState(null, '', path);
        }
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;
        const viewToggle = e.target.closest('#training-sessions-view-toolbar .training-sessions-view-toggle');
        if (!viewToggle) return;
        const mode = viewToggle.getAttribute('data-view');
        if (mode !== 'list' && mode !== 'cards') return;
        e.preventDefault();
        persistViewMode(mode);
        syncViewToForm(mode);
        applyTrainingViewMode(mode);
        updateTrainingViewInUrl(mode);
    });

    const serverView = getServerViewMode();
    const stored = getStoredViewMode();
    if (stored !== null && stored !== serverView) {
        syncViewToForm(stored);
        applyTrainingViewMode(stored);
        updateTrainingViewInUrl(stored);
    } else {
        persistViewMode(serverView);
        applyTrainingViewMode(serverView);
    }

    function initPerPage() {
        if (!perPageHidden || !perPageSelect) return;
        const url = new URL(window.location.href);
        const urlVal = url.searchParams.get('per_page');
        let current = urlVal ? parseInt(urlVal, 10) : parseInt(perPageHidden.value || '50', 10);
        if (![10,25,50,100].includes(current)) current = 50;

        if (!urlVal) {
            try {
                const storedPer = parseInt(localStorage.getItem(PER_PAGE_STORAGE_KEY) || '', 10);
                if ([10,25,50,100].includes(storedPer) && storedPer !== current) {
                    url.searchParams.set('per_page', String(storedPer));
                    history.replaceState(null, '', url.pathname + '?' + url.searchParams.toString());
                    current = storedPer;
                }
            } catch (e) {}
        }

        perPageHidden.value = String(current);
        perPageSelect.value = String(current);

        perPageSelect.addEventListener('change', function () {
            const v = parseInt(String(perPageSelect.value || '50'), 10);
            const val = [10,25,50,100].includes(v) ? v : 50;
            perPageHidden.value = String(val);
            try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(val)); } catch (e) {}
            const form = perPageHidden.closest('form');
            if (form) form.submit();
        });
    }

    initPerPage();
})();
</script>
@endpush
