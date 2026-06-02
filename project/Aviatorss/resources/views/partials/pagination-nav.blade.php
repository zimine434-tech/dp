@php
    use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;

    $paginatorInstance = $paginator ?? null;
    $current = max(1, (int) ($currentPage ?? ($paginatorInstance?->currentPage() ?? 1)));
    $last = max(1, (int) ($lastPage ?? ($paginatorInstance?->lastPage() ?? 1)));
    $window = max(1, (int) ($windowSize ?? 4));
    $routeName = $route ?? '';
    $queryBase = collect($query ?? request()->query())->except(['fragment', 'ajax'])->all();
    $aria = $ariaLabel ?? 'Пагинация';

    $pageUrl = static function (int $page) use ($paginatorInstance, $routeName, $queryBase) {
        if ($paginatorInstance instanceof PaginatorContract) {
            return $paginatorInstance->url($page);
        }

        return route($routeName, array_merge($queryBase, ['page' => $page]));
    };

    $btnBase = 'inline-flex min-w-[2.5rem] items-center justify-center rounded-lg px-3 py-2 text-sm font-medium transition';
    $btnActive = 'bg-blue-600 text-white shadow-sm';
    $btnIdle = 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50';
    $btnDisabled = 'border border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed pointer-events-none';

    $start = max(1, min($current - (int) floor(($window - 1) / 2), $last - $window + 1));
    $end = min($last, $start + $window - 1);
    $start = max(1, $end - $window + 1);

    $canRender = $last > 1 && ($paginatorInstance instanceof PaginatorContract || $routeName !== '');
@endphp

@if($canRender)
    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="{{ $aria }}">
        @if($current > 1)
            <a href="{{ $pageUrl($current - 1) }}" class="{{ $btnBase }} {{ $btnIdle }}" aria-label="Предыдущая страница" rel="prev">‹</a>
        @else
            <span class="{{ $btnBase }} {{ $btnDisabled }}" aria-hidden="true">‹</span>
        @endif

        @if($start > 1)
            <a href="{{ $pageUrl(1) }}" class="{{ $btnBase }} {{ $btnIdle }}">1</a>
            @if($start > 2)
                <span class="px-1 text-sm text-gray-500" aria-hidden="true">…</span>
            @endif
        @endif

        @for($p = $start; $p <= $end; $p++)
            <a href="{{ $pageUrl($p) }}" class="{{ $btnBase }} {{ $p === $current ? $btnActive : $btnIdle }}" @if($p === $current) aria-current="page" @endif>{{ $p }}</a>
        @endfor

        @if($end < $last)
            @if($end < $last - 1)
                <span class="px-1 text-sm text-gray-500" aria-hidden="true">…</span>
            @endif
            <a href="{{ $pageUrl($last) }}" class="{{ $btnBase }} {{ $btnIdle }}">{{ $last }}</a>
        @endif

        @if($current < $last)
            <a href="{{ $pageUrl($current + 1) }}" class="{{ $btnBase }} {{ $btnIdle }}" aria-label="Следующая страница" rel="next">›</a>
        @else
            <span class="{{ $btnBase }} {{ $btnDisabled }}" aria-hidden="true">›</span>
        @endif
    </nav>
@endif
