@include('partials.pagination-nav', [
    'currentPage' => $currentPage ?? 1,
    'lastPage' => $lastPage ?? 1,
    'route' => $route ?? '',
    'query' => $query ?? null,
    'ariaLabel' => $ariaLabel ?? 'Пагинация',
    'windowSize' => $windowSize ?? 4,
])
