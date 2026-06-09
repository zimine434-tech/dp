@extends('layouts.student')

@section('title', 'Авиатор')

@section('content')
    @php
        $trainingStatusLabels = [
            'scheduled' => 'Запланирована',
            'upcoming' => 'Запланирована',
            'in_progress' => 'Идёт',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
        ];
        $listingFilters = $listingFilters ?? [];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Посещенные тренировки</h1>
            </div>
            <a
                href="{{ route('profile') }}"
                role="button"
                class="inline-flex shrink-0 items-center justify-center self-start rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
            >
                ← Назад в профиль
            </a>
        </div>

        @php $lf = $listingFilters; @endphp
        <form method="GET" action="{{ route('profile.participations.trainings') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="per_page" id="pt_filter_per_page" value="{{ (int)($perPage ?? request()->query('per_page', 20)) }}">
            <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                    <label for="pt_sport_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                    <x-teacher-sport-combobox
                        :sports="$sportsForFilter"
                        :selected="$lf['sport_id'] ?? null"
                        name="sport_id"
                        input-id="pt_sport"
                        empty-label="Все виды"
                        variant="filter"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <label for="pt_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск по названию</label>
                    <input type="search" id="pt_q" name="q" value="{{ $lf['q'] ?? '' }}" maxlength="200" placeholder="Введите название..." autocomplete="off" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                    <div class="min-w-0 sm:w-40">
                        <label for="pt_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                        <input type="date" id="pt_date_from" name="date_from" value="{{ $lf['date_from'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="min-w-0 sm:w-40">
                        <label for="pt_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                        <input type="date" id="pt_date_to" name="date_to" value="{{ $lf['date_to'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                    <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Применить</button>
                    <a href="{{ route('profile.participations.trainings') }}" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Сбросить</a>
                </div>
            </div>
        </form>

        @if($registrations->isNotEmpty())
            <div class="rounded-lg bg-white p-4 shadow-md sm:p-6">
                <div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($registrations as $registration)
                        @php($t = $registration->training)
                        <a
                            href="{{ route('training-sessions.show', ['trainingSession' => $t]) }}"
                            class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700">{{ $t->title }}</h2>
                            </div>
                            @if($t->start_time)
                                <p class="mt-3 text-sm text-gray-700">
                                    <span class="font-semibold">{{ $t->start_time->translatedFormat('d F Y') }}</span>
                                    <span class="text-gray-600">, {{ $t->start_time->format('H:i') }}</span>
                                </p>
                            @endif
                            @if($t->sport?->name)
                                <p class="mt-2 text-sm text-gray-600">{{ $t->sport?->name ?? '—' }}</p>
                            @endif
                            @if($registration->registered_at)
                                <p class="mt-2 text-sm text-gray-500">
                                    Регистрация: {{ $registration->registered_at->translatedFormat('d F Y') }}, {{ $registration->registered_at->format('H:i') }}
                                </p>
                            @endif
                            <div class="mt-auto flex flex-wrap gap-2 pt-4">
                                @if($t->status)
                                    @php($ts = $trainingStatusLabels[$t->status] ?? $t->status)
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800">{{ $ts }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm font-medium text-blue-600 group-hover:text-blue-800">Подробнее →</p>
                        </a>
                    @endforeach
                    </div>
                    <div class="mt-4 border-t border-gray-100 px-4 py-3">
                        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                            <label for="pt_per_page_select_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                            <x-per-page-combobox :selected="(int)($perPage ?? request()->query('per_page', 20))" input-id="pt_per_page_select" />
                        </div>
                        @if($registrations->hasPages())
                            {{ $registrations->links() }}
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                @php($hasListingFilters = collect($listingFilters)->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isNotEmpty())
                @if($hasListingFilters)
                    <p class="text-sm font-medium text-gray-900">Нет тренировок по выбранным условиям</p>
                    <p class="mt-1 text-sm text-gray-500">Попробуйте изменить фильтры или нажмите «Сбросить».</p>
                @else
                    <p class="text-sm font-medium text-gray-900">Завершённых тренировок с вашей записью пока нет</p>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const PER_PAGE_STORAGE_KEY = 'profile_participations_trainings_per_page';
    const hidden = document.getElementById('pt_filter_per_page');
    if (!hidden) return;

    function syncTrainingsParticipationPerPageBottomFromHidden() {
        const bottom = document.getElementById('pt_per_page_select');
        if (!bottom) return;
        bottom.value = String(hidden.value || '20');
        const root = bottom.closest('[data-filter-combobox]');
        if (root && typeof root._syncFilterCombobox === 'function') {
            root._syncFilterCombobox();
        }
    }

    try {
        const u = new URL(window.location.href);
        if (!u.searchParams.get('per_page')) {
            const stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
            if (stored && stored !== hidden.value) {
                hidden.value = stored;
                syncTrainingsParticipationPerPageBottomFromHidden();
            }
        }
    } catch (e) {}

    document.addEventListener('change', function (e) {
        const target = e.target;
        if (!target || target.id !== 'pt_per_page_select') return;
        hidden.value = String(target.value || hidden.value || '20');
        try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(hidden.value || '20')); } catch (e) {}
        const form = hidden.closest('form');
        if (form) form.submit();
    });
})();
</script>
@endpush
