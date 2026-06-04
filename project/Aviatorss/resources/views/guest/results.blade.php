@extends('layouts.guest')

@section('title', 'Результаты соревнований')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Результаты соревнований</h1>
            </div>
        </div>

        <div
            class="rounded-2xl border border-gray-200/90 bg-gradient-to-br from-white via-white to-slate-50/90 p-5 shadow-md ring-1 ring-black/[0.04] sm:p-6"
            data-guest-results-filters
        >
            <div class="flex flex-col gap-5 sm:flex-row sm:flex-wrap sm:items-start">
                @if(count($sportFilterOptions) > 0)
                <div class="relative min-w-0 flex-1 sm:max-w-xs" data-guest-results-sport-combo data-selected-sport-ids="{{ $selectedSportQuery }}">
                    <label for="guest-results-sport-trigger" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">Спорт</label>
                    <button
                        type="button"
                        id="guest-results-sport-trigger"
                        data-guest-results-sport-trigger
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        aria-controls="guest-results-sport-panel"
                        class="flex w-full items-center justify-between gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-left text-sm font-medium text-gray-900 shadow-sm transition hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                    >
                        <span data-guest-results-sport-trigger-label>{{ $selectedSportLabel }}</span>
                        <svg data-guest-results-sport-chevron class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        id="guest-results-sport-panel"
                        data-guest-results-sport-panel
                        class="absolute left-0 right-0 z-50 mt-1 hidden min-w-[min(100%,18rem)]"
                        role="presentation"
                    >
                        <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-black/5">
                            <ul
                                id="guest-results-sport-listbox"
                                data-guest-results-sport-list
                                class="max-h-52 overflow-y-auto overscroll-contain py-1"
                                role="listbox"
                                aria-labelledby="guest-results-sport-trigger"
                            >
                                <li>
                                    <button
                                        type="button"
                                        role="option"
                                        aria-selected="{{ $selectedSportQuery === '' ? 'true' : 'false' }}"
                                        data-guest-results-sport-option
                                        data-sport-ids=""
                                        class="w-full px-4 py-2.5 text-left text-sm text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none {{ $selectedSportQuery === '' ? 'bg-sky-100 font-semibold' : '' }}"
                                    >
                                        Все
                                    </button>
                                </li>
                                @foreach($sportFilterOptions as $opt)
                                    @php
                                        $optKey = collect($opt['ids'])->sort()->values()->implode(',');
                                        $isOptSelected = $selectedSportQuery !== '' && $selectedSportQuery === $optKey;
                                    @endphp
                                    <li>
                                        <button
                                            type="button"
                                            role="option"
                                            aria-selected="{{ $isOptSelected ? 'true' : 'false' }}"
                                            data-guest-results-sport-option
                                            data-sport-ids="{{ $optKey }}"
                                            class="w-full px-4 py-2.5 text-left text-sm leading-snug text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none {{ $isOptSelected ? 'bg-sky-100 font-semibold' : '' }}"
                                        >
                                            {{ $opt['name'] }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                <div class="min-w-0 flex-1 sm:max-w-md">
                    <label for="guest-results-name-q" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">Поиск по названию</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            id="guest-results-name-q"
                            data-guest-results-name-q
                            value="{{ e($searchQ) }}"
                            autocomplete="off"
                            placeholder="Название соревнования…"
                            class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 shadow-inner shadow-gray-100/80 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                        />
                    </div>
                </div>
            </div>
        </div>

        @if($results->total() > 0)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                @foreach($results as $result)
                    <article class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                        @include('news.partials.news-cover', ['item' => $result->competition, 'stacked' => true])
                        <div class="flex min-w-0 flex-1 flex-col p-5">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <h3 class="min-w-0 flex-1 text-lg font-semibold leading-snug text-gray-900">
                                    <a href="{{ route('guest.results.show', ['competition' => $result->competition]) }}" class="transition hover:text-blue-600">
                                        {{ $result->competition->name }}
                                    </a>
                                </h3>
                                <span
                                    class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        @if(is_numeric($result->place) && (int) $result->place === 1) bg-yellow-100 text-yellow-800
                                        @elseif(is_numeric($result->place) && (int) $result->place === 2) bg-gray-200 text-gray-800
                                        @elseif(is_numeric($result->place) && (int) $result->place === 3) bg-orange-100 text-orange-800
                                        @else bg-blue-100 text-blue-800
                                        @endif"
                                >
                                    Место: {{ $result->place }}
                                </span>
                            </div>
                            <p class="mb-3 line-clamp-3 flex-1 text-sm text-gray-600">
                                @if(filled($result->competition->description))
                                    {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($result->competition->description ?? '')))), 150) }}
                                @else
                                    {{ $result->competition->sport?->name ?? 'Соревнование' }}
                                    @if($result->competition->participants->count() > 0)
                                        · участников: {{ $result->competition->participants->count() }}
                                    @endif
                                @endif
                            </p>
                            <div class="mb-3 space-y-1 text-xs text-gray-600 sm:text-sm">
                                <p>
                                    <span class="font-medium text-gray-700">Вид участия:</span>
                                    {{ $result->competition->resultFormatLabel() }}
                                </p>
                                <p>
                                    <span class="font-medium text-gray-700">Вид спорта:</span>
                                    {{ $result->competition->sport?->name ?? '—' }}
                                </p>
                                <p>
                                    <span class="font-medium text-gray-700">Категория:</span>
                                    {{ $result->competition->category?->name_category ?? '—' }}
                                </p>
                            </div>
                            <div class="mb-4 text-sm text-gray-500">
                                @php
                                    $cDates = $result->competition;
                                @endphp
                                @if($cDates->start_date && $cDates->end_date)
                                    @if($cDates->start_date->format('Y-m-d') === $cDates->end_date->format('Y-m-d'))
                                        <span>Дата: {{ $cDates->start_date->format('d.m.Y') }}</span>
                                    @else
                                        <span>Дата: {{ $cDates->start_date->format('d.m.Y') }} - {{ $cDates->end_date->format('d.m.Y') }}</span>
                                    @endif
                                @elseif($cDates->start_date)
                                    <span>Дата: {{ $cDates->start_date->format('d.m.Y') }}</span>
                                @elseif($cDates->end_date)
                                    <span>Дата: {{ $cDates->end_date->format('d.m.Y') }}</span>
                                @else
                                    <span class="text-gray-400">Дата не указана</span>
                                @endif
                            </div>
                            <a
                                href="{{ route('guest.results.show', ['competition' => $result->competition]) }}"
                                class="mt-auto block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Подробнее
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($results->hasPages())
                <div class="mt-8 pt-2">
                    {{ $results->links() }}
                </div>
            @endif
        @elseif($selectedSportQuery !== '' || $searchQ !== '')
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50/80 px-6 py-12 text-center shadow-inner">
                <p class="text-sm font-medium text-gray-800">Ничего не найдено</p>
                <p class="mt-2 text-sm text-gray-600">Измените вид спорта, запрос в поиске или сбросьте фильтры.</p>
                <a href="{{ route('guest.results') }}" class="mt-4 inline-block text-sm font-medium text-blue-600 hover:text-blue-800">Показать все результаты</a>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет результатов</h3>
                <p class="mt-1 text-sm text-gray-500">Результаты соревнований появятся здесь после их добавления.</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
