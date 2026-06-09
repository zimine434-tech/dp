@extends('layouts.student')

@section('title', 'Портфолио')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Портфолио</h1>
            </div>
            <a
                href="{{ route('profile') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium whitespace-nowrap"
            >
                Назад в профиль
            </a>
        </div>

        @php
            $portfolioView = ($view ?? 'list') === 'cards' ? 'cards' : 'list';
            $participationType = $participationType ?? '';
            $portfolioListQuery = $portfolioListQuery ?? [];
            $hasFilters = ($q ?? '') !== '' || ($sportId ?? null) || ($participationType !== '') || ($dateFrom ?? null) || ($dateTo ?? null);
            $portfolioShowUrl = function ($competition, $result) use ($portfolioListQuery) {
                $kind = \App\Http\Controllers\StudentPortfolioController::participationKind($result);

                return route('profile.portfolio.show', array_merge(
                    ['competition' => $competition, 'participation' => $kind],
                    $portfolioListQuery
                ));
            };
        @endphp

        <div class="flex flex-col gap-3 lg:flex-row lg:items-stretch lg:gap-4">
            <div class="flex min-w-0 flex-1 flex-col">
                <form
                    id="portfolio-filters-form"
                    method="get"
                    action="{{ route('profile.portfolio') }}"
                    class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
                >
                    <input type="hidden" name="view" id="portfolio-view-input" value="{{ $portfolioView }}">
                    <input type="hidden" name="page" value="1">
                    <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                        <div class="min-w-0 flex-1">
                            <label for="portfolio-q" class="mb-1 block text-sm font-medium text-gray-700">Поиск</label>
                            <input
                                id="portfolio-q"
                                type="search"
                                name="q"
                                value="{{ $q ?? '' }}"
                                placeholder="Название соревнования"
                                autocomplete="off"
                                maxlength="255"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        <div class="w-full min-w-0 shrink-0 sm:w-36 lg:w-40">
                            <label for="portfolio-participation-type_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Участие</label>
                            <x-filter-combobox
                                name="participation_type"
                                :selected="$participationType"
                                :options="[
                                    ['value' => '', 'label' => 'Все'],
                                    ['value' => 'personal', 'label' => 'Личное'],
                                    ['value' => 'team', 'label' => 'Командное'],
                                ]"
                                input-id="portfolio-participation-type"
                                variant="filter"
                            />
                        </div>
                        <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                            <label for="portfolio-sport-id_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                            <x-teacher-sport-combobox
                                :sports="$sportsForFilter ?? []"
                                :selected="$sportId ?? null"
                                name="sport_id"
                                input-id="portfolio-sport-id"
                                empty-label="Все виды"
                                variant="filter"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                            <div class="min-w-0 sm:w-40">
                                <label for="portfolio-date-from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                                <input
                                    id="portfolio-date-from"
                                    type="date"
                                    name="date_from"
                                    value="{{ $dateFrom ?? '' }}"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                            </div>
                            <div class="min-w-0 sm:w-40">
                                <label for="portfolio-date-to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                                <input
                                    id="portfolio-date-to"
                                    type="date"
                                    name="date_to"
                                    value="{{ $dateTo ?? '' }}"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                            </div>
                        </div>
                        <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                            <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">
                                Применить
                            </button>
                            <a href="{{ route('profile.portfolio') }}" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">
                                Сбросить
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex shrink-0 flex-col">
                <div class="flex h-full w-full shrink-0 flex-col justify-end rounded-lg bg-white px-4 pb-3.5 pt-3 shadow-md sm:w-auto" role="group" aria-label="Вид отображения портфолио">
                    <div>
                        <span class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Вид</span>
                        <div class="inline-flex h-10 w-full items-center rounded-lg border border-gray-200 bg-gray-50 p-0.5 sm:w-auto" role="tablist">
                            <button
                                type="button"
                                id="portfolio-view-list"
                                class="portfolio-view-toggle inline-flex h-9 flex-1 items-center justify-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:flex-none {{ $portfolioView === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                                data-view="list"
                                role="tab"
                                aria-selected="{{ $portfolioView === 'list' ? 'true' : 'false' }}"
                            >
                                Список
                            </button>
                            <button
                                type="button"
                                id="portfolio-view-cards"
                                class="portfolio-view-toggle inline-flex h-9 flex-1 items-center justify-center rounded-md px-4 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:flex-none {{ $portfolioView === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                                data-view="cards"
                                role="tab"
                                aria-selected="{{ $portfolioView === 'cards' ? 'true' : 'false' }}"
                            >
                                Карточки
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($hasFilters && ($results->total() ?? 0) === 0)
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
                По заданным условиям поиска и фильтров ничего не найдено.
                <a href="{{ route('profile.portfolio') }}" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>.
            </div>
        @elseif(($results->total() ?? 0) === 0)
            <div class="bg-white rounded-lg shadow-md p-12 text-center text-gray-600">
                Пока нет соревнований с занятыми местами.
            </div>
        @else
            <div data-portfolio-list class="{{ $portfolioView === 'cards' ? 'hidden' : '' }} bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Соревнование</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Участие</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Место</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden md:table-cell">Даты</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($results as $result)
                        @php
                            $competition = $result->competition;
                            $participationLabel = \App\Http\Controllers\StudentPortfolioController::participationKind($result) === 'personal' ? 'Личное' : 'Командное';
                            $place = trim((string) ($result->place ?? ''));
                            $dates = $competition && $competition->start_date && $competition->end_date
                                ? ($competition->start_date->format('d.m.Y') === $competition->end_date->format('d.m.Y')
                                    ? $competition->start_date->format('d.m.Y')
                                    : ($competition->start_date->format('d.m.Y').' – '.$competition->end_date->format('d.m.Y')))
                                : '—';
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <div class="font-medium">{{ $competition?->name ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $participationLabel }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $place !== '' ? $place : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">{{ $dates }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if($competition)
                                    <a href="{{ $portfolioShowUrl($competition, $result) }}" class="font-medium text-blue-600 hover:text-blue-800">Подробнее</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-600">
                                На этой странице нет записей.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div data-portfolio-cards class="{{ $portfolioView === 'list' ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($results as $result)
                            @php
                                $competition = $result->competition;
                                $participationLabel = \App\Http\Controllers\StudentPortfolioController::participationKind($result) === 'personal' ? 'Личное' : 'Командное';
                                $place = trim((string) ($result->place ?? ''));
                                $dates = $competition && $competition->start_date && $competition->end_date
                                    ? ($competition->start_date->format('d.m.Y') === $competition->end_date->format('d.m.Y')
                                        ? $competition->start_date->format('d.m.Y')
                                        : ($competition->start_date->format('d.m.Y').' – '.$competition->end_date->format('d.m.Y')))
                                    : '—';
                            @endphp
                            <div class="min-w-0 rounded-xl border border-gray-100 bg-white p-5 shadow-md">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 break-words">{{ $competition?->name ?? '—' }}</div>
                                        <div class="mt-1 text-xs text-gray-600">{{ $dates }}</div>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">{{ $participationLabel }}</span>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Место: <span class="font-semibold text-gray-900">{{ $place !== '' ? $place : '—' }}</span>
                                    </div>
                                </div>

                                @if($competition)
                                    <a href="{{ $portfolioShowUrl($competition, $result) }}" class="mt-4 inline-flex text-sm font-medium text-blue-600 hover:text-blue-800">
                                        Подробнее →
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
            </div>

            @if($results->total() > 0)
                <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <label for="portfolio-per-page_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                        <x-per-page-combobox
                            :selected="(int) ($perPage ?? 10)"
                            input-id="portfolio-per-page"
                            name="per_page"
                            html-form="portfolio-filters-form"
                        />
                    </div>
                    @if($results->hasPages())
                        <div class="flex justify-end">
                            {{ $results->links() }}
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const STORAGE_KEY = 'student_portfolio_view';
    const form = document.getElementById('portfolio-filters-form');
    const viewInput = document.getElementById('portfolio-view-input');

    function setView(view) {
        if (!viewInput) return;
        viewInput.value = view === 'cards' ? 'cards' : 'list';
        try { localStorage.setItem(STORAGE_KEY, viewInput.value); } catch (e) {}
    }

    function applyView(view) {
        const isCards = view === 'cards';
        document.querySelectorAll('[data-portfolio-list]').forEach(el => el.classList.toggle('hidden', isCards));
        document.querySelectorAll('[data-portfolio-cards]').forEach(el => el.classList.toggle('hidden', !isCards));
        const btnList = document.getElementById('portfolio-view-list');
        const btnCards = document.getElementById('portfolio-view-cards');
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

    if (!form || !viewInput) return;

    // If no explicit view in query, use stored preference.
    try {
        const url = new URL(window.location.href);
        if (!url.searchParams.get('view')) {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored === 'cards' || stored === 'list') {
                viewInput.value = stored;
                applyView(stored);
            }
        }
    } catch (e) {}

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.portfolio-view-toggle');
        if (!btn) return;
        e.preventDefault();
        const view = btn.getAttribute('data-view');
        if (view !== 'list' && view !== 'cards') return;
        setView(view);
        applyView(view);
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });

    document.addEventListener('change', function (e) {
        const target = e.target;
        if (!target || target.id !== 'portfolio-per-page') return;
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });

})();
</script>
@endpush
