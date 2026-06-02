@php
    $variant = $variant ?? 'published';
    $hasSearch = $hasSearch ?? false;
@endphp

<div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
    @if($variant === 'draft')
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
    @else
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
    @endif

    @if($hasSearch)
        <p class="mt-2 text-sm text-gray-600">Ничего не найдено по названию.</p>
        <p class="mt-2 text-sm">
            <a
                href="{{ route('news.index', array_filter(['news_status' => request('news_status', 'all') !== 'all' ? request('news_status') : null])) }}"
                class="font-medium text-blue-600 hover:text-blue-800"
            >Сбросить поиск</a>
        </p>
    @else
        <p class="mt-2 text-sm text-gray-500">
            {{ $variant === 'draft' ? 'Нет черновиков' : 'Нет опубликованных новостей' }}
        </p>
    @endif
</div>
