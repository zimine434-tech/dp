@php
    use App\Support\StudentCompetitionListingSort;

    $cardsSortStack = $cardsSortStack ?? [];
    $listSortStack = $listSortStack ?? [];
    $listingRoute = $listingRoute ?? 'competitions.index';
    $baseListingParams = $baseListingParams ?? [];
    $field = $field ?? 'start_date';
    $label = $label ?? '';
    $defaultOrder = $defaultOrder ?? 'asc';
    $listingAjaxAttr = $listingAjaxAttr ?? 'data-competitions-student-ajax';
    $sortLinkExtra = $sortLinkExtra ?? ['page' => 1];

    $newListStack = StudentCompetitionListingSort::normalizeListStack(
        StudentCompetitionListingSort::cycleTableColumn($listSortStack, $field, $defaultOrder)
    );
    $href = StudentCompetitionListingSort::listingUrl(
        $listingRoute,
        $baseListingParams,
        $cardsSortStack,
        $newListStack,
        $sortLinkExtra
    );

    $activeOrder = StudentCompetitionListingSort::orderForField($listSortStack, $field);
    $priority = null;
    foreach ($listSortStack as $index => $item) {
        if ($item['field'] === $field) {
            $priority = $index + 1;
            break;
        }
    }
@endphp

<a href="{{ $href }}" {{ $listingAjaxAttr }}="1" class="inline-flex cursor-pointer items-center hover:text-gray-900" title="Нажмите: по возрастанию → по убыванию → без сортировки">
    <span>{{ $label }}</span>
    @if($priority !== null && count($listSortStack) > 1)
        <span class="ml-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-blue-100 px-1 text-[10px] font-semibold text-blue-800">{{ $priority }}</span>
    @endif
    @include('competitions.student.partials.sort-arrow', [
        'active' => $activeOrder !== null,
        'order' => $activeOrder ?? 'asc',
        'showInactive' => true,
    ])
</a>
