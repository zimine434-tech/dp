@php
    use App\Support\StudentCompetitionListingSort;

    $resultsView = $resultsView ?? 'list';
    $cardsSortStack = $cardsSortStack ?? [];
    $listSortStack = $listSortStack ?? [];
    $resultsBaseParams = $resultsBaseParams ?? [];
    $listingAjaxAttr = 'data-competitions-results-ajax';
    $sortLinkExtra = ['page' => 1, 'ongoing_page' => 1];
@endphp

<div class="{{ $resultsView === 'list' ? 'hidden' : 'mb-4' }}" data-results-cards-sort-wrap>
    @include('competitions.student.partials.cards-sort-bar', [
        'listingRoute' => 'competitions.results',
        'baseListingParams' => $resultsBaseParams,
        'cardsSortStack' => $cardsSortStack,
        'listSortStack' => $listSortStack,
        'listingAjaxAttr' => $listingAjaxAttr,
        'sortLinkExtra' => $sortLinkExtra,
    ])
</div>
