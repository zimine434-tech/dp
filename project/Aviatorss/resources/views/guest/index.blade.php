@extends('layouts.guest')

@section('title', 'Главная')

@push('styles')
<style>
    [data-home-thumbs]::-webkit-scrollbar {
        width: 0 !important;
        height: 0 !important;
        display: none !important;
    }
    [data-home-track] {
        transform-style: flat;
    }
    [data-home-slide] {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        will-change: opacity, transform;
    }
    [data-home-photo-frame] {
        will-change: transform;
    }
</style>
@endpush

@section('content')
    @php
        $latestCompetitionPhotos = $latestCompetitionPhotos ?? collect();
        $homePhotos = $latestCompetitionPhotos->values();
        $homePhotoUrls = $latestCompetitionPhotos->map(fn ($photo) => $photo->url)->values()->all();
        $homePhotosCount = $latestCompetitionPhotos->count();
    @endphp
    @if($homePhotosCount > 0)
            <section class="relative w-full overflow-hidden">
                <div class="relative overflow-hidden bg-gray-900" data-home-carousel data-home-index="0" style="touch-action: pan-y;">
                    <div class="absolute inset-0" data-home-backgrounds>
                        @foreach($homePhotos as $photo)
                            <img
                                src="{{ $photo->url }}"
                                alt=""
                                data-home-bg
                                data-home-bg-index="{{ $loop->index }}"
                                class="absolute inset-0 h-full w-full scale-110 object-cover blur-2xl transition-opacity duration-1000 ease-in-out {{ $loop->first ? 'opacity-55' : 'opacity-0' }}"
                                aria-hidden="true"
                            >
                        @endforeach
                        <div class="absolute inset-0 bg-black/25"></div>
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 top-0 z-20 h-14 bg-gradient-to-b from-black/35 via-black/15 to-transparent backdrop-blur-[2px]"></div>
                    <div class="relative w-full overflow-hidden" data-home-track-wrap>
                        <div
                            class="relative h-[min(42dvh,22rem)] sm:h-[min(54vh,34rem)]"
                            data-home-track
                        >
                            @foreach($homePhotos as $photo)
                                <div
                                    class="pointer-events-none absolute inset-0 transform-gpu opacity-0 transition-[opacity,transform] duration-[1.1s] ease-in-out"
                                    data-home-slide
                                    data-home-slide-index="{{ $loop->index }}"
                                >
                                    <button
                                        type="button"
                                        class="relative block h-full w-full cursor-zoom-in"
                                        data-news-lightbox
                                        data-lightbox-src="{{ $photo->url }}"
                                        data-lightbox-gallery='@json($homePhotoUrls)'
                                        data-lightbox-index="{{ $loop->index }}"
                                        data-lightbox-alt="Фотография клуба"
                                        aria-label="Открыть фото в полном размере"
                                    >
                                        <div class="relative z-10 flex h-full w-full items-center justify-center px-2 py-1 [perspective:1400px] sm:px-3">
                                            <div
                                                data-home-photo-frame
                                                class="h-full w-[38%] min-w-0 overflow-hidden rounded-md ring-1 ring-white/10 shadow-none transition-transform duration-[1.1s] ease-in-out sm:w-[34%] lg:w-[30%]"
                                            >
                                                <img
                                                    src="{{ $photo->url }}"
                                                    alt="Фотография клуба"
                                                    class="h-full w-full object-contain object-center"
                                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                                >
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($homePhotosCount > 1)
                        <button type="button" data-home-prev class="absolute left-2 top-1/2 z-[60] inline-flex min-h-11 min-w-11 -translate-y-1/2 touch-manipulation items-center justify-center rounded-full bg-black/55 p-1.5 text-white shadow-lg ring-1 ring-white/30 sm:left-3 sm:min-h-0 sm:min-w-0" aria-label="Предыдущее фото">
                            <svg class="h-6 w-6 drop-shadow-[0_1px_3px_rgba(0,0,0,0.9)] sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" data-home-next class="absolute right-2 top-1/2 z-[60] inline-flex min-h-11 min-w-11 -translate-y-1/2 touch-manipulation items-center justify-center rounded-full bg-black/55 p-1.5 text-white shadow-lg ring-1 ring-white/30 sm:right-3 sm:min-h-0 sm:min-w-0" aria-label="Следующее фото">
                            <svg class="h-6 w-6 drop-shadow-[0_1px_3px_rgba(0,0,0,0.9)] sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <span data-home-counter class="pointer-events-none absolute right-2 top-2 z-40 rounded-full bg-black/60 px-2 py-0.5 text-xs text-white">
                            1 / {{ $homePhotosCount }}
                        </span>
                        <div class="relative z-10 -mt-3 px-2 pb-3 pt-3 max-md:hidden md:block">
                                <div class="flex gap-2.5 overflow-x-auto overflow-y-hidden [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:h-0 [&::-webkit-scrollbar]:w-0" data-home-thumbs style="scrollbar-width:none; -ms-overflow-style:none;">
                                @foreach($latestCompetitionPhotos as $photo)
                                    <div class="flex w-28 shrink-0 flex-col items-stretch gap-0" data-home-thumb-block>
                                        <button
                                            type="button"
                                            data-home-thumb
                                            data-home-thumb-index="{{ $loop->index }}"
                                            class="h-16 w-full overflow-hidden rounded border-2 {{ $loop->first ? 'border-white' : 'border-transparent opacity-70' }}"
                                            aria-label="Фото {{ $loop->iteration }}"
                                        >
                                            <img src="{{ $photo->url }}" alt="Миниатюра фото {{ $loop->iteration }}" class="h-full w-full object-cover" loading="lazy">
                                        </button>
                                        <div class="h-0 overflow-hidden transition-[height] duration-150 ease-out" data-home-thumb-progress-strip>
                                            <div class="h-0.5 w-full overflow-hidden rounded-full bg-white/25">
                                                <div data-home-thumb-progress class="h-full w-0 rounded-full bg-sky-400"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                        </div>
                    @endif
                </div>
            </section>
    @endif

    <div class="mx-auto max-w-[min(120rem,calc(100vw-2rem))] space-y-16 px-4 pb-12 pt-10 sm:px-6 lg:px-8">
        <!-- Новости -->
        <section>
            <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <h2 class="text-2xl font-bold text-gray-900">Последние новости</h2>
                <a href="{{ route('guest.news') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                    Все новости →
                </a>
            </div>

            @if($publishedNews->count() > 0)
                <div class="relative min-w-0" data-paginated-section data-item-label="Новости">
                    <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-[repeat(4,minmax(0,1fr))]">
                            @foreach($publishedNews as $news)
                                <article data-paginated-card class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                                    @include('news.partials.news-cover', ['item' => $news, 'stacked' => true])
                                    <div class="flex min-h-0 flex-1 flex-col p-5">
                                        <h3 class="mb-2 text-lg font-semibold leading-snug text-gray-900">
                                            <a href="{{ route('guest.news.show', ['news' => $news]) }}" class="hover:text-blue-600 transition">
                                                {{ $news->name }}
                                            </a>
                                        </h3>
                                        <p class="mb-0 min-h-0 flex-1 text-left text-sm leading-snug text-gray-600 break-words">@if(filled($news->description))@include('news.partials.news-description-excerpt', ['description' => $news->description, 'url' => route('guest.news.show', ['news' => $news])])@else<span class="text-gray-400 italic">Описание отсутствует</span>@endif</p>
                                        <div class="mt-4 text-sm text-gray-500">
                                            <span>{{ $news->date->format('d.m.Y') }}</span>
                                        </div>
                                        <a
                                            href="{{ route('guest.news.show', ['news' => $news]) }}"
                                            class="mt-4 block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                                        >
                                            Подробнее
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                    </div>
                    <nav
                        class="mt-5 flex flex-wrap items-center justify-end gap-2 sm:gap-3"
                        data-paginated-nav
                        aria-label="Навигация по новостям"
                        hidden
                    >
                        <button
                            type="button"
                            data-paginated-arrow="prev"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Предыдущая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex flex-wrap items-center justify-end gap-2" data-paginated-pages></div>
                        <button
                            type="button"
                            data-paginated-arrow="next"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Следующая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            @else
                @include('guest.partials.home-empty-trio', ['message' => 'Новостей пока нет', 'slots' => 1])
            @endif
        </section>

        <!-- Предстоящие соревнования -->
        <section>
            <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <h2 class="text-2xl font-bold text-gray-900">Предстоящие соревнования</h2>
                <a href="{{ route('guest.competitions') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                    Все соревнования →
                </a>
            </div>

            @if($upcomingCompetitions->count() > 0)
                <div class="relative min-w-0" data-paginated-section data-item-label="Соревнования">
                    <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-[repeat(4,minmax(0,1fr))]">
                    @foreach($upcomingCompetitions as $competition)
                        <div data-paginated-card class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                            @if($competition->images->isNotEmpty())
                                @include('news.partials.news-cover', ['item' => $competition, 'stacked' => true])
                            @endif
                            <div class="flex min-h-0 flex-1 flex-col p-6">
                                <div class="mb-3 flex shrink-0 items-start justify-between">
                                    <h3 class="min-w-0 flex-1 text-lg font-bold leading-tight text-gray-900">
                                        <a href="{{ route('guest.competitions.show', ['competition' => $competition]) }}" class="hover:text-blue-600 transition">
                                            {{ $competition->name }}
                                        </a>
                                    </h3>
                                    @if($competition->status === 'upcoming')
                                        <span class="ml-2 inline-flex flex-shrink-0 items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            Предстоящее
                                        </span>
                                    @elseif($competition->status === 'ongoing')
                                        <span class="ml-2 inline-flex flex-shrink-0 items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            Идет
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mb-0 min-h-0 flex-1 space-y-2 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>{{ $competition->sport?->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>
                                            @if($competition->start_date->format('Y-m-d') === $competition->end_date->format('Y-m-d'))
                                                {{ $competition->start_date->format('d.m.Y') }}
                                            @else
                                                {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                            @endif
                                        </span>
                                    </div>
                                    @if($competition->location)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>{{ $competition->location->location }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <a
                                    href="{{ route('guest.competitions.show', ['competition' => $competition]) }}"
                                    class="mt-4 block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                                >
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    @endforeach
                    </div>
                    <nav
                        class="mt-5 flex flex-wrap items-center justify-end gap-2 sm:gap-3"
                        data-paginated-nav
                        aria-label="Навигация по соревнованиям"
                        hidden
                    >
                        <button
                            type="button"
                            data-paginated-arrow="prev"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Предыдущая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex flex-wrap items-center justify-end gap-2" data-paginated-pages></div>
                        <button
                            type="button"
                            data-paginated-arrow="next"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Следующая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            @else
                @include('guest.partials.home-empty-trio', ['message' => 'Предстоящих соревнований пока нет', 'slots' => 1])
            @endif
        </section>

        <!-- Результаты соревнований -->
        <section>
            <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <h2 class="text-2xl font-bold text-gray-900">Результаты соревнований</h2>
                <a href="{{ route('guest.results') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                    Все результаты →
                </a>
            </div>

            @if($latestResults->count() > 0)
                <div class="relative min-w-0" data-paginated-section data-item-label="Результаты">
                    <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-[repeat(4,minmax(0,1fr))]">
                    @foreach($latestResults as $result)
                        <article data-paginated-card class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                            @include('news.partials.news-cover', ['item' => $result->competition, 'stacked' => true])
                            <div class="flex min-h-0 flex-1 flex-col p-5">
                                <div class="mb-2 flex shrink-0 items-start justify-between gap-3">
                                    <h3 class="min-w-0 flex-1 text-lg font-semibold leading-snug text-gray-900">
                                        <a href="{{ route('guest.results.show', ['competition' => $result->competition]) }}" class="hover:text-blue-600 transition">
                                            {{ $result->competition->name }}
                                        </a>
                                    </h3>
                                    <div class="flex shrink-0 flex-col items-end gap-1 text-right">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                @if(is_numeric($result->place) && (int) $result->place === 1) bg-yellow-100 text-yellow-800
                                                @elseif(is_numeric($result->place) && (int) $result->place === 2) bg-gray-200 text-gray-800
                                                @elseif(is_numeric($result->place) && (int) $result->place === 3) bg-orange-100 text-orange-800
                                                @else bg-blue-100 text-blue-800
                                                @endif"
                                        >
                                            Место: {{ $result->place }}
                                        </span>
                                    </div>
                                </div>
                                <p class="mb-0 min-h-0 flex-1 text-sm leading-snug text-gray-600 line-clamp-3">
                                    @if(filled($result->competition->description))
                                        {{ \App\Support\RichTextPlain::fromHtml($result->competition->description, 150) }}
                                    @else
                                        {{ $result->competition->sport?->name ?? 'Соревнование' }}
                                        @if($result->competition->participants->count() > 0)
                                            · участников: {{ $result->competition->participants->count() }}
                                        @endif
                                    @endif
                                </p>
                                <div class="mt-4 text-sm text-gray-500">
                                    @php
                                        $cDates = $result->competition;
                                    @endphp
                                    @if($cDates->start_date && $cDates->end_date)
                                        @if($cDates->start_date->format('Y-m-d') === $cDates->end_date->format('Y-m-d'))
                                            <span>Дата: {{ $cDates->start_date->format('d.m.Y') }}</span>
                                        @else
                                            <span>Дата: {{ $cDates->start_date->format('d.m.Y') }} — {{ $cDates->end_date->format('d.m.Y') }}</span>
                                        @endif
                                    @elseif($cDates->start_date)
                                        <span>Дата: {{ $cDates->start_date->format('d.m.Y') }}</span>
                                    @elseif($cDates->end_date)
                                        <span>Дата: {{ $cDates->end_date->format('d.m.Y') }}</span>
                                    @else
                                        <span class="text-gray-400">Дата проведения не указана</span>
                                    @endif
                                </div>
                                <a
                                    href="{{ route('guest.results.show', ['competition' => $result->competition]) }}"
                                    class="mt-4 block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                                >
                                    Подробнее
                                </a>
                            </div>
                        </article>
                    @endforeach
                    </div>
                    <nav
                        class="mt-5 flex flex-wrap items-center justify-end gap-2 sm:gap-3"
                        data-paginated-nav
                        aria-label="Навигация по результатам"
                        hidden
                    >
                        <button
                            type="button"
                            data-paginated-arrow="prev"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Предыдущая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex flex-wrap items-center justify-end gap-2" data-paginated-pages></div>
                        <button
                            type="button"
                            data-paginated-arrow="next"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Следующая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            @else
                @include('guest.partials.home-empty-trio', ['message' => 'Результатов пока нет', 'slots' => 1])
            @endif
        </section>

        <!-- Виды спорта -->
        <section>
            <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <h2 class="text-2xl font-bold text-gray-900">Виды спорта</h2>
                <a href="{{ route('guest.sports') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                    Все виды спорта →
                </a>
            </div>

            @if($sports->count() > 0)
                <div class="relative min-w-0" data-paginated-section data-item-label="Виды спорта">
                    <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-[repeat(4,minmax(0,1fr))]">
                    @foreach($sports as $sport)
                        <div data-paginated-card class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                            <div class="flex min-h-0 flex-1 flex-col p-6">
                                <h3 class="mb-2 shrink-0 text-xl font-semibold text-gray-900">{{ $sport->name }}</h3>
                                <div class="mb-0 min-h-0 flex-1 space-y-2">
                                    @if($sport->description)
                                        <p class="line-clamp-3 text-sm text-gray-600">
                                            {{ \App\Support\RichTextPlain::fromHtml($sport->description, 100) }}
                                        </p>
                                    @endif
                                </div>
                                <a
                                    href="{{ route('guest.sports.show', $sport) }}"
                                    class="mt-4 block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                                >
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    @endforeach
                    </div>
                    <nav
                        class="mt-5 flex flex-wrap items-center justify-end gap-2 sm:gap-3"
                        data-paginated-nav
                        aria-label="Навигация по видам спорта"
                        hidden
                    >
                        <button
                            type="button"
                            data-paginated-arrow="prev"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Предыдущая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex flex-wrap items-center justify-end gap-2" data-paginated-pages></div>
                        <button
                            type="button"
                            data-paginated-arrow="next"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Следующая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            @else
                @include('guest.partials.home-empty-trio', ['message' => 'Видов спорта пока нет'])
            @endif
        </section>

        <!-- Команды -->
        <section>
            <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <h2 class="text-2xl font-bold text-gray-900">Команды</h2>
                <a href="{{ route('guest.teams') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                    Все команды →
                </a>
            </div>

            @if($teams->count() > 0)
                <div class="relative min-w-0" data-paginated-section data-item-label="Команды">
                    <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-[repeat(4,minmax(0,1fr))]">
                    @foreach($teams as $team)
                        <div data-paginated-card class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                            <div class="flex min-h-0 flex-1 flex-col p-6">
                                <h3 class="mb-2 shrink-0 text-xl font-semibold text-gray-900">
                                    <a href="{{ route('guest.teams.show', ['team' => $team]) }}" class="hover:text-blue-600 transition">
                                        {{ $team->name }}
                                    </a>
                                </h3>
                                <div class="mb-0 min-h-0 flex-1 space-y-3">
                                    @if($team->sport)
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Вид спорта:</span>
                                            <a href="{{ $team->sport ? route('guest.sports.show', $team->sport) : '#' }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $team->sport?->name ?? '—' }}
                                            </a>
                                        </p>
                                    @endif
                                    @if($team->description)
                                        <p class="line-clamp-3 text-sm text-gray-600">
                                            {{ \App\Support\RichTextPlain::fromHtml($team->description, 100) }}
                                        </p>
                                    @endif
                                    <div class="text-sm text-gray-500">
                                        <span class="font-medium">Участников:</span> {{ $team->members->whereNull('out')->count() }}
                                    </div>
                                </div>
                                <a
                                    href="{{ route('guest.teams.show', ['team' => $team]) }}"
                                    class="mt-4 block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                                >
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    @endforeach
                    </div>
                    <nav
                        class="mt-5 flex flex-wrap items-center justify-end gap-2 sm:gap-3"
                        data-paginated-nav
                        aria-label="Навигация по командам"
                        hidden
                    >
                        <button
                            type="button"
                            data-paginated-arrow="prev"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Предыдущая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex flex-wrap items-center justify-end gap-2" data-paginated-pages></div>
                        <button
                            type="button"
                            data-paginated-arrow="next"
                            class="inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 disabled:pointer-events-none disabled:opacity-35"
                            aria-label="Следующая страница"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            @else
                @include('guest.partials.home-empty-trio', ['message' => 'Команд пока нет'])
            @endif
        </section>
    </div>

    @include('news.partials.news-lightbox')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.querySelector('[data-home-carousel]');
    if (!root) return;
    var homeHero = root;
    var trackWrap = root.querySelector('[data-home-track-wrap]');
    var track = root.querySelector('[data-home-track]');
    var slides = root.querySelectorAll('[data-home-slide]');
    var prevBtn = root.querySelector('[data-home-prev]');
    var nextBtn = root.querySelector('[data-home-next]');
    var counter = root.querySelector('[data-home-counter]');
    var thumbs = root.querySelectorAll('[data-home-thumb]');
    var backgrounds = root.querySelectorAll('[data-home-bg]');
    var total = slides.length;
    var index = 0;
    var autoSlideDuration = window.matchMedia('(max-width: 639px)').matches ? 20000 : 15000;
    var autoSlideTimer = null;
    var progressRaf = null;
    var progressStartedAt = null;
    var progressRemaining = autoSlideDuration;
    var isSliding = false;
    var autoSlideToken = 0;
    var slidingGuardTimer = null;
    var sideOffsetPx = 0;
    var lastWrapWidth = 0;
    var touchStartX = 0;
    var touchStartY = 0;
    var touchDeltaX = 0;
    var touchDeltaY = 0;
    var touchTracking = false;
    var touchSwiped = false;
    if (!track || !trackWrap || total < 1) return;
    var cardWidthFallback = function (wrapWidth) {
        var pad = window.matchMedia('(min-width: 640px)').matches ? 24 : 16;
        var inner = Math.max(0, wrapWidth - pad);
        var frac = 0.38;
        if (window.matchMedia('(min-width: 1024px)').matches) frac = 0.30;
        else if (window.matchMedia('(min-width: 640px)').matches) frac = 0.34;
        return Math.max(1, Math.round(inner * frac));
    };

    var applySlideClasses = function () {
        var rectW = trackWrap.getBoundingClientRect().width;
        var wrapWidth = Math.round(rectW || trackWrap.offsetWidth || track.offsetWidth || 0);
        if (!wrapWidth && lastWrapWidth) wrapWidth = lastWrapWidth;
        if (!wrapWidth) wrapWidth = 320;
        lastWrapWidth = wrapWidth;
        var activeFrame = slides[index] ? slides[index].querySelector('[data-home-photo-frame]') : null;
        var cardWidth = 0;
        if (activeFrame) {
            var w = activeFrame.offsetWidth;
            if (!w) {
                var r = activeFrame.getBoundingClientRect();
                w = Math.round(r.width);
            }
            cardWidth = w;
        }
        if (!cardWidth || cardWidth < 40 || cardWidth > wrapWidth * 0.55) {
            cardWidth = cardWidthFallback(wrapWidth);
        }
        var edgeGapPx = 18;
        var rawSide = Math.round((wrapWidth - cardWidth) / 2 - edgeGapPx);
        var cap = Math.max(24, Math.floor(wrapWidth * 0.42));
        sideOffsetPx = Math.max(12, Math.min(rawSide, cap));
        var prevIndex = (index - 1 + total) % total;
        var nextIndex = (index + 1) % total;
        slides.forEach(function (slide, slideIndex) {
            var innerCard = slide.querySelector('[data-home-photo-frame]');
            slide.classList.remove('z-40', 'z-30', 'z-20', 'z-10', 'z-0');
            slide.classList.add('z-0', 'pointer-events-none');
            slide.style.opacity = '0';
            slide.style.visibility = 'visible';
            slide.style.transform = 'translate3d(0,0,0)';
            if (innerCard) {
                innerCard.style.transformOrigin = 'center center';
                innerCard.style.transform = 'translate3d(0,0,0) rotateY(0deg) scale(0.9)';
            }

            if (slideIndex === index) {
                slide.classList.remove('z-0', 'pointer-events-none');
                slide.classList.add('z-40');
                slide.style.opacity = '1';
                slide.style.transform = 'translate3d(0,0,0) scale(1)';
                if (innerCard) {
                    innerCard.style.transformOrigin = 'center center';
                    innerCard.style.transform = 'translate3d(0,0,0) rotateY(0deg) scale(1)';
                }
            } else if (slideIndex === prevIndex) {
                slide.classList.remove('z-0');
                slide.classList.add('z-30');
                slide.style.opacity = '0.42';
                slide.style.transform = 'translate3d(0,0,0)';
                if (innerCard) {
                    innerCard.style.transformOrigin = 'right center';
                    innerCard.style.transform = 'translate3d(' + (-sideOffsetPx) + 'px,0,0) rotateY(7deg) scale(0.95)';
                }
            } else if (slideIndex === nextIndex) {
                slide.classList.remove('z-0');
                slide.classList.add('z-30');
                slide.style.opacity = '0.42';
                slide.style.transform = 'translate3d(0,0,0)';
                if (innerCard) {
                    innerCard.style.transformOrigin = 'left center';
                    innerCard.style.transform = 'translate3d(' + sideOffsetPx + 'px,0,0) rotateY(-7deg) scale(0.95)';
                }
            }
        });
    };

    root.querySelectorAll('[data-home-slide] img').forEach(function (img) {
        var onImgLayout = function () {
            applySlideClasses();
        };
        img.addEventListener('load', onImgLayout);
        if (img.complete && img.naturalWidth) {
            requestAnimationFrame(onImgLayout);
        }
    });

    var getActiveProgressFill = function () {
        var thumb = thumbs[index];
        if (!thumb) return null;
        var block = thumb.closest('[data-home-thumb-block]');
        return block && block.querySelector('[data-home-thumb-progress]');
    };

    var scrollThumbWithinStrip = function (thumb) {
        if (!thumb) return;
        var strip = thumb.closest('[data-home-thumbs]');
        if (!strip) return;
        var thumbLeft = thumb.offsetLeft;
        var thumbRight = thumbLeft + thumb.offsetWidth;
        var visibleLeft = strip.scrollLeft;
        var visibleRight = visibleLeft + strip.clientWidth;
        if (thumbLeft < visibleLeft) {
            strip.scrollTo({ left: Math.max(0, thumbLeft - 8), behavior: 'smooth' });
            return;
        }
        if (thumbRight > visibleRight) {
            var nextLeft = thumbRight - strip.clientWidth + 8;
            strip.scrollTo({ left: Math.max(0, nextLeft), behavior: 'smooth' });
        }
    };

    var stopProgress = function () {
        if (progressRaf) {
            cancelAnimationFrame(progressRaf);
            progressRaf = null;
        }
    };

    var runProgress = function () {
        if (progressStartedAt === null) return;
        var fill = getActiveProgressFill();
        if (!fill) return;
        var elapsedNow = Date.now() - progressStartedAt;
        progressRemaining = Math.max(0, autoSlideDuration - elapsedNow);
        var percent = Math.max(0, Math.min(100, ((autoSlideDuration - progressRemaining) / autoSlideDuration) * 100));
        fill.style.width = percent + '%';
        if (progressRemaining > 0) {
            progressRaf = requestAnimationFrame(runProgress);
        } else {
            progressRaf = null;
        }
    };

    var stopAutoSlide = function () {
        autoSlideToken += 1;
        if (autoSlideTimer) {
            clearTimeout(autoSlideTimer);
            autoSlideTimer = null;
        }
        if (progressStartedAt !== null) {
            var elapsed = Date.now() - progressStartedAt;
            progressRemaining = Math.max(0, autoSlideDuration - elapsed);
        }
        stopProgress();
    };

    var startAutoSlide = function () {
        if (total <= 1) return;
        autoSlideToken += 1;
        var token = autoSlideToken;
        if (autoSlideTimer) clearTimeout(autoSlideTimer);
        stopProgress();
        progressStartedAt = Date.now() - (autoSlideDuration - progressRemaining);
        runProgress();
        autoSlideTimer = setTimeout(function () {
            if (token !== autoSlideToken) return;
            if (isSliding) {
                progressRemaining = 900;
                startAutoSlide();
                return;
            }
            var moved = setIndex(index + 1);
            if (!moved) {
                progressRemaining = 900;
                startAutoSlide();
                return;
            }
            progressRemaining = autoSlideDuration;
            startAutoSlide();
        }, progressRemaining);
    };

    var setIndex = function (next) {
        if (isSliding) return false;
        var nextIndex = ((next % total) + total) % total;
        var changed = nextIndex !== index;
        index = nextIndex;
        root.setAttribute('data-home-index', String(index));
        if (changed) {
            isSliding = true;
            if (slidingGuardTimer) clearTimeout(slidingGuardTimer);
            slidingGuardTimer = setTimeout(function () {
                isSliding = false;
                slidingGuardTimer = null;
            }, 1120);
        }
        backgrounds.forEach(function (bg, bgIndex) {
            bg.classList.toggle('opacity-55', bgIndex === index);
            bg.classList.toggle('opacity-0', bgIndex !== index);
        });
        applySlideClasses();
        if (counter) counter.textContent = (index + 1) + ' / ' + total;
        thumbs.forEach(function (t, i) {
            var block = t.closest('[data-home-thumb-block]');
            var strip = block && block.querySelector('[data-home-thumb-progress-strip]');
            var fill = block && block.querySelector('[data-home-thumb-progress]');
            t.classList.toggle('border-white', i === index);
            t.classList.toggle('border-transparent', i !== index);
            t.classList.toggle('opacity-70', i !== index);
            if (i === index) {
                scrollThumbWithinStrip(t);
                if (strip) { strip.classList.remove('h-0'); strip.classList.add('h-0.5', 'mt-0.5'); }
                if (fill) fill.style.width = '0%';
            } else {
                if (strip) { strip.classList.add('h-0'); strip.classList.remove('h-0.5', 'mt-0.5'); }
                if (fill) fill.style.width = '0%';
            }
        });
        return changed;
    };

    if (prevBtn) prevBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (isSliding) return;
        stopAutoSlide();
        var moved = setIndex(index - 1);
        if (!moved) return;
        progressRemaining = autoSlideDuration;
        startAutoSlide();
    });
    if (nextBtn) nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (isSliding) return;
        stopAutoSlide();
        var moved = setIndex(index + 1);
        if (!moved) return;
        progressRemaining = autoSlideDuration;
        startAutoSlide();
    });
    thumbs.forEach(function (t) {
        t.addEventListener('click', function () {
            if (isSliding) return;
            var i = parseInt(t.getAttribute('data-home-thumb-index') || '0', 10);
            if (!isNaN(i)) {
                stopAutoSlide();
                var moved = setIndex(i);
                if (!moved) return;
                progressRemaining = autoSlideDuration;
                startAutoSlide();
            }
        });
    });

    trackWrap.addEventListener('touchstart', function (event) {
        if (!event.touches || event.touches.length !== 1) return;
        if (root.getAttribute('data-guest-photo-pinch-brake') === '1') return;
        if (event.target && event.target.closest && event.target.closest('img[data-photo-pinch-active="1"]')) return;
        var touch = event.touches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
        touchDeltaX = 0;
        touchDeltaY = 0;
        touchTracking = true;
        touchSwiped = false;
        stopAutoSlide();
    }, { passive: true });

    trackWrap.addEventListener('touchmove', function (event) {
        if (!touchTracking || !event.touches || event.touches.length !== 1) return;
        if (root.getAttribute('data-guest-photo-pinch-brake') === '1') {
            touchTracking = false;
            touchDeltaX = 0;
            touchDeltaY = 0;
            return;
        }
        var touch = event.touches[0];
        touchDeltaX = touch.clientX - touchStartX;
        touchDeltaY = touch.clientY - touchStartY;
    }, { passive: true });

    var finishTouchSwipe = function () {
        if (!touchTracking) return;
        touchTracking = false;
        var absX = Math.abs(touchDeltaX);
        var absY = Math.abs(touchDeltaY);
        if (touchSwiped || absX < 36 || absX <= absY || isSliding) {
            if (progressRemaining <= 0 || progressRemaining > autoSlideDuration) {
                progressRemaining = autoSlideDuration;
            }
            startAutoSlide();
            return;
        }
        touchSwiped = true;
        var moved = false;
        if (touchDeltaX < 0) {
            moved = setIndex(index + 1);
        } else {
            moved = setIndex(index - 1);
        }
        if (moved) {
            progressRemaining = autoSlideDuration;
        }
        startAutoSlide();
    };

    trackWrap.addEventListener('touchend', finishTouchSwipe, { passive: true });
    trackWrap.addEventListener('touchcancel', function () {
        touchTracking = false;
        if (progressRemaining <= 0 || progressRemaining > autoSlideDuration) {
            progressRemaining = autoSlideDuration;
        }
        startAutoSlide();
    }, { passive: true });

    homeHero.addEventListener('mouseenter', stopAutoSlide);
    homeHero.addEventListener('mouseleave', function () {
        if (progressRemaining <= 0 || progressRemaining > autoSlideDuration) {
            progressRemaining = autoSlideDuration;
        }
        startAutoSlide();
    });

    applySlideClasses();
    setIndex(0);
    progressRemaining = autoSlideDuration;
    startAutoSlide();

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            applySlideClasses();
        }, 120);
    });
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sections = document.querySelectorAll('[data-paginated-section]');
    if (!sections.length) return;

    var resizeFns = [];

    sections.forEach(function (root) {
        var itemLabel = root.getAttribute('data-item-label') || 'Записи';
        var pagination = root.querySelector('[data-paginated-nav]');
        var pagesWrap = root.querySelector('[data-paginated-pages]');
        var prevArrow = root.querySelector('[data-paginated-arrow="prev"]');
        var nextArrow = root.querySelector('[data-paginated-arrow="next"]');
        var cards = root.querySelectorAll('[data-paginated-card]');
        if (!pagination || !pagesWrap || !cards.length) return;

        var pageButtons = [];
        var totalPages = 0;
        var activePage = 1;

        function perPage() {
            return window.matchMedia('(min-width: 768px)').matches ? 4 : 1;
        }

        function ariaRangeForPage(p) {
            var per = perPage();
            var from = (p - 1) * per + 1;
            var to = Math.min(cards.length, p * per);
            return itemLabel + ' ' + from + '–' + to;
        }

        function applyVisibleCards() {
            var per = perPage();
            var p = activePage;
            if (totalPages <= 1) {
                cards.forEach(function (el) {
                    el.classList.remove('hidden');
                });
                return;
            }
            cards.forEach(function (el, i) {
                var cardPage = Math.floor(i / per) + 1;
                el.classList.toggle('hidden', cardPage !== p);
            });
        }

        function setPageButtonStyles() {
            var ap = activePage;
            pageButtons.forEach(function (btn, i) {
                var n = i + 1;
                var isAct = n === ap;
                btn.classList.toggle('bg-blue-600', isAct);
                btn.classList.toggle('text-white', isAct);
                btn.classList.toggle('border-blue-600', isAct);
                btn.classList.toggle('shadow-sm', isAct);
                btn.classList.toggle('border', !isAct);
                btn.classList.toggle('border-gray-300', !isAct);
                btn.classList.toggle('bg-white', !isAct);
                btn.classList.toggle('text-gray-800', !isAct);
                btn.setAttribute('aria-current', isAct ? 'page' : 'false');
            });
            if (prevArrow) prevArrow.disabled = ap <= 1;
            if (nextArrow) nextArrow.disabled = ap >= totalPages;
        }

        function buildPagination() {
            var per = perPage();
            totalPages = Math.max(1, Math.ceil(cards.length / per));
            activePage = Math.min(Math.max(1, activePage), totalPages);

            pagesWrap.innerHTML = '';
            pageButtons = [];

            if (totalPages <= 1) {
                pagination.hidden = true;
                applyVisibleCards();
                return;
            }

            pagination.hidden = false;

            for (var p = 1; p <= totalPages; p++) {
                var b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('data-paginated-page', String(p));
                b.className = 'inline-flex min-h-10 min-w-10 shrink-0 touch-manipulation items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-800 tabular-nums hover:bg-gray-50';
                b.textContent = String(p);
                b.setAttribute('aria-label', 'Страница ' + p + ', ' + ariaRangeForPage(p));
                (function (pageNum) {
                    b.addEventListener('click', function () {
                        goToPage(pageNum);
                    });
                })(p);
                pagesWrap.appendChild(b);
                pageButtons.push(b);
            }

            applyVisibleCards();
            setPageButtonStyles();
        }

        function goToPage(p) {
            if (p < 1 || p > totalPages) return;
            activePage = p;
            applyVisibleCards();
            setPageButtonStyles();
        }

        if (prevArrow) {
            prevArrow.addEventListener('click', function () {
                goToPage(activePage - 1);
            });
        }
        if (nextArrow) {
            nextArrow.addEventListener('click', function () {
                goToPage(activePage + 1);
            });
        }

        resizeFns.push(buildPagination);
        buildPagination();

        var ro = typeof ResizeObserver !== 'undefined' ? new ResizeObserver(buildPagination) : null;
        if (ro) ro.observe(root);
    });

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            resizeFns.forEach(function (fn) {
                fn();
            });
        }, 100);
    });
});
</script>
@endpush

