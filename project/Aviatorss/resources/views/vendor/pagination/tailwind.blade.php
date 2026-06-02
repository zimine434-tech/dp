@if ($paginator->hasPages())
    @include('partials.pagination-nav', [
        'paginator' => $paginator,
        'ariaLabel' => __('Pagination Navigation'),
    ])
@endif
