@php
    use App\Support\NewsListingSort;

    $cardsSortStack = $cardsSortStack ?? [];
    $listingRoute = $listingRoute ?? 'news.index';
    $baseListingParams = $baseListingParams ?? [];
    $sortLinkExtra = $sortLinkExtra ?? ['page' => 1];

    $sortUrl = function (string $field, string $order) use ($listingRoute, $baseListingParams, $cardsSortStack, $sortLinkExtra): string {
        $newStack = NewsListingSort::toggleCriterion($cardsSortStack, $field, $order);

        return NewsListingSort::listingUrl(
            $listingRoute,
            $baseListingParams,
            $newStack,
            $sortLinkExtra
        );
    };

    $pillClass = function (string $field, string $expectedOrder) use ($cardsSortStack): string {
        $active = NewsListingSort::orderForField($cardsSortStack, $field) === $expectedOrder;

        return $active
            ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700'
            : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100';
    };
@endphp

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm" data-news-sort-bar>
    <p class="mb-3 text-xs font-medium text-gray-500">Сортировка</p>
    <div class="flex flex-wrap gap-2">
        <a
            href="{{ $sortUrl('name', 'asc') }}"
            data-news-sort-link="1"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('name', 'asc') }}"
        >
            По названию (А→Я)
        </a>
        <a
            href="{{ $sortUrl('name', 'desc') }}"
            data-news-sort-link="1"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('name', 'desc') }}"
        >
            По названию (Я→А)
        </a>
        <a
            href="{{ $sortUrl('date', 'desc') }}"
            data-news-sort-link="1"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('date', 'desc') }}"
        >
            По дате (сначала поздние)
        </a>
        <a
            href="{{ $sortUrl('date', 'asc') }}"
            data-news-sort-link="1"
            class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $pillClass('date', 'asc') }}"
        >
            По дате (сначала ранние)
        </a>
    </div>
</div>
