@extends('layouts.guest')

@section('title', 'Соревнования')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Соревнования</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Публичный список соревнований</p>
        </div>

        <!-- Список соревнований -->
        @if($competitions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($competitions as $competition)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-semibold text-gray-900 leading-tight flex-1">
                                    <a href="{{ route('guest.competitions.show', ['competition' => $competition]) }}" class="hover:text-blue-600 transition">
                                        {{ $competition->name }}
                                    </a>
                                </h3>
                                @if($competition->status === 'upcoming')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 flex-shrink-0 ml-2">
                                        Предстоящее
                                    </span>
                                @elseif($competition->status === 'ongoing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 ml-2">
                                        Идет
                                    </span>
                                @elseif($competition->status === 'finished')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 flex-shrink-0 ml-2">
                                        Завершено
                                    </span>
                                @endif
                            </div>
                            
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $competition->sport->name }}</span>
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
                            
                            <a href="{{ route('guest.competitions.show', ['competition' => $competition]) }}" class="block text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                Подробнее
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $competitions->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <p class="text-gray-500">Соревнований пока нет</p>
            </div>
        @endif
    </div>
@endsection

