@extends('layouts.guest')

@section('title', 'Виды спорта')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Виды спорта</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Список видов спорта в клубе</p>
        </div>

        <!-- Поиск -->
        <form method="GET" action="{{ route('guest.sports') }}" class="max-w-xl" data-sports-search-form>
            <div class="flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    data-sports-search-input
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                    placeholder="Поиск по названию (например: футзал)"
                >
                <button
                    type="submit"
                    class="hidden shrink-0 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:inline-flex"
                >
                    Найти
                </button>
                @if(!empty($q))
                    <a
                        href="{{ route('guest.sports') }}"
                        class="shrink-0 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        Сброс
                    </a>
                @endif
            </div>
            <input type="hidden" name="per_page" id="sports_guest_per_page_hidden" value="{{ (int)($perPage ?? 25) }}">
        </form>

        <!-- Список видов спорта -->
        @if($sports->count() > 0)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4" data-sports-grid>
                @foreach($sports as $sport)
                    <div
                        class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg"
                        data-sport-card
                        data-sport-name="{{ mb_strtolower($sport->name) }}"
                    >
                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="mb-2 text-xl font-semibold text-gray-900">
                                <a href="{{ route('guest.sports.show', $sport) }}" class="hover:text-blue-600 transition">{{ $sport->name }}</a>
                            </h3>
                            <div class="mb-4 flex-1 space-y-2">
                                @if($sport->description)
                                    <p class="line-clamp-3 text-sm text-gray-600">
                                        {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($sport->description))), 100) }}
                                    </p>
                                @endif
                            </div>
                            <a
                                href="{{ route('guest.sports.show', $sport) }}"
                                class="mt-auto block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Подробнее
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 border-t border-gray-200 pt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2">
                    <label for="sports_guest_per_page_bottom_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>
                    <x-per-page-combobox :selected="(int)($perPage ?? 25)" input-id="sports_guest_per_page_bottom" />
                </div>
                <div>
                    @if($sports->hasPages())
                        {{ $sports->links() }}
                    @endif
                </div>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Видов спорта пока нет</p>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const PER_PAGE_STORAGE_KEY = 'sports_guest_per_page';
    const hidden = document.getElementById('sports_guest_per_page_hidden');
    const select = document.getElementById('sports_guest_per_page_bottom');
    if (!hidden || !select) return;

    try {
        const u = new URL(window.location.href);
        if (!u.searchParams.get('per_page')) {
            const stored = localStorage.getItem(PER_PAGE_STORAGE_KEY);
            if (stored && stored !== hidden.value) {
                hidden.value = stored;
                select.value = stored;
            }
        }
    } catch (e) {}

    select.addEventListener('change', function () {
        hidden.value = String(select.value || '25');
        try { localStorage.setItem(PER_PAGE_STORAGE_KEY, String(hidden.value || '25')); } catch (e2) {}
        const form = document.querySelector('[data-sports-search-form]');
        if (form) form.submit();
    });
})();
</script>
@endpush

