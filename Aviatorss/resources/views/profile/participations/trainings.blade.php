@extends('layouts.student')

@section('title', 'Авиатор')

@section('content')
    @php
        $trainingStatusLabels = [
            'scheduled' => 'Запланирована',
            'upcoming' => 'Запланирована',
            'in_progress' => 'Идёт',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
        ];
        $listingFilters = $listingFilters ?? [];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Посещенные тренировки</h1>
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
            :action="route('profile.participations.trainings')"
            :reset-url="route('profile.participations.trainings')"
            :listing-filters="$listingFilters"
            :sports-for-filter="$sportsForFilter"
            id-prefix="pt"
            listing-search-root-id="pt-listing-root"
        />

        @if($registrations->isNotEmpty())
            <div class="rounded-lg bg-white p-4 shadow-md sm:p-6">
                <div id="pt-listing-root">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($registrations as $registration)
                        @php($t = $registration->training)
                        <a
                            href="{{ route('training-sessions.show', ['trainingSession' => $t]) }}"
                            class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2"
                            data-listing-search-haystack="{{ mb_strtolower((string) ($t->title ?? ''), 'UTF-8') }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-blue-700">{{ $t->title }}</h2>
                            </div>
                            @if($t->start_time)
                                <p class="mt-3 text-sm text-gray-700">
                                    <span class="font-semibold">{{ $t->start_time->translatedFormat('d F Y') }}</span>
                                    <span class="text-gray-600">, {{ $t->start_time->format('H:i') }}</span>
                                </p>
                            @endif
                            @if($t->sport?->name)
                                <p class="mt-2 text-sm text-gray-600">{{ $t->sport->name }}</p>
                            @endif
                            @if($registration->registered_at)
                                <p class="mt-2 text-sm text-gray-500">
                                    Регистрация: {{ $registration->registered_at->translatedFormat('d F Y') }}, {{ $registration->registered_at->format('H:i') }}
                                </p>
                            @endif
                            <div class="mt-auto flex flex-wrap gap-2 pt-4">
                                @if($t->status)
                                    @php($ts = $trainingStatusLabels[$t->status] ?? $t->status)
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-800">{{ $ts }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm font-medium text-blue-600 group-hover:text-blue-800">Подробнее →</p>
                        </a>
                    @endforeach
                    </div>
                    @if($registrations->hasPages())
                        <div class="border-t border-gray-100 px-4 py-3">
                            {{ $registrations->links('pagination::tailwind') }}
                        </div>
                    @endif
                    <p id="pt-listing-root-js-empty" class="hidden py-10 text-center text-sm text-gray-600">
                        По названию ничего не найдено.
                    </p>
                </div>
            </div>
        @else
            <div class="rounded-lg bg-white px-6 py-12 text-center shadow-md">
                @php($hasListingFilters = collect($listingFilters)->filter(fn ($v) => $v !== null && trim((string) $v) !== '')->isNotEmpty())
                @if($hasListingFilters)
                    <p class="text-sm font-medium text-gray-900">Нет тренировок по выбранным условиям</p>
                    <p class="mt-1 text-sm text-gray-500">Попробуйте изменить фильтры или нажмите «Сбросить».</p>
                @else
                    <p class="text-sm font-medium text-gray-900">Завершённых тренировок с вашей записью пока нет</p>
                @endif
            </div>
        @endif
    </div>
@endsection
