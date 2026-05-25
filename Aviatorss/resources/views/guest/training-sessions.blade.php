@extends('layouts.guest')

@section('title', 'Тренировки')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Тренировки</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Публичный список тренировок</p>
        </div>

        <!-- Список тренировок -->
        @if($trainingSessions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($trainingSessions as $training)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-semibold text-gray-900 leading-tight flex-1">
                                    <a href="{{ route('guest.training-sessions.show', ['trainingSession' => $training]) }}" class="hover:text-blue-600 transition">
                                        {{ $training->title }}
                                    </a>
                                </h3>
                                @if($training->status === 'scheduled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 flex-shrink-0 ml-2">
                                        Запланировано
                                    </span>
                                @elseif($training->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 ml-2">
                                        Идет
                                    </span>
                                @endif
                            </div>
                            
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $training->sport->name }}</span>
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
                            
                            <a href="{{ route('guest.training-sessions.show', ['trainingSession' => $training]) }}" class="block text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                Подробнее
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $trainingSessions->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Нет запланированных или текущих тренировок</p>
            </div>
        @endif
    </div>
@endsection

