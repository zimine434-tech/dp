@props([
    'sessions',
    'view' => 'list',
    'perPage' => 50,
    'listingRoute' => 'training-sessions.index',
    'baseListingParams' => [],
    'cardsSortStack' => [],
    'listSortStack' => [],
    'rowPartial' => 'training-sessions.partials.session-row',
    'cardPartial' => 'training-sessions.partials.session-card-teacher',
    'listingAjaxAttr' => 'data-training-sessions-listing-ajax',
])

@php
    $tsView = $view;
    $baseWithFilter = $baseListingParams;
@endphp

<div class="space-y-4">
<div class="{{ $tsView === 'list' ? 'hidden' : '' }}" data-training-sessions-cards-sort-wrap>
    @include('training-sessions.partials.cards-sort-bar', [
        'listingRoute' => $listingRoute,
        'baseListingParams' => $baseWithFilter,
        'cardsSortStack' => $cardsSortStack,
        'listSortStack' => $listSortStack,
        'listingAjaxAttr' => $listingAjaxAttr,
    ])
</div>

<div data-training-sessions-list-wrap class="{{ $tsView === 'cards' ? 'hidden' : '' }}">
    <div class="overflow-x-auto rounded-lg bg-white shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        @include('training-sessions.partials.table-sort-header', [
                            'listingRoute' => $listingRoute,
                            'baseListingParams' => $baseWithFilter,
                            'cardsSortStack' => $cardsSortStack,
                            'listSortStack' => $listSortStack,
                            'field' => 'name',
                            'label' => 'Название',
                            'defaultOrder' => 'asc',
                            'listingAjaxAttr' => $listingAjaxAttr,
                        ])
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden md:table-cell">Спорт / команда</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                        @include('training-sessions.partials.table-sort-header', [
                            'listingRoute' => $listingRoute,
                            'baseListingParams' => $baseWithFilter,
                            'cardsSortStack' => $cardsSortStack,
                            'listSortStack' => $listSortStack,
                            'field' => 'start_time',
                            'label' => 'Дата и время',
                            'defaultOrder' => 'desc',
                            'listingAjaxAttr' => $listingAjaxAttr,
                        ])
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 hidden lg:table-cell">Локация</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Статус</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($sessions as $session)
                    @include($rowPartial, ['session' => $session])
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div data-training-sessions-cards-wrap class="{{ $tsView === 'list' ? 'hidden' : '' }}">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($sessions as $session)
            <div class="min-w-0">
                @include($cardPartial, ['session' => $session])
            </div>
        @endforeach
    </div>
</div>

<div id="training-sessions-pagination" class="flex justify-end pt-2">
    <div class="mr-auto flex items-center gap-2">
        <label for="training-sessions-per-page-bottom_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
        <x-per-page-combobox :selected="(int)($perPage ?? 50)" input-id="training-sessions-per-page-bottom" />
    </div>
    @if(method_exists($sessions, 'hasPages') && $sessions->hasPages())
        {{ $sessions->links() }}
    @endif
</div>
</div>
