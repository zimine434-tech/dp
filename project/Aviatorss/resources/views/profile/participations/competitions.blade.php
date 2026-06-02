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

        @php $lf = $listingFilters; @endphp
        <form method="GET" action="{{ route('profile.participations.competitions') }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <input type="hidden" name="page" value="1">
            <div class="flex flex-col gap-4 lg:flex-row lg:flex-nowrap lg:items-end lg:gap-4">
                <div class="w-full min-w-0 shrink-0 lg:w-48 xl:w-52">
                    <label for="pc_sport_combobox_trigger" class="mb-1 block text-sm font-medium text-gray-700">Спорт</label>
                    <x-teacher-sport-combobox
                        :sports="$sportsForFilter"
                        :selected="$lf['sport_id'] ?? null"
                        name="sport_id"
                        input-id="pc_sport"
                        empty-label="Все виды"
                        variant="filter"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <label for="pc_q" class="mb-1 block text-sm font-medium text-gray-700">Поиск по названию</label>
                    <input type="search" id="pc_q" name="q" value="{{ $lf['q'] ?? '' }}" maxlength="200" placeholder="Введите название..." autocomplete="off" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3 sm:flex sm:shrink-0 sm:gap-3">
                    <div class="min-w-0 sm:w-40">
                        <label for="pc_date_from" class="mb-1 block text-sm font-medium text-gray-700">От</label>
                        <input type="date" id="pc_date_from" name="date_from" value="{{ $lf['date_from'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div class="min-w-0 sm:w-40">
                        <label for="pc_date_to" class="mb-1 block text-sm font-medium text-gray-700">До</label>
                        <input type="date" id="pc_date_to" name="date_to" value="{{ $lf['date_to'] ?? '' }}" class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex w-full shrink-0 gap-2 lg:w-auto">
                    <button type="submit" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Применить</button>
                    <a href="{{ route('profile.participations.competitions') }}" class="inline-flex h-10 min-w-[7.5rem] flex-1 items-center justify-center rounded-md border border-gray-300 bg-gray-200 px-4 text-sm font-medium text-gray-800 shadow-sm transition hover:bg-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 lg:flex-none">Сбросить</a>
                </div>
            </div>
        </form>

        @if($participations->isNotEmpty())
            <div class="rounded-lg bg-white p-4 shadow-md sm:p-6">
                <div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($participations as $participation)
                        @php($c = $participation->competition)
                        <a
                            href="{{ route('competitions.show', ['competition' => $c]) }}"
                            class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700">{{ $c->name }}</h2>
                                @include('competitions.student.partials.competition-status-badges', ['competition' => $c, 'stacked' => true])
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
                            </p>
                            @php($sportNames = $c->sportNamesForListing())
                            @if($sportNames->isNotEmpty())
                                <p class="mt-1 text-sm text-gray-700">
                                    {{ $sportNames->count() === 1 ? 'Вид спорта' : 'Виды спорта' }}: {{ $sportNames->join(', ') }}
                                </p>
                            @endif
                            @if($c->location?->name)
                                <p class="mt-2 text-sm text-gray-600">{{ $c->location->name }}</p>
                            @endif
                            <p class="mt-auto pt-4 text-sm font-medium text-blue-600 group-hover:text-blue-800">Подробнее →</p>
                        </a>
                    @endforeach
                    </div>
                    @if($participations->hasPages())
                        <div class="border-t border-gray-100 px-4 py-3">
                            {{ $participations->links() }}
                        </div>
                    @endif
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
