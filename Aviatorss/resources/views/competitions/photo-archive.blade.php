@extends('layouts.teacher')

@section('title', 'Архив соревнований')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Архив соревнований</h1>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">
                    Завершенные соревнования, которые закончились более 6 месяцев назад (до {{ ($archiveThreshold ?? now()->subMonths(6))->format('d.m.Y') }}).
                </p>
            </div>
            <div class="flex flex-wrap gap-2 justify-end">
                <a
                    href="{{ route('competitions.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    К соревнованиям
                </a>
                <a
                    href="{{ route('competitions.results') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium whitespace-nowrap"
                >
                    <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    Результаты
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md border border-gray-100 p-4 sm:p-6">
            <form method="GET" action="{{ route('competitions.photo-archive') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1" for="q">Название</label>
                    <input
                        id="q"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Поиск по названию соревнования..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        type="text"
                    >
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1" for="date_from">От (дата начала)</label>
                    <input
                        id="date_from"
                        name="date_from"
                        value="{{ $dateFrom ?? '' }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        type="date"
                    >
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1" for="date_to">До (дата окончания)</label>
                    <input
                        id="date_to"
                        name="date_to"
                        value="{{ $dateTo ?? '' }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        type="date"
                    >
                </div>
                <div class="sm:col-span-4 flex flex-wrap gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Применить
                    </button>
                    <a href="{{ route('competitions.photo-archive') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm font-medium">
                        Сбросить
                    </a>
                </div>
            </form>
        </div>

        @php
            $competitions = $competitions ?? collect();
        @endphp

        @if ($competitions->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center text-gray-600">
                Ничего не найдено.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($competitions as $competition)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-3 py-3 border-b border-gray-100 bg-gray-50/80">
                            <div class="flex flex-col gap-2">
                                <div class="font-semibold text-gray-900 text-base leading-tight">
                                    <a href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'photo-archive'] + request()->only(['q', 'date_from', 'date_to'])) }}" class="hover:text-blue-600 transition">
                                        {{ $competition->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-gray-600">
                                    {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Участники: {{ $competition->participants->count() }} •
                                    Фото: {{ ($competition->images ?? collect())->count() }}
                                </div>
                                <div class="pt-1">
                                    <a
                                        href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'photo-archive'] + request()->only(['q', 'date_from', 'date_to'])) }}"
                                        class="inline-flex items-center justify-center px-2.5 py-1 border border-gray-300 bg-white text-gray-700 rounded-md hover:bg-gray-50 transition text-xs font-medium whitespace-nowrap"
                                    >
                                        Список участников
                                    </a>
                                </div>
                                @if(auth()->user()->role === 'teacher')
                                <div class="pt-1">
                                    <a
                                        href="{{ route('competitions.photos', $competition) }}"
                                        class="inline-flex items-center justify-center px-2.5 py-1 border border-gray-300 bg-white text-gray-700 rounded-md hover:bg-gray-50 transition text-xs font-medium whitespace-nowrap"
                                    >
                                        Добавить/посмотреть фото
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="p-3">
                            @php
                                $images = $competition->images ?? collect();
                                $coverImage = $images->first();
                            @endphp
                            @if ($images->isEmpty())
                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                                    Для этого соревнования пока нет фотографий.
                                </div>
                            @else
                                @php
                                    $galleryUrls = $images->map(fn ($i) => $i->url)->values()->all();
                                    $total = $images->count();
                                @endphp
                                <div class="relative rounded-md border border-gray-100 overflow-hidden bg-gray-50" data-news-carousel data-carousel-index="0">
                                    <div class="w-full overflow-hidden">
                                        <div
                                            class="flex transition-transform duration-300 ease-out"
                                            data-carousel-track
                                            style="width: {{ $total * 100 }}%; transform: translateX(0%);"
                                        >
                                            @foreach ($images as $img)
                                                <div class="shrink-0" style="width: {{ 100 / $total }}%;" data-carousel-slide>
                                                    <button
                                                        type="button"
                                                        class="block w-full aspect-[16/8.5]"
                                                    >
                                                        <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @if ($total > 1)
                                        <button
                                            type="button"
                                            data-carousel-prev
                                            class="absolute left-1 top-1/2 z-20 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/65"
                                            aria-label="Предыдущее фото"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            data-carousel-next
                                            class="absolute right-1 top-1/2 z-20 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/65"
                                            aria-label="Следующее фото"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                        <span data-carousel-counter class="pointer-events-none absolute top-2 right-2 z-20 rounded-full bg-black/60 px-2 py-0.5 text-xs font-medium text-white">
                                            1 / {{ $total }}
                                        </span>
                                    @endif
                                </div>
                                @if(auth()->user()->role === 'teacher')
                                <div class="mt-2 flex justify-end">
                                    <form
                                        action="{{ route('competitions.photo-archive.destroy', $coverImage) }}"
                                        method="POST"
                                        onsubmit="return confirm('Удалить это фото?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-xs font-medium bg-red-600 text-white rounded shadow hover:bg-red-700">
                                            Удалить фотографию
                                        </button>
                                    </form>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection


