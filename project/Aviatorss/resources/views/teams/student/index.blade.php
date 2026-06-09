@extends('layouts.student')

@section('title', 'Команды')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Команды</h1>
        </div>

        @php
            $listingFilters = $listingFilters ?? [];
            $hasTeamFilters = filled($listingFilters['q'] ?? null) || filled($listingFilters['sport_id'] ?? null);
        @endphp

        @php $lf = $listingFilters; @endphp
        <form method="GET" action="{{ route('teams.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="per_page" id="teams_student_filter_per_page" value="{{ $perPage ?? 12 }}">
            <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                    <label for="teams_student_sport_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                    <x-teacher-sport-combobox
                        :sports="$sportsForFilter ?? []"
                        :selected="$lf['sport_id'] ?? null"
                        name="sport_id"
                        input-id="teams_student_sport"
                        empty-label="Все виды"
                        variant="filter"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <label for="teams_student_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск</label>
                    <input
                        type="search"
                        id="teams_student_q"
                        name="q"
                        value="{{ $lf['q'] ?? '' }}"
                        maxlength="255"
                        placeholder="По названию или описанию…"
                        autocomplete="off"
                        class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                </div>
                <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                    <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">
                        Применить
                    </button>
                    <a href="{{ route('teams.index') }}" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">
                        Сбросить
                    </a>
                </div>
            </div>
        </form>

        <!-- Список команд -->
        @if($teams->count() > 0)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                @foreach($teams as $team)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden flex flex-col h-full">
                        <div class="p-5 sm:p-6 flex flex-col flex-1 min-h-0">
                            <!-- Заголовок и бейдж -->
                            <div class="mb-4">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900 leading-tight flex-1 min-w-0 break-words">{{ $team->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap flex-shrink-0">
                                        @php
                                            $count = $team->members->whereNull('out')->count();
                                            $lastDigit = $count % 10;
                                            $lastTwoDigits = $count % 100;
                                            
                                            if ($count === 0 || ($lastTwoDigits >= 5 && $lastTwoDigits <= 20) || $lastDigit >= 5 || $lastDigit === 0) {
                                                $text = $count . ' участников';
                                            } elseif ($lastDigit === 1) {
                                                $text = $count . ' участник';
                                            } else {
                                                $text = $count . ' участника';
                                            }
                                        @endphp
                                        {{ $text }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Описание -->
                            <div class="mb-4 flex-1 min-h-0">
                                @if(\App\Support\RichTextPlain::filled($team->description))
                                    <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">{{ \App\Support\RichTextPlain::fromHtml($team->description, 240) }}</p>
                                @else
                                    <p class="text-gray-400 text-sm italic">Описание отсутствует</p>
                                @endif
                            </div>

                            <!-- Кнопка подробнее -->
                            <div class="mt-auto">
                                <a 
                                    href="{{ route('teams.show', ['team' => $team]) }}" 
                                    class="block w-full text-center px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors text-sm font-medium shadow-sm"
                                >
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Пагинация + количество -->
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <label for="teams_student_per_page_bottom_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                    <x-per-page-combobox :selected="(int)($perPage ?? 12)" input-id="teams_student_per_page_bottom" />
                </div>
                <div>
                    @if($teams->hasPages())
                        {{ $teams->links() }}
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                @if($hasTeamFilters)
                    <h3 class="mt-2 text-sm font-medium text-gray-900">По вашему запросу ничего не найдено</h3>
                    <p class="mt-1 text-sm text-gray-500">Измените поиск или нажмите «Сбросить».</p>
                @else
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет команд</h3>
                    <p class="mt-1 text-sm text-gray-500">Команды пока не созданы.</p>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const PER_PAGE_STORAGE_KEY = 'teams_student_per_page';
    const perPageHidden = document.getElementById('teams_student_filter_per_page');

    function syncTeamsStudentPerPageBottomFromHidden() {
        if (!perPageHidden) return;
        const bottom = document.getElementById('teams_student_per_page_bottom');
        if (!bottom) return;
        bottom.value = String(perPageHidden.value || '12');
        const root = bottom.closest('[data-filter-combobox]');
        if (root && typeof root._syncFilterCombobox === 'function') {
            root._syncFilterCombobox();
        }
    }

    try {
        const u = new URL(window.location.href);
        if (!u.searchParams.get('per_page')) {
            const stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
            if (stored && perPageHidden && stored !== perPageHidden.value) {
                perPageHidden.value = stored;
                syncTeamsStudentPerPageBottomFromHidden();
                const form = perPageHidden.closest('form');
                if (form) {
                    form.submit();
                }
            }
        }
    } catch (e) {}

    document.addEventListener('change', function (e) {
        const target = e.target;
        if (!target || target.id !== 'teams_student_per_page_bottom') return;
        const v = String(target.value || '12');
        try { localStorage.setItem(PER_PAGE_STORAGE_KEY, v); } catch (e2) {}
        if (perPageHidden) {
            perPageHidden.value = v;
            const form = perPageHidden.closest('form');
            if (form) {
                form.submit();
                return;
            }
        }
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', v);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });
})();
</script>
@endpush

