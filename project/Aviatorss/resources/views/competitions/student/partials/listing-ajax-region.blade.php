@php
    $listingPage = $listingPage ?? 'index';
    $listingRoute = $listingRoute ?? ($listingPage === 'my' ? 'competitions.student.my' : 'competitions.index');
    $studentView = $view ?? 'cards';
    if (! in_array($studentView, ['list', 'cards'], true)) {
        $studentView = 'cards';
    }
    $baseWithFilter = array_merge($baseListingParams ?? [], ['filter' => $filter ?? 'upcoming']);
    $cardsSortStack = $cardsSortStack ?? [];
    $listSortStack = $listSortStack ?? [];
@endphp

<div
    id="competitions-student-listing-ajax"
    class="space-y-6 transition-opacity duration-150"
    role="region"
    aria-label="Список соревнований"
    aria-live="polite"
>
    <div id="competitions-student-status-tabs" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-xs font-medium text-gray-500">Статус</p>
        <div class="flex flex-wrap gap-2">
            @foreach(['all' => 'Все', 'upcoming' => 'Предстоящие', 'ongoing' => 'Идут сейчас', 'finished' => 'Завершенные', 'cancelled' => 'Отменённые'] as $statusKey => $statusLabel)
                <a
                    href="{{ $statusRoute($statusKey) }}"
                    data-competitions-student-ajax="1"
                    class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ ($filter ?? 'upcoming') === $statusKey ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    {{ $statusLabel }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="{{ $studentView === 'list' ? 'hidden' : 'mb-4' }}" data-competitions-cards-sort-wrap>
        @include('competitions.student.partials.cards-sort-bar', [
            'listingRoute' => $listingRoute,
            'baseListingParams' => $baseWithFilter,
            'cardsSortStack' => $cardsSortStack,
            'listSortStack' => $listSortStack,
        ])
    </div>

    @if($competitions->total() > 0)
        <div>
            <div data-competitions-list-wrap class="{{ $studentView === 'cards' ? 'hidden' : '' }}">
                <div class="overflow-x-auto rounded-lg bg-white shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 w-[38%] min-w-[10rem] max-w-md">
                                    @include('competitions.student.partials.table-sort-header', [
                                        'listingRoute' => $listingRoute,
                                        'baseListingParams' => $baseWithFilter,
                                        'cardsSortStack' => $cardsSortStack,
                                        'listSortStack' => $listSortStack,
                                        'field' => 'name',
                                        'label' => 'Название',
                                        'defaultOrder' => 'asc',
                                    ])
                                </th>
                                <th class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 md:table-cell">Вид спорта</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                                    @include('competitions.student.partials.table-sort-header', [
                                        'listingRoute' => $listingRoute,
                                        'baseListingParams' => $baseWithFilter,
                                        'cardsSortStack' => $cardsSortStack,
                                        'listSortStack' => $listSortStack,
                                        'field' => 'start_date',
                                        'label' => 'Дата',
                                        'defaultOrder' => 'desc',
                                    ])
                                </th>
                                <th class="hidden px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 lg:table-cell">Локация</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Вид участия</th>
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

            <div class="{{ $listingPage === 'my' ? 'mt-4 ' : '' }}border-t border-gray-100 px-4 py-3">
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <label for="{{ $perPageComboboxTriggerId ?? 'comp_per_page_select_combobox_trigger' }}" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                    <x-per-page-combobox :selected="(int)($perPage ?? 50)" :input-id="$perPageSelectId ?? 'comp_per_page_select'" />
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
            @elseif(($filter ?? '') === 'cancelled')
                <h3 class="mt-2 text-sm font-medium text-gray-900">Отменённых соревнований нет</h3>
                <p class="mt-1 text-sm text-gray-500">Смените фильтры или выберите другой статус.</p>
            @elseif($listingPage === 'my')
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет соревнований</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Вы ещё не участвуете ни в одном соревновании.
                    <a href="{{ route('competitions.index') }}" class="font-medium text-blue-600 hover:text-blue-800">Перейти к соревнованиям</a>
                </p>
            @elseif(($filter ?? '') === 'upcoming')
                <h3 class="mt-2 text-sm font-medium text-gray-900">Предстоящих соревнований пока нет.</h3>
                <p class="mt-1 text-sm text-gray-500">Они появятся после публикации.</p>
            @elseif(($filter ?? '') === 'all')
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет доступных соревнований</h3>
                <p class="mt-1 text-sm text-gray-500">Смените фильтры по дате или виду спорта либо нажмите «Сбросить».</p>
            @else
                <h3 class="mt-2 text-sm font-medium text-gray-900">Нет соревнований по условиям</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Измените статус выше или сбросьте фильтры по дате и виду спорта (кнопка «Сбросить»).
                </p>
            @endif
        </div>
    @endif
</div>
