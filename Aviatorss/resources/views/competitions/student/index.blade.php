@extends('layouts.student')

@section('title', 'Соревнования')

@section('content')
    @php
        $filter = $filter ?? 'upcoming';
        $listingFilters = $listingFilters ?? [];
        $queryForStatus = array_filter([
            'sport_id' => $listingFilters['sport_id'] ?? null,
            'date_from' => $listingFilters['date_from'] ?? null,
            'date_to' => $listingFilters['date_to'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        $statusRoute = fn (string $f) => route('competitions.index', array_merge($queryForStatus, ['filter' => $f]));
        $resetListingUrl = route('competitions.index', ['filter' => $filter]);
    @endphp
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Соревнования</h1>
        </div>

        <x-student-listing-filters-bar
            :action="route('competitions.index')"
            :reset-url="$resetListingUrl"
            :listing-filters="$listingFilters"
            :sports-for-filter="$sportsForFilter"
            id-prefix="comp"
            listing-search-root-id="comp-listing-root"
        >
            <input type="hidden" name="filter" value="{{ $filter }}">
        </x-student-listing-filters-bar>

        <!-- Фильтры по статусам -->
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="mb-3 text-xs font-medium text-gray-500">Статус</p>
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ $statusRoute('all') }}"
                    class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'all' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Все
                </a>
                <a
                    href="{{ $statusRoute('upcoming') }}"
                    class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'upcoming' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Предстоящие
                </a>
                <a
                    href="{{ $statusRoute('ongoing') }}"
                    class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'ongoing' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Идут сейчас
                </a>
                <a
                    href="{{ $statusRoute('finished') }}"
                    class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $filter === 'finished' ? 'border-blue-600 bg-blue-600 text-white shadow-sm hover:bg-blue-700' : 'border-gray-200 bg-gray-50 text-gray-800 hover:bg-gray-100' }}"
                >
                    Завершенные
                </a>
            </div>
        </div>

        @if($competitions->total() > 0)
            <div id="comp-listing-root">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 lg:grid-cols-4">
                @foreach($competitions as $competition)
                    <div
                        class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg"
                        data-listing-search-haystack="{{ mb_strtolower((string) ($competition->name ?? ''), 'UTF-8') }}"
                    >
                        @if($competition->images->isNotEmpty())
                            @include('news.partials.news-cover', ['item' => $competition, 'stacked' => true])
                        @endif
                        <div class="flex min-w-0 flex-1 flex-col p-6">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <h3 class="flex-1 text-lg font-bold leading-tight text-gray-900">
                                    <a href="{{ route('competitions.show', $competition) }}" class="transition hover:text-blue-600">
                                        {{ $competition->name }}
                                    </a>
                                </h3>
                                @if($competition->status === 'upcoming')
                                    <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        Предстоящее
                                    </span>
                                @elseif($competition->status === 'ongoing')
                                    <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                        Идет
                                    </span>
                                @elseif($competition->status === 'finished')
                                    <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Завершено
                                    </span>
                                @endif
                            </div>

                            <div class="mb-4 space-y-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $competition->sport?->name ?? 'Не указан' }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>
                                        @if($competition->start_date && $competition->end_date)
                                            @if($competition->start_date->format('Y-m-d') === $competition->end_date->format('Y-m-d'))
                                                {{ $competition->start_date->format('d.m.Y') }}
                                            @else
                                                {{ $competition->start_date->format('d.m.Y') }} — {{ $competition->end_date->format('d.m.Y') }}
                                            @endif
                                        @elseif($competition->start_date)
                                            {{ $competition->start_date->format('d.m.Y') }}
                                        @elseif($competition->end_date)
                                            {{ $competition->end_date->format('d.m.Y') }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-start">
                                    <svg class="mr-2 mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $competition->location?->location ?? 'Не указана' }}</span>
                                </div>
                            </div>

                            @if(filled($competition->description))
                                <p class="mb-4 line-clamp-3 flex-1 text-sm text-gray-600">
                                    {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($competition->description ?? '')))), 150) }}
                                </p>
                            @endif

                            <a
                                href="{{ route('competitions.show', $competition) }}"
                                class="mt-auto block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Подробнее
                            </a>
                        </div>
                    </div>
                @endforeach
                </div>
                @if($competitions->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3">
                        {{ $competitions->links('pagination::tailwind') }}
                    </div>
                @endif
                <p id="comp-listing-root-js-empty" class="hidden py-10 text-center text-sm text-gray-600">
                    По названию ничего не найдено.
                </p>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                @if($filter === 'upcoming')
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Предстоящих соревнований пока нет.</h3>
                    <p class="mt-1 text-sm text-gray-500">Они появятся после публикации.</p>
                @elseif($filter === 'all')
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет доступных соревнований</h3>
                    <p class="mt-1 text-sm text-gray-500">Смените фильтры по дате или виду спорта либо нажмите «Сбросить».</p>
                @else
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет соревнований по условиям</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Измените статус выше или сбросьте фильтры по дате и виду спорта (кнопка «Сбросить»).
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection
