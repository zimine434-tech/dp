@props([
    'sessions',
    'filter' => 'all',
    'hasSearchFilters' => false,
    'view' => 'list',
    'perPage' => 50,
    'listingRoute' => 'training-sessions.index',
    'baseListingParams' => [],
    'cardsSortStack' => [],
    'listSortStack' => [],
])

<div
    id="training-sessions-content"
    class="space-y-6 transition-opacity duration-150"
    role="region"
    aria-label="Список тренировок"
>
    @php
        $sessionsCount = method_exists($sessions, 'count') ? (int) $sessions->count() : 0;
        $sessionsHasPages = method_exists($sessions, 'hasPages') ? (bool) $sessions->hasPages() : false;
    @endphp
    @if($hasSearchFilters && $sessionsCount === 0)
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
            По заданным условиям ничего не найдено.
            <a href="{{ route('training-sessions.index', array_filter(['filter' => $filter !== 'all' ? $filter : null])) }}" data-training-sessions-listing-ajax="1" data-training-reset-empty-listing="1" class="font-medium text-blue-600 hover:text-blue-800">Сбросить фильтры</a>.
        </div>
    @elseif($sessionsCount === 0)
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
            @if($filter === 'upcoming')
                Нет предстоящих тренировок.
            @elseif($filter === 'in_progress')
                Нет тренировок, которые идут сейчас.
            @elseif($filter === 'completed')
                Нет завершённых тренировок.
            @elseif($filter === 'cancelled')
                Нет отменённых тренировок.
            @else
                Пока нет ни одной тренировочной сессии — создайте первую.
            @endif
        </div>
    @else
        @include('training-sessions.partials.listing-sessions-body', [
            'sessions' => $sessions,
            'view' => $view,
            'perPage' => $perPage,
            'listingRoute' => $listingRoute,
            'baseListingParams' => $baseListingParams,
            'cardsSortStack' => $cardsSortStack,
            'listSortStack' => $listSortStack,
        ])
    @endif
</div>
