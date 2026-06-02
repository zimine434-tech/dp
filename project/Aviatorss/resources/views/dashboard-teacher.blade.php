@extends('layouts.teacher')

@section('title', 'Главная страница')

@section('content')
    <div class="space-y-10">
        <!-- Новости -->
        @if($publishedNews->count() > 0)
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Последние новости</h2>
                    <a href="{{ route('news.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Все новости →
                    </a>
                </div>
            <div class="grid grid-cols-1 items-stretch gap-3 sm:grid-cols-[repeat(2,minmax(0,1fr))] sm:gap-3 lg:grid-cols-[repeat(4,minmax(0,1fr))]">
                @foreach($publishedNews as $item)
                    <article class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                        @include('news.partials.news-cover', ['item' => $item, 'stacked' => true])
                        <div class="flex min-h-0 flex-1 flex-col p-5">
                            <h3 class="mb-2 text-lg font-semibold leading-snug text-gray-900">
                                <a href="{{ route('news.show', ['news' => $item]) }}" class="transition hover:text-blue-600">
                                    {{ $item->name }}
                                </a>
                            </h3>
                                <p class="mb-0 min-h-0 flex-1 text-left text-sm leading-snug text-gray-600 break-words">@if(filled($item->description))@include('news.partials.news-description-excerpt', ['description' => $item->description, 'url' => route('news.show', ['news' => $item])])@else<span class="text-gray-400 italic">Описание отсутствует</span>@endif</p>
                            <div class="mt-4 text-sm text-gray-500">
                                <span>{{ $item->date->format('d.m.Y') }}</span>
                            </div>
                            <a
                                href="{{ route('news.show', ['news' => $item]) }}"
                                class="mt-4 block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Подробнее
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
            </div>
        @endif

        <!-- Ближайшие соревнования -->
        @if($upcomingCompetitions->count() > 0)
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Ближайшие соревнования</h2>
                    <div class="flex gap-4">
                        <a href="{{ route('competitions.results') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                            Результаты →
                        </a>
                        <a href="{{ route('competitions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Все соревнования →
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 items-stretch gap-3 sm:grid-cols-[repeat(2,minmax(0,1fr))] sm:gap-3 lg:grid-cols-[repeat(4,minmax(0,1fr))]">
                    @foreach($upcomingCompetitions as $competition)
                        <div class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                            @if($competition->images->isNotEmpty())
                                @include('news.partials.news-cover', ['item' => $competition, 'stacked' => true])
                            @endif
                            <div class="flex min-h-0 flex-1 flex-col p-6">
                                <div class="mb-3 flex shrink-0 items-start justify-between">
                                    <h3 class="min-w-0 flex-1 text-lg font-bold leading-tight text-gray-900">
                                        <a href="{{ route('competitions.show', ['competition' => $competition]) }}" class="hover:text-blue-600">
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
                                        <span>
                                            Участие: {{ ($competition->result_type ?? 'team') === 'personal' ? 'Личное' : 'Командное' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <span>Категория: {{ $competition->category?->name_category ?? 'Не указана' }}</span>
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
                                
                                <a href="{{ route('competitions.show', ['competition' => $competition]) }}" class="mt-4 block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Ближайшие тренировки -->
        @if($upcomingTrainingSessions->count() > 0)
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Ближайшие тренировки</h2>
                    <a href="{{ route('training-sessions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Все тренировки →
                    </a>
                </div>
                <div class="grid grid-cols-1 items-stretch gap-3 sm:grid-cols-[repeat(2,minmax(0,1fr))] sm:gap-3 lg:grid-cols-[repeat(4,minmax(0,1fr))]">
                    @foreach($upcomingTrainingSessions as $training)
                        <div class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
                            <div class="flex min-h-0 flex-1 flex-col p-6">
                                <div class="mb-3 flex shrink-0 items-start justify-between">
                                    <h3 class="min-w-0 flex-1 text-lg font-bold leading-tight text-gray-900">
                                        <a href="{{ route('training-sessions.show', ['trainingSession' => $training]) }}" class="hover:text-blue-600">
                                            {{ $training->title }}
                                        </a>
                                    </h3>
                                    @if($training->status === 'scheduled')
                                        <span class="ml-2 inline-flex flex-shrink-0 items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            Запланировано
                                        </span>
                                    @elseif($training->status === 'in_progress')
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
                                        <span>{{ $training->sport?->name ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>
                                            @if($training->start_time->format('Y-m-d') === $training->end_time->format('Y-m-d'))
                                                {{ $training->start_time->format('d.m.Y H:i') }} - {{ $training->end_time->format('H:i') }}
                                            @else
                                                {{ $training->start_time->format('d.m.Y H:i') }} - {{ $training->end_time->format('d.m.Y H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                    @if($training->location)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>{{ $training->location->location }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <a href="{{ route('training-sessions.show', ['trainingSession' => $training]) }}" class="mt-4 block shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700">
                                    Подробнее
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Ближайшие тренировки</h2>
                    <a href="{{ route('training-sessions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Все тренировки →
                    </a>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Нет запланированных или текущих тренировок</p>
                </div>
            </div>
        @endif
    </div>
@endsection

