@extends('layouts.student')

@section('title', 'Авиатор')

@section('content')
    @php
        $statusLabels = [
            'upcoming' => 'Предстоящее',
            'ongoing' => 'Идёт',
            'finished' => 'Завершено',
            'cancelled' => 'Отменено',
        ];
        $listingFilters = $listingFilters ?? [];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Завершённые соревнования, в которых вы участвовали</h1>
            </div>
            <a
                href="{{ route('profile') }}"
                role="button"
                class="inline-flex shrink-0 items-center justify-center self-start rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
            >
                ← Назад в профиль
            </a>
        </div>

        <x-student-listing-filters-bar
            :action="route('profile.participations.competitions')"
            :reset-url="route('profile.participations.competitions')"
            :listing-filters="$listingFilters"
            :sports-for-filter="$sportsForFilter"
            id-prefix="pc"
            listing-search-root-id="pc-listing-root"
        />

        @if($participations->isNotEmpty())
            <div class="rounded-lg bg-white p-4 shadow-md sm:p-6">
                <div id="pc-listing-root">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($participations as $participation)
                        @php($c = $participation->competition)
                        <a
                            href="{{ route('competitions.show', ['competition' => $c]) }}"
                            class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
                            data-listing-search-haystack="{{ mb_strtolower((string) ($c->name ?? ''), 'UTF-8') }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700">{{ $c->name }}</h2>
                            </div>
                            <p class="mt-3 text-sm text-gray-600">
                                @if($c->start_date)
                                    <span class="font-medium text-gray-800">{{ $c->start_date->translatedFormat('d F Y') }}</span>
                                    @if($c->end_date && !$c->end_date->equalTo($c->start_date))
                                        - {{ $c->end_date->translatedFormat('d F Y') }}
                                    @endif
                                @else
                                    Даты уточняются
                                @endif
                                @if($c->sport?->name)
                                    <span class="mt-1 block text-gray-700">{{ $c->sport->name }}</span>
                                @endif
                            </p>
                            @if($c->location?->name)
                                <p class="mt-2 text-sm text-gray-600">{{ $c->location->name }}</p>
                            @endif
                            <div class="mt-auto flex flex-wrap gap-2 pt-4">
                                @if($c->status)
                                    @php($st = $statusLabels[$c->status] ?? $c->status)
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800">{{ $st }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm font-medium text-blue-600 group-hover:text-blue-800">Подробнее →</p>
                        </a>
                    @endforeach
                    </div>
                    @if($participations->hasPages())
                        <div class="border-t border-gray-100 px-4 py-3">
                            {{ $participations->links('pagination::tailwind') }}
                        </div>
                    @endif
                    <p id="pc-listing-root-js-empty" class="hidden py-10 text-center text-sm text-gray-600">
                        По названию ничего не найдено.
                    </p>
                </div>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                @php($hasListingFilters = collect($listingFilters ?? [])->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isNotEmpty())
                @if($hasListingFilters)
                    <p class="text-sm font-medium text-gray-900">Нет соревнований по выбранным условиям</p>
                    <p class="mt-1 text-sm text-gray-500">Измените фильтры или нажмите «Сбросить».</p>
                @else
                    <p class="text-sm font-medium text-gray-900">Завершённых соревнований с вашим участием пока нет</p>
                @endif
            </div>
        @endif
    </div>
@endsection
