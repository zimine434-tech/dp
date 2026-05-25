@extends('layouts.teacher')

@section('title', 'Тренировочные сессии')

@section('content')
    @php
        $trainingIndexFilterParams = array_filter([
            'q' => ($q ?? '') !== '' ? $q : null,
            'date_from' => $dateFrom ?? null,
            'date_to' => $dateTo ?? null,
            'sport_id' => $sportId ?? null,
            'view' => ($view ?? 'list') !== 'list' ? $view : null,
        ], fn ($v) => $v !== null && $v !== '');
        $trainingStatusUrl = fn (string $f) => route('training-sessions.index', $f === 'all' ? $trainingIndexFilterParams : array_merge($trainingIndexFilterParams, ['filter' => $f]));
        $tsView = $view ?? 'list';
        $trainingFiltersHidden = ['view' => $tsView];
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
                @include('competitions.partials.listing-filters-form', [
                    'action' => route('training-sessions.index'),
                    'resetUrl' => route('training-sessions.index', array_filter(['filter' => ($filter ?? 'all') !== 'all' ? $filter : null])),
                    'q' => $q ?? '',
                    'dateFrom' => $dateFrom ?? null,
                    'dateTo' => $dateTo ?? null,
                    'sportId' => $sportId ?? null,
                    'showPlaceFilter' => false,
                    'sports' => $sportsForFilter ?? collect(),
                    'formId' => 'training-sessions-filters-form',
                    'sportInputId' => 'training-sessions-sport',
                    'liveFilters' => true,
                    'liveAjax' => true,
                    'searchPlaceholder' => 'Название тренировки',
                    'ajaxResetDataAttribute' => 'data-training-sessions-listing-ajax',
                    'hiddenFields' => $trainingFiltersHidden,
                ])
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
        ])
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const VIEW_STORAGE_KEY = 'training_sessions_view';
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

    if (!form || !form.dataset.liveFilters) {
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
        ['q', 'sport_id', 'date_from', 'date_to'].forEach(function (name) {
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
            applyQueryToForm(url.searchParams);
            syncTrainingStatusChips(filterFromTrainingUrlSearchParams(url.searchParams));
            document.dispatchEvent(new CustomEvent('sport-combobox:sync'));
            applyTrainingViewMode(getServerViewMode());

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
        const a = e.target.closest('[data-training-sessions-listing-ajax]');
        if (!a || !a.href || !isTrainingIndexHref(a.href)) return;
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

    if (input) {
        input.addEventListener('input', scheduleDebounced);
        input.addEventListener('search', scheduleDebounced);
    }
    const sport = form.querySelector('input[name="sport_id"]');
    const dateFrom = form.querySelector('input[name="date_from"]');
    const dateTo = form.querySelector('input[name="date_to"]');
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
