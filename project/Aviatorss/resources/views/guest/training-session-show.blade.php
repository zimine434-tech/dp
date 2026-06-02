@extends('layouts.guest')

@section('title', $trainingSession->title)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $trainingSession->title }}</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Публичная информация о тренировке</p>
                </div>
                <a 
                    href="{{ route('guest.training-sessions') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                >
                    Назад к списку
                </a>
            </div>
        </div>


        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Основная информация</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Название</label>
                    <p class="text-lg text-gray-900">{{ $trainingSession->title }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Вид спорта</label>
                    <p class="text-lg text-gray-900">{{ $trainingSession->sport?->name ?? '—' }}</p>
                    <p class="text-sm text-gray-500">
                        @if($trainingSession->team)
                            <a href="{{ route('guest.teams.show', ['team' => $trainingSession->team]) }}" class="text-blue-600 hover:text-blue-800" title="Состав команды">
                                {{ $trainingSession->team->name }}
                            </a>
                        @else
                            Без команды
                        @endif
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата и время начала</label>
                    <p class="text-lg text-gray-900">{{ $trainingSession->start_time->format('d.m.Y H:i') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата и время окончания</label>
                    <p class="text-lg text-gray-900">{{ $trainingSession->end_time->format('d.m.Y H:i') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Локация</label>
                    <p class="text-lg text-gray-900">{{ $trainingSession->location->location ?? 'Не указана' }}</p>
                    @if($trainingSession->location && $trainingSession->location->description)
                        <p class="text-sm text-gray-500">{{ $trainingSession->location->description }}</p>
                    @endif
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Статус</label>
                    <div class="mt-1">
                        @if($trainingSession->status === 'scheduled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Запланирована
                            </span>
                        @elseif($trainingSession->status === 'in_progress')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Идет
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($trainingSession->description)
                <div class="mt-6 pt-6 border-t">
                    <label class="text-sm font-medium text-gray-500 block mb-2">Описание</label>
                    @include('partials.rich-text', ['html' => $trainingSession->description, 'class' => 'text-gray-700'])
                </div>
            @endif
        </div>

        <!-- Информация о регистрации -->
        <div class="bg-blue-50 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Хотите принять участие?</h3>
                    <p class="text-gray-700 mb-4">Для регистрации на тренировку необходимо войти в систему.</p>
                    <a href="{{ route('login') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Войти
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

