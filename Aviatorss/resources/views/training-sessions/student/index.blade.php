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
        $filter = $filter ?? 'all';
        $listingFilters = $listingFilters ?? [];
        $studentView = $view ?? 'cards';
        if (! in_array($studentView, ['list', 'cards'], true)) {
            $studentView = 'cards';
        }
        $queryForStatus = array_filter([
            'sport_id' => $listingFilters['sport_id'] ?? null,
            'date_from' => $listingFilters['date_from'] ?? null,
            'date_to' => $listingFilters['date_to'] ?? null,
            'q' => ($listingFilters['q'] ?? '') !== '' ? $listingFilters['q'] : null,
            'view' => $studentView === 'list' ? 'list' : null,
        ], fn ($v) => $v !== null && $v !== '');
        $statusRoute = fn (string $f) => route('training-sessions.index', $f === 'all' ? $queryForStatus : array_merge($queryForStatus, ['filter' => $f]));
        $resetListingUrl = route('training-sessions.index', array_filter([
            'filter' => $filter !== 'all' ? $filter : null,
            'view' => $studentView === 'list' ? 'list' : null,
        ]));
    @endphp
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Тренировочные сессии</h1>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                <x-student-listing-filters-bar
                    :action="route('training-sessions.index')"
                    :reset-url="$resetListingUrl"
                    :listing-filters="$listingFilters"
                    :sports-for-filter="$sportsForFilter"
                    id-prefix="ts"
                    :debounce-search-submit="true"
                >
                    <input type="hidden" name="view" id="ts_filter_view" value="{{ $studentView }}">
                    @if($filter !== 'all')
                        <input type="hidden" name="filter" value="{{ $filter }}">
                    @endif
                </x-student-listing-filters-bar>
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
                <div data-training-sessions-list-wrap class="{{ $studentView === 'cards' ? 'hidden' : '' }}">
                    <div class="overflow-x-auto rounded-lg bg-white shadow-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Название</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden md:table-cell">Спорт / команда</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Дата и время</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden lg:table-cell">Локация</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Статус</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($allTrainingSessions as $session)
                                    @include('training-sessions.student.partials.session-row', ['session' => $session])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div data-training-sessions-cards-wrap class="{{ $studentView === 'list' ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                        @foreach($allTrainingSessions as $session)
                            @include('training-sessions.student.partials.session-card', ['session' => $session])
                        @endforeach
                    </div>
                </div>
                @if($allTrainingSessions->hasPages())
                    <div class="mt-4 border-t border-gray-100 px-4 py-3">
                        {{ $allTrainingSessions->links('pagination::tailwind') }}
                    </div>
                @endif
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
})();
</script>
@endpush