@if(count($sportFilterOptions) > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var combo = document.querySelector('[data-guest-results-sport-combo]');
    var trigger = document.querySelector('[data-guest-results-sport-trigger]');
    var panel = document.querySelector('[data-guest-results-sport-panel]');
    var chevron = document.querySelector('[data-guest-results-sport-chevron]');
    if (!combo || !trigger || !panel) return;

    function isPanelOpen() {
        return !panel.classList.contains('hidden');
    }

    function openPanel() {
        panel.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');
        if (chevron) chevron.classList.add('rotate-180');
    }

    function closePanel() {
        panel.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function navigateSport(ids) {
        var u = new URL(window.location.href);
        if (!ids) {
            u.searchParams.delete('sport');
        } else {
            u.searchParams.set('sport', ids);
        }
        u.searchParams.delete('page');
        window.location.href = u.pathname + u.search;
    }

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        if (isPanelOpen()) closePanel();
        else openPanel();
    });

    panel.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    document.addEventListener('click', function () {
        if (isPanelOpen()) closePanel();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isPanelOpen()) {
            closePanel();
            trigger.focus();
        }
    });

    document.querySelectorAll('[data-guest-results-sport-option]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            navigateSport(btn.getAttribute('data-sport-ids') || '');
        });
    });
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('[data-guest-results-name-q]');
    if (!input) return;

    var debounceMs = 400;
    var t;
    var lastSent = (input.value || '').trim();

    input.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () {
            var v = (input.value || '').trim();
            if (v === lastSent) return;
            lastSent = v;
            var u = new URL(window.location.href);
            if (v) {
                u.searchParams.set('q', v);
            } else {
                u.searchParams.delete('q');
            }
            u.searchParams.delete('page');
            window.location.href = u.toString();
        }, debounceMs);
    });
});
</script>
@endpush
