@php
    use App\Support\SportListingSort;

    $cardsSortStack = $cardsSortStack ?? [];
    $listSortStack = $listSortStack ?? [];
    $listingRoute = $listingRoute ?? 'sports.index';
    $baseListingParams = $baseListingParams ?? [];
    $sortLinkExtra = $sortLinkExtra ?? ['page' => 1];

    $sortUrl = function (string $field, string $order) use ($listingRoute, $baseListingParams, $cardsSortStack, $listSortStack, $sortLinkExtra): string {
        $newCardsStack = SportListingSort::toggleCardCriterion($cardsSortStack, $field, $order);

        return SportListingSort::listingUrl(
            $listingRoute,
            $baseListingParams,
            $newCardsStack,
            $listSortStack,
            $sortLinkExtra
        );
    };

    $pillClass = function (string $field, string $expectedOrder) use ($cardsSortStack): string {
        $active = SportListingSort::orderForField($cardsSortStack, $field) === $expectedOrder;

        return $active
            ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700'
            : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100';
    };
@endphp

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm" data-sports-cards-sort>
    <p class="mb-3 text-xs font-medium text-gray-500">Сортировка</p>
    <div class="flex flex-wrap gap-2">
        <a
            href="{{ $sortUrl('name', 'asc') }}"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('name', 'asc') }}"
        >
            По названию (А→Я)
        </a>
        <a
            href="{{ $sortUrl('name', 'desc') }}"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('name', 'desc') }}"
        >
            По названию (Я→А)
        </a>
        <a
            href="{{ $sortUrl('created_at', 'desc') }}"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('created_at', 'desc') }}"
        >
            По дате (сначала новые)
        </a>
        <a
            href="{{ $sortUrl('created_at', 'asc') }}"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('created_at', 'asc') }}"
        >
            По дате (сначала старые)
        </a>
    </div>
</div>
