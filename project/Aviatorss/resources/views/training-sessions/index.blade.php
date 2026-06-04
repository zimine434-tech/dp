@extends('layouts.teacher')

@section('title', 'Тренировочные сессии')

@section('content')
    @php
        use App\Support\TrainingSessionListingSort;

        $cardsSortStack = $cardsSortStack ?? TrainingSessionListingSort::defaultStack();
        $listSortStack = $listSortStack ?? TrainingSessionListingSort::defaultStack();
        $trainingIndexFilterParams = array_filter([
            'q' => ($q ?? '') !== '' ? $q : null,
            'date_from' => $dateFrom ?? null,
            'date_to' => $dateTo ?? null,
            'sport_id' => $sportId ?? null,
            'view' => ($view ?? 'list') !== 'list' ? $view : null,
            'per_page' => (int) ($perPage ?? 50) !== 50 ? (string) (int) ($perPage ?? 50) : null,
        ], fn ($v) => $v !== null && $v !== '');
        $trainingBaseListingParams = array_merge(
            $trainingIndexFilterParams,
            ($filter ?? 'all') !== 'all' ? ['filter' => $filter] : []
        );
        $trainingStatusUrl = fn (string $f) => TrainingSessionListingSort::listingUrl(
            'training-sessions.index',
            array_merge($trainingIndexFilterParams, $f === 'all' ? [] : ['filter' => $f]),
            $cardsSortStack,
            $listSortStack,
            ['page' => 1]
        );
        $tsView = $view ?? 'list';
        $trainingFiltersHidden = ['view' => $tsView, 'per_page' => (int) ($perPage ?? 50)];
        if (($filter ?? 'all') !== 'all') {
            $trainingFiltersHidden['filter'] = $filter;
        }
    @endphp
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Тренировочные сессии</h1>
            </div>
            <a
                href="{{ route('training-sessions.create') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap text-sm font-medium"
            >
                <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="hidden sm:inline">Создать тренировку</span>
                <span class="sm:hidden">Создать</span>
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex h-full flex-col rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md">
                    <form
                        id="training-sessions-filters-form"
                        method="get"
                        action="{{ route('training-sessions.index') }}"
                        class="flex flex-1 flex-col justify-end"
                        data-ajax-listing-filters="1"
                    >
                        <input type="hidden" name="page" value="1">
                        @foreach($trainingFiltersHidden as $hiddenName => $hiddenValue)
                            <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenValue }}">
                        @endforeach
                        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end lg:flex-nowrap lg:gap-3 xl:gap-4">
                            <div class="min-w-0 w-full sm:min-w-[12rem] sm:flex-1 lg:w-52 lg:flex-none lg:shrink-0 xl:w-60">
                                <label for="training-sessions-filters-form-q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Поиск</label>
                                <input
                                    id="training-sessions-filters-form-q"
                                    type="search"
                                    name="q"
                                    value="{{ $q ?? '' }}"
                                    placeholder="Название тренировки"
                                    autocomplete="off"
                                    class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                                >
                            </div>
                            <div class="min-w-0 w-full sm:w-44 sm:shrink sm:min-w-[8rem] lg:min-w-[7rem] lg:flex-1 lg:max-w-[12rem]">
                                <label for="training-sessions-sport_combobox_trigger" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Вид спорта</label>
                                <x-teacher-sport-combobox
                                    :sports="$sportsForFilter ?? collect()"
                                    :selected="$sportId ?? null"
                                    name="sport_id"
                                    input-id="training-sessions-sport"
                                    empty-label="Все виды"
                                    variant="filter"
                                />
                            </div>
                            <div class="grid min-w-0 w-full grid-cols-2 gap-3 sm:flex sm:min-w-0 sm:shrink sm:gap-3 lg:flex-[0.9] lg:min-w-0 lg:max-w-[20rem]">
                                <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                                    <label for="training-sessions-filters-form-date-from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата с</label>
                                    <input
                                        id="training-sessions-filters-form-date-from"
                                        type="date"
                                        name="date_from"
                                        value="{{ $dateFrom ?? '' }}"
                                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                                    >
                                </div>
                                <div class="min-w-0 sm:min-w-[7rem] sm:flex-1 sm:max-w-[10rem] lg:min-w-[6.5rem] lg:max-w-[9.5rem]">
                                    <label for="training-sessions-filters-form-date-to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Дата по</label>
                                    <input
                                        id="training-sessions-filters-form-date-to"
                                        type="date"
                                        name="date_to"
                                        value="{{ $dateTo ?? '' }}"
                                        class="block h-10 w-full rounded-lg border-2 border-gray-200 bg-white px-3 text-sm text-gray-900 outline-none focus:border-blue-500 focus:outline-none focus:ring-0"
                                    >
                                </div>
                            </div>
                            <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                                <button type="submit" class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-700 sm:flex-none">Применить</button>
                                <a
                                    href="{{ route('training-sessions.index', array_filter(['filter' => ($filter ?? 'all') !== 'all' ? $filter : null])) }}"
                                    data-training-sessions-listing-ajax="1"
                                    class="inline-flex h-10 min-w-[7rem] flex-1 items-center justify-center rounded-lg border-2 border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 sm:flex-none"
                                >Сбросить</a>
                            </div>
                        </div>
                    </form>
                    <div id="training-sessions-sort-hidden-inputs" class="hidden" aria-hidden="true">
                        @include('training-sessions.partials.sort-hidden-inputs', compact('cardsSortStack', 'listSortStack'))
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 flex-col">
                @include('training-sessions.partials.view-toolbar', ['view' => $tsView])
            </div>
        </div>

        <div class="rounded-lg bg-white px-4 py-3 shadow-md">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">Статус</p>
            <div class="flex flex-wrap gap-2" id="training-sessions-status-chips">
                <a
                    href="{{ $trainingStatusUrl('all') }}"
                    data-training-sessions-listing-ajax="1"
                    data-training-status-chip="all"
                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition {{ ($filter ?? 'all') === 'all' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Все
                </a>
                <a
                    href="{{ $trainingStatusUrl('upcoming') }}"
                    data-training-sessions-listing-ajax="1"
                    data-training-status-chip="upcoming"
                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition {{ ($filter ?? '') === 'upcoming' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Запланированные
                </a>
                <a
                    href="{{ $trainingStatusUrl('in_progress') }}"
                    data-training-sessions-listing-ajax="1"
                    data-training-status-chip="in_progress"
                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition {{ ($filter ?? '') === 'in_progress' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Идут сейчас
                </a>
                <a
                    href="{{ $trainingStatusUrl('completed') }}"
                    data-training-sessions-listing-ajax="1"
                    data-training-status-chip="completed"
                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition {{ ($filter ?? '') === 'completed' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Завершённые
                </a>
                <a
                    href="{{ $trainingStatusUrl('cancelled') }}"
                    data-training-sessions-listing-ajax="1"
                    data-training-status-chip="cancelled"
                    class="inline-flex items-center rounded-full border px-3 py-1.5 text-sm font-medium transition {{ ($filter ?? '') === 'cancelled' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Отменённые
                </a>
            </div>
        </div>

        @include('training-sessions.partials.index-content', [
            'sessions' => $sessions ?? collect(),
            'filter' => $filter ?? 'all',
            'hasSearchFilters' => $hasSearchFilters ?? false,
            'view' => $tsView,
            'perPage' => (int) ($perPage ?? 50),
            'listingRoute' => 'training-sessions.index',
            'baseListingParams' => $trainingBaseListingParams,
            'cardsSortStack' => $cardsSortStack,
            'listSortStack' => $listSortStack,
        ])
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const VIEW_STORAGE_KEY = 'training_sessions_view';
    const PER_PAGE_STORAGE_KEY = 'training_sessions_teacher_per_page';
    const sortHiddenWrapId = 'training-sessions-sort-hidden-inputs';
    const form = document.getElementById('training-sessions-filters-form');

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
        applyTrainingViewMode(getServerViewMode());
        return;
    }

    const input = form.querySelector('input[type="search"][name="q"]');
    const trainingPath = new URL(form.action, window.location.origin).pathname;
    let debounceTimer = null;
    let abortController = null;

    function isTrainingIndexHref(href) {
        try {
            const u = new URL(href, window.location.origin);
            return u.origin === window.location.origin && u.pathname === trainingPath;
        } catch (e) {
            return false;
        }
    }

    const TS_STATUS_ALLOWED = ['all', 'upcoming', 'in_progress', 'completed', 'cancelled'];
    const TS_STATUS_ACTIVE = ['border-blue-600', 'bg-blue-600', 'text-white'];
    const TS_STATUS_INACTIVE = ['border-gray-200', 'bg-gray-50', 'text-gray-800', 'hover:bg-gray-100'];

    function filterFromTrainingUrlSearchParams(params) {
        const f = params.get('filter');
        return TS_STATUS_ALLOWED.includes(f) ? f : 'all';
    }

    function syncTrainingStatusChips(activeFilter) {
        const active = TS_STATUS_ALLOWED.includes(activeFilter) ? activeFilter : 'all';
        document.querySelectorAll('[data-training-status-chip]').forEach(function (el) {
            const v = el.getAttribute('data-training-status-chip');
            const isActive = v === active;
            TS_STATUS_ACTIVE.forEach(function (c) {
                el.classList.toggle(c, isActive);
            });
            TS_STATUS_INACTIVE.forEach(function (c) {
                el.classList.toggle(c, !isActive);
            });
        });
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
        const currentWrap = getSortWrap();
        if (!nextWrap || !currentWrap) {
            return;
        }
        currentWrap.innerHTML = nextWrap.innerHTML;
    }

    function normalizeTrainingListingSearchParams(params) {
        params.forEach(function (value, key) {
            if (value === '') params.delete(key);
        });
        if (params.get('view') === 'list') {
            params.delete('view');
        }
        if (!params.get('filter')) {
            params.delete('filter');
        }
    }

    function buildTrainingListingUrl() {
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));
        normalizeTrainingListingSearchParams(params);
        appendSortParams(params);
        url.search = params.toString();
        return url;
    }

    /** Сброс полей поиска/дат/спорта; «вид» и статус берутся из текущей формы (актуально после переключения список/карточки в JS). */
    function buildTrainingResetSearchFiltersUrl() {
        const url = buildTrainingListingUrl();
        const params = url.searchParams;
        ['q', 'sport_id', 'date_from', 'date_to'].forEach(function (key) {
            params.delete(key);
        });
        normalizeTrainingListingSearchParams(params);
        url.search = params.toString();
        return url;
    }

    function buildTrainingListingUrlForStatusChip(chipValue) {
        const url = buildTrainingListingUrl();
        const params = url.searchParams;
        if (chipValue === 'all') {
            params.delete('filter');
        } else {
            params.set('filter', chipValue);
        }
        normalizeTrainingListingSearchParams(params);
        url.search = params.toString();
        return url;
    }

    function applyQueryToForm(params) {
        const viewEl = form.elements.namedItem('view');
        if (viewEl) {
            viewEl.value = params.get('view') === 'cards' ? 'cards' : 'list';
        }
        const filterEl = form.elements.namedItem('filter');
        if (filterEl) {
            filterEl.value = params.get('filter') || '';
        }
        ['q', 'sport_id', 'date_from', 'date_to', 'per_page'].forEach(function (name) {
            const el = form.elements.namedItem(name);
            if (!el) return;
            el.value = params.has(name) ? params.get(name) : '';
        });
    }

    async function refreshTrainingListing(targetUrl) {
        const url = targetUrl || buildTrainingListingUrl();
        const resultsEl = document.getElementById('training-sessions-content');
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
            const nextResults = doc.getElementById('training-sessions-content');
            if (!nextResults) return;

            resultsEl.replaceWith(document.importNode(nextResults, true));
            replaceSortHiddenInputs(doc);
            applyQueryToForm(url.searchParams);
            syncTrainingStatusChips(filterFromTrainingUrlSearchParams(url.searchParams));
            document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
            applyTrainingViewMode(getServerViewMode());
            syncPerPageBottomFromHidden();

            const path = url.pathname + (url.search ? url.search : '');
            if (window.location.pathname + window.location.search !== path) {
                history.replaceState(null, '', path);
            }
        } catch (e) {
            if (e.name === 'AbortError') return;
        } finally {
            const el = document.getElementById('training-sessions-content');
            if (el) {
                el.classList.remove('opacity-60', 'pointer-events-none');
                el.removeAttribute('aria-busy');
            }
        }
    }

    function syncPerPageBottomFromHidden() {
        const perPageHidden = form && form.elements.namedItem('per_page');
        const perPageBottom = document.getElementById('training-sessions-per-page-bottom');
        if (!perPageHidden || !perPageBottom) return;
        perPageBottom.value = String(perPageHidden.value || '50');
    }

    function scheduleDebounced() {
        clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () {
            debounceTimer = null;
            refreshTrainingListing(buildTrainingListingUrl());
        }, 320);
    }

    function scheduleNow() {
        clearTimeout(debounceTimer);
        debounceTimer = null;
        refreshTrainingListing(buildTrainingListingUrl());
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;

        const viewToggle = e.target.closest('#training-sessions-view-toolbar .training-sessions-view-toggle');
        if (viewToggle) {
            const mode = viewToggle.getAttribute('data-view');
            if (mode !== 'list' && mode !== 'cards') return;
            e.preventDefault();
            persistViewMode(mode);
            syncViewToForm(mode);
            applyTrainingViewMode(mode);
            updateTrainingViewInUrl(mode);
            return;
        }

        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const a = e.target.closest('a');
        if (!a || !a.href || !isTrainingIndexHref(a.href)) return;
        if (a.hasAttribute('data-turbo')) return;
        if (!a.hasAttribute('data-training-sessions-listing-ajax') && !a.closest('#training-sessions-pagination')) return;
        e.preventDefault();
        clearTimeout(debounceTimer);
        debounceTimer = null;

        let targetUrl;
        const statusChip = a.getAttribute('data-training-status-chip');
        if (statusChip !== null) {
            targetUrl = buildTrainingListingUrlForStatusChip(statusChip);
        } else if (a.closest('#training-sessions-filters-form')) {
            targetUrl = buildTrainingResetSearchFiltersUrl();
        } else if (a.getAttribute('data-training-reset-empty-listing') === '1') {
            targetUrl = buildTrainingResetSearchFiltersUrl();
        } else {
            targetUrl = new URL(a.href, window.location.origin);
        }

        syncTrainingStatusChips(filterFromTrainingUrlSearchParams(targetUrl.searchParams));
        refreshTrainingListing(targetUrl);
    });

    const perPageHidden = form.elements.namedItem('per_page');
    const perPageBottom = document.getElementById('training-sessions-per-page-bottom');

    if (perPageHidden) {
        try {
            const u = new URL(window.location.href);
            if (!u.searchParams.get('per_page')) {
                const stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
                if (stored && stored !== perPageHidden.value) {
                    perPageHidden.value = stored;
                    if (perPageBottom) perPageBottom.value = stored;
                }
            } else if (perPageBottom) {
                perPageBottom.value = u.searchParams.get('per_page');
            }
        } catch (e) {}
    }

    // Селектор находится внутри #training-sessions-content и перерисовывается AJAX-ом,
    // поэтому слушаем change делегированно.
    document.addEventListener('change', function (e) {
        const target = e.target;
        if (!target || target.id !== 'training-sessions-per-page-bottom') return;
        if (!perPageHidden) return;
        perPageHidden.value = String(target.value || '50');
        try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(perPageHidden.value || '50')); } catch (e2) {}
        scheduleNow();
    });

    syncPerPageBottomFromHidden();

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        scheduleNow();
    });

    const serverView = getServerViewMode();
    const storedView = getStoredViewMode();
    if (storedView !== serverView) {
        syncViewToForm(storedView);
        applyTrainingViewMode(storedView);
        updateTrainingViewInUrl(storedView);
    } else {
        persistViewMode(serverView);
        applyTrainingViewMode(serverView);
    }

    syncTrainingStatusChips(filterFromTrainingUrlSearchParams(new URL(window.location.href).searchParams));
})();
</script>
@endpush
