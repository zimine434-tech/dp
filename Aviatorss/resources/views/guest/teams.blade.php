@extends('layouts.guest')

@section('title', 'Команды')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Команды</h1>
            <p class="mt-1 text-sm text-gray-600 sm:text-base">Публичные составы команд</p>
        </div>

        @if($teams->count() > 0)
            <div
                class="rounded-2xl border border-gray-200/90 bg-gradient-to-br from-white via-white to-slate-50/90 p-5 shadow-md ring-1 ring-black/[0.04] sm:p-6"
                data-guest-teams-filters
            >
                <div class="flex flex-col gap-5 sm:flex-row sm:flex-wrap sm:items-start">
                    <div
                        class="relative min-w-0 flex-1 sm:max-w-xs"
                        data-guest-teams-sport-combo
                        data-selected-sport-ids=""
                    >
                        <label for="guest-teams-sport-trigger" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">Спорт</label>
                        <button
                            type="button"
                            id="guest-teams-sport-trigger"
                            data-guest-teams-sport-trigger
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            aria-controls="guest-teams-sport-panel"
                            class="flex w-full items-center justify-between gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-left text-sm font-medium text-gray-900 shadow-sm transition hover:border-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                        >
                            <span data-guest-teams-sport-trigger-label>Все</span>
                            <svg data-guest-teams-sport-chevron class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            id="guest-teams-sport-panel"
                            data-guest-teams-sport-panel
                            class="absolute left-0 right-0 z-50 mt-1 hidden min-w-[min(100%,18rem)]"
                            role="presentation"
                        >
                            <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-black/5">
                                <ul
                                    id="guest-teams-sport-listbox"
                                    data-guest-teams-sport-list
                                    class="max-h-52 overflow-y-auto overscroll-contain py-1"
                                    role="listbox"
                                    aria-labelledby="guest-teams-sport-trigger"
                                >
                                    <li>
                                        <button
                                            type="button"
                                            role="option"
                                            aria-selected="true"
                                            data-guest-teams-sport-option
                                            data-sport-ids=""
                                            class="w-full px-4 py-2.5 text-left text-sm text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none"
                                        >
                                            Все
                                        </button>
                                    </li>
                                    @foreach($sportFilterOptions as $opt)
                                        <li>
                                            <button
                                                type="button"
                                                role="option"
                                                aria-selected="false"
                                                data-guest-teams-sport-option
                                                data-sport-ids="{{ implode(',', $opt['ids']) }}"
                                                class="w-full px-4 py-2.5 text-left text-sm leading-snug text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none"
                                            >
                                                {{ $opt['name'] }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 sm:max-w-md">
                        <label for="guest-teams-search" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">Поиск по названию команды</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input
                                type="search"
                                id="guest-teams-search"
                                data-guest-teams-search
                                autocomplete="off"
                                placeholder="Начните вводить название…"
                                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 shadow-inner shadow-gray-100/80 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div
                data-guest-teams-empty
                class="hidden rounded-2xl border border-dashed border-gray-300 bg-gray-50/80 px-6 py-14 text-center shadow-inner"
                role="status"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-200/80 text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-medium text-gray-800">Ничего не найдено</p>
                <p class="mt-1 text-sm text-gray-600">Сбросьте фильтр по спорту или измените запрос в поиске.</p>
            </div>

            <div data-guest-teams-grid class="grid grid-cols-1 items-stretch gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                @foreach($teams as $team)
                    <div
                        class="flex h-full min-h-0 min-w-0 flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md ring-1 ring-black/[0.03] transition hover:-translate-y-0.5 hover:shadow-lg"
                        data-guest-team-card
                        data-sport-id="{{ $team->sport_id ?? '' }}"
                        data-team-name="{{ e($team->name) }}"
                    >
                        <div class="flex min-h-0 flex-1 flex-col p-6">
                            <h3 class="mb-2 text-xl font-semibold text-gray-900">
                                <a href="{{ route('guest.teams.show', ['team' => $team]) }}" class="transition hover:text-blue-600">
                                    {{ $team->name }}
                                </a>
                            </h3>
                            <div class="mb-4 flex min-h-0 flex-1 flex-col space-y-3">
                                @if($team->sport)
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Вид спорта:</span>
                                        <a href="{{ route('guest.sports.show', $team->sport) }}" class="text-blue-600 hover:text-blue-800">{{ $team->sport->name }}</a>
                                    </p>
                                @endif
                                @if($team->description)
                                    <p class="line-clamp-3 text-sm text-gray-600">
                                        {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($team->description))), 100) }}
                                    </p>
                                @endif
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">Участников:</span> {{ $team->members->whereNull('out')->count() }}
                                </div>
                            </div>
                            <a
                                href="{{ route('guest.teams.show', ['team' => $team]) }}"
                                class="mt-auto block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                            >
                                Подробнее
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Команд пока нет</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
@if($teams->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var combo = document.querySelector('[data-guest-teams-sport-combo]');
    var trigger = document.querySelector('[data-guest-teams-sport-trigger]');
    var panel = document.querySelector('[data-guest-teams-sport-panel]');
    var triggerLabel = document.querySelector('[data-guest-teams-sport-trigger-label]');
    var chevron = document.querySelector('[data-guest-teams-sport-chevron]');
    var searchInput = document.querySelector('[data-guest-teams-search]');
    var cards = document.querySelectorAll('[data-guest-team-card]');
    var grid = document.querySelector('[data-guest-teams-grid]');
    var empty = document.querySelector('[data-guest-teams-empty]');
    if (!combo || !trigger || !panel || !searchInput || !grid || !empty || !cards.length) return;

    var sportOptions = combo.querySelectorAll('[data-guest-teams-sport-option]');

    function norm(s) {
        return (s || '').toString().trim().toLocaleLowerCase('ru-RU');
    }

    function selectedIdSet() {
        var raw = combo.getAttribute('data-selected-sport-ids') || '';
        if (raw === '') return null;
        var parts = raw.split(',').filter(Boolean);
        return parts.length ? parts : null;
    }

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

    function setSportSelection(btn) {
        var ids = btn.getAttribute('data-sport-ids') || '';
        combo.setAttribute('data-selected-sport-ids', ids);
        sportOptions.forEach(function (b) {
            var on = b === btn;
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            b.classList.toggle('bg-sky-100', on);
            b.classList.toggle('font-semibold', on);
        });
        if (triggerLabel) triggerLabel.textContent = btn.textContent.trim();
    }

    function apply() {
        var idSet = selectedIdSet();
        var q = norm(searchInput.value);
        var visible = 0;
        cards.forEach(function (card) {
            var sid = String(card.getAttribute('data-sport-id') || '');
            var name = norm(card.getAttribute('data-team-name') || '');
            var sportOk = idSet === null || idSet.indexOf(sid) !== -1;
            var nameOk = !q || name.indexOf(q) !== -1;
            var show = sportOk && nameOk;
            card.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        var none = visible === 0;
        empty.classList.toggle('hidden', !none);
        grid.classList.toggle('hidden', none);
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

    sportOptions.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            setSportSelection(btn);
            closePanel();
            apply();
        });
    });

    searchInput.addEventListener('input', apply);

    var firstOpt = combo.querySelector('[data-guest-teams-sport-option]');
    if (firstOpt) setSportSelection(firstOpt);
    apply();
});
</script>
@endif
@endpush