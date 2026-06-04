@extends('layouts.student')

@section('title', 'Новости')

@section('content')
    @php
        use App\Support\NewsListingSort;

        $listingFilters = $listingFilters ?? [];
        $cardsSortStack = $cardsSortStack ?? NewsListingSort::defaultStack();
        $hasFilters = filled($listingFilters['q'] ?? null)
            || filled($listingFilters['date_from'] ?? null)
            || filled($listingFilters['date_to'] ?? null);
        $newsBaseListingParams = array_filter([
            'q' => filled($listingFilters['q'] ?? null) ? $listingFilters['q'] : null,
            'date_from' => $listingFilters['date_from'] ?? null,
            'date_to' => $listingFilters['date_to'] ?? null,
            'per_page' => ($perPage ?? 25) !== 25 ? (string) ($perPage ?? 25) : null,
        ], fn ($v) => $v !== null && $v !== '');
        $resetListingUrl = NewsListingSort::listingUrl('news.index', [], $cardsSortStack, ['page' => 1]);
    @endphp
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Новости</h1>
        </div>

        @php $lf = $listingFilters; @endphp
        <form method="GET" action="{{ route('news.index') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <input type="hidden" name="page" value="1">
            <input type="hidden" name="per_page" id="news_student_filter_per_page" value="{{ $perPage ?? 25 }}">
            @include('news.partials.sort-hidden-inputs', compact('cardsSortStack'))
            <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                <div class="min-w-0 flex-1">
                    <label for="news_student_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск</label>
                    <input
                        type="search"
                        id="news_student_q"
                        name="q"
                        value="{{ $lf['q'] ?? '' }}"
                        maxlength="255"
                        placeholder="По названию или тексту…"
                        autocomplete="off"
                        class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                </div>
                <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                    <div class="min-w-0 sm:w-40">
                        <label for="news_student_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                        <input
                            type="date"
                            id="news_student_date_from"
                            name="date_from"
                            value="{{ $lf['date_from'] ?? '' }}"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                    </div>
                    <div class="min-w-0 sm:w-40">
                        <label for="news_student_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                        <input
                            type="date"
                            id="news_student_date_to"
                            name="date_to"
                            value="{{ $lf['date_to'] ?? '' }}"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                    </div>
                </div>
                <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                    <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">
                        Применить
                    </button>
                    <a href="{{ $resetListingUrl }}" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">
                        Сбросить
                    </a>
                </div>
            </div>
        </form>

        @include('news.partials.news-sort-bar', [
            'listingRoute' => 'news.index',
            'baseListingParams' => $newsBaseListingParams,
            'cardsSortStack' => $cardsSortStack,
        ])

        @if(session('error'))
            <div class="rounded-lg border-l-4 border-red-400 bg-red-50 p-4">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg border-l-4 border-green-400 bg-green-50 p-4">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        @if($publishedNews->total() > 0)
            @include('news.partials.news-grid', ['news' => $publishedNews, 'type' => 'student'])

            <div class="border-t border-gray-100 px-4 py-3">
                <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <label for="news_student_per_page_select_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                    <x-per-page-combobox :selected="(int) ($perPage ?? 25)" input-id="news_student_per_page_select" />
                </div>
                @if($publishedNews->hasPages())
                    {{ $publishedNews->links() }}
                @endif
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                @if($hasFilters)
                    <h3 class="mt-2 text-sm font-medium text-gray-900">По вашему запросу ничего не найдено</h3>
                    <p class="mt-1 text-sm text-gray-500">Измените поиск или нажмите «Сбросить».</p>
                @else
                    <p class="mt-2 text-sm text-gray-500">Нет опубликованных новостей</p>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const PER_PAGE_STORAGE_KEY = 'news_student_per_page';
    const perPageHidden = document.getElementById('news_student_filter_per_page');
    const perPageSelect = document.getElementById('news_student_per_page_select');

    if (!perPageHidden || !perPageSelect) {
        return;
    }

    try {
        const url = new URL(window.location.href);
        if (!url.searchParams.get('per_page')) {
            const stored = parseInt(localStorage.getItem(PER_PAGE_STORAGE_KEY) || '', 10);
            if ([10, 25, 50, 100].includes(stored) && stored !== parseInt(perPageHidden.value || '25', 10)) {
                perPageHidden.value = String(stored);
                perPageSelect.value = String(stored);
            }
        }
    } catch (e) {}

    perPageSelect.addEventListener('change', function () {
        const v = parseInt(String(perPageSelect.value || '25'), 10);
        const val = [10, 25, 50, 100].includes(v) ? v : 25;
        perPageHidden.value = String(val);
        try {
            localStorage.setItem(PER_PAGE_STORAGE_KEY, String(val));
        } catch (e) {}

        const form = perPageHidden.closest('form');
        if (form) {
            form.submit();
        }
    });
})();
</script>
@endpush
