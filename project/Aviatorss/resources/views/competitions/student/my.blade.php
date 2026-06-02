@extends('layouts.student')

@section('title', 'Мои соревнования')

@section('content')
    @php
        $filter = $filter ?? 'upcoming';
        $listingFilters = $listingFilters ?? [];
        $studentView = $view ?? 'cards';
        if (! in_array($studentView, ['list', 'cards'], true)) {
            $studentView = 'cards';
        }
        $queryForStatus = array_filter([
            'sport_id' => $listingFilters['sport_id'] ?? null,
            'date_from' => $listingFilters['date_from'] ?? null,
            'date_to' => $listingFilters['date_to'] ?? null,
            'q' => filled($listingFilters['q'] ?? null) ? $listingFilters['q'] : null,
            'view' => $studentView === 'list' ? 'list' : null,
            'per_page' => ($perPage ?? 50) !== 50 ? (string) ($perPage ?? 50) : null,
        ], fn ($v) => $v !== null && $v !== '');
        $statusRoute = fn (string $f) => route('competitions.student.my', array_merge($queryForStatus, ['filter' => $f]));
        $resetListingUrl = route('competitions.student.my', ['filter' => $filter]);
    @endphp

    <div class="space-y-6">
        <div class="flex items-start justify-between gap-3">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Мои соревнования</h1>
            <a
                href="{{ route('competitions.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
            >
                Все соревнования
            </a>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                @php $lf = $listingFilters; @endphp
                <form method="GET" action="{{ route('competitions.student.my') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <input type="hidden" name="view" id="comp_my_filter_view" value="{{ $studentView }}">
                    <input type="hidden" name="per_page" id="comp_my_filter_per_page" value="{{ $perPage ?? 50 }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                        <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                            <label for="comp_my_sport_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                            <x-teacher-sport-combobox
                                :sports="$sportsForFilter"
                                :selected="$lf['sport_id'] ?? null"
                                name="sport_id"
                                input-id="comp_my_sport"
                                empty-label="Все виды"
                                variant="filter"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="comp_my_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск по названию</label>
                            <input type="search" id="comp_my_q" name="q" value="{{ $lf['q'] ?? '' }}" maxlength="200" placeholder="Введите название..." autocomplete="off" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                            <div class="min-w-0 sm:w-40">
                                <label for="comp_my_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                                <input type="date" id="comp_my_date_from" name="date_from" value="{{ $lf['date_from'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div class="min-w-0 sm:w-40">
                                <label for="comp_my_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                                <input type="date" id="comp_my_date_to" name="date_to" value="{{ $lf['date_to'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
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
                @include('competitions.partials.view-toolbar', ['view' => $studentView])
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="mb-3 text-xs font-medium text-gray-500">Статус</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $statusRoute('all') }}" class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'all' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}">Все</a>
                <a href="{{ $statusRoute('upcoming') }}" class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'upcoming' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}">Предстоящие</a>
                <a href="{{ $statusRoute('ongoing') }}" class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'ongoing' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}">Идут сейчас</a>
                <a href="{{ $statusRoute('finished') }}" class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'finished' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}">Завершенные</a>
                <a href="{{ $statusRoute('cancelled') }}" class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'cancelled' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}">Отменённые</a>
            </div>
        </div>

        @if($competitions->total() > 0)
            <div>
                <div data-competitions-list-wrap class="{{ $studentView === 'cards' ? 'hidden' : '' }}">
                    <div class="overflow-x-auto rounded-lg bg-white shadow-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Название</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden md:table-cell">Вид спорта</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Дата</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden lg:table-cell">Место</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Статус</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($competitions as $competition)
                                    @include('competitions.student.partials.competition-row', ['competition' => $competition])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div data-competitions-cards-wrap class="{{ $studentView === 'list' ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                        @foreach($competitions as $competition)
                            @include('competitions.student.partials.competition-card', ['competition' => $competition])
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-100 px-4 py-3">
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                        <label for="comp_my_per_page_select_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                        <x-per-page-combobox :selected="(int)($perPage ?? 50)" input-id="comp_my_per_page_select" />
                    </div>
                    @if($competitions->hasPages())
                        {{ $competitions->links() }}
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                @if(filled($listingFilters['q'] ?? null))
                    <h3 class="mt-2 text-sm font-medium text-gray-900">По названию ничего не найдено</h3>
                    <p class="mt-1 text-sm text-gray-500">Измените запрос или сбросьте фильтры.</p>
                @elseif($filter === 'cancelled')
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Отменённых соревнований нет</h3>
                    <p class="mt-1 text-sm text-gray-500">Смените фильтры или выберите другой статус.</p>
                @else
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет соревнований</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Вы ещё не участвуете ни в одном соревновании.
                        <a href="{{ route('competitions.index') }}" class="font-medium text-blue-600 hover:text-blue-800">Перейти к соревнованиям</a>
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const VIEW_STORAGE_KEY = 'competitions_student_view';
    const viewHidden = document.getElementById('comp_my_filter_view');
    const PER_PAGE_STORAGE_KEY = 'competitions_student_per_page';
    const perPageHidden = document.getElementById('comp_my_filter_per_page');
    const perPageSelect = document.getElementById('comp_my_per_page_select');

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

    function applyCompetitionViewMode(mode) {
        const isCards = mode === 'cards';
        document.querySelectorAll('[data-competitions-list-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', isCards);
        });
        document.querySelectorAll('[data-competitions-cards-wrap]').forEach(function (el) {
            el.classList.toggle('hidden', !isCards);
        });
        const btnList = document.getElementById('competitions-view-list');
        const btnCards = document.getElementById('competitions-view-cards');
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

    function updateCompetitionViewInUrl(mode) {
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
        if (e.defaultPrevented) return;
        const viewToggle = e.target.closest('#competitions-view-toolbar .competitions-view-toggle');
        if (!viewToggle) return;
        const mode = viewToggle.getAttribute('data-view');
        if (mode !== 'list' && mode !== 'cards') return;
        e.preventDefault();
        persistViewMode(mode);
        syncViewToForm(mode);
        applyCompetitionViewMode(mode);
        updateCompetitionViewInUrl(mode);
    });

    const serverView = getServerViewMode();
    const stored = getStoredViewMode();
    if (stored !== null && stored !== serverView) {
        syncViewToForm(stored);
        applyCompetitionViewMode(stored);
        updateCompetitionViewInUrl(stored);
    } else {
        persistViewMode(serverView);
        applyCompetitionViewMode(serverView);
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

