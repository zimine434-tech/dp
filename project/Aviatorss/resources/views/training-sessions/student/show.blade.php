@extends('layouts.student')

@section('title', 'Детали тренировочной сессии')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $trainingSession->title }}</h1>
                    @if(filled($trainingSession->description))
                        @include('partials.rich-text', ['html' => $trainingSession->description, 'class' => 'mt-1 text-sm text-gray-600 sm:text-base'])
                    @endif
                </div>
                <a 
                    href="{{ route('training-sessions.student.index') }}" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Назад к списку
                </a>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Основная информация</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Вид спорта</label>
                    <p class="text-lg text-gray-900">{{ $trainingSession->sport?->name ?? '—' }}</p>
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
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Идет
                            </span>
                        @elseif($trainingSession->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Отменена
                            </span>
                        @elseif($trainingSession->status === 'completed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Завершена
                            </span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Действия -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Действия</h2>
            
            @php
                $user = auth()->user();
                $isRegistered = $trainingSession->registrations->where('user_id', $user->id)->count() > 0;
                $conflictingTraining = $conflictingRegistration?->training;
                $hasTimeConflict = $conflictingTraining !== null;
                $canRegister = $trainingSession->status === 'scheduled' && ! $isRegistered && ! $hasTimeConflict;
                $canUnregister = $trainingSession->status === 'scheduled' && $isRegistered;
                $registrationClosed = in_array($trainingSession->status, ['in_progress', 'completed'], true);
            @endphp

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded mb-4">
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

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($hasTimeConflict && ! $isRegistered)
                <div class="mb-4 rounded border-l-4 border-amber-400 bg-amber-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-800">
                                {{ \App\Support\TrainingRegistrationOverlap::conflictMessage($conflictingTraining) }}
                                <a href="{{ route('training-sessions.show', $conflictingTraining) }}" class="font-medium text-blue-600 hover:text-blue-800">Перейти к тренировке</a>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                @if($registrationClosed)
                    <p class="min-w-0 flex-1 text-sm text-gray-600">
                        Регистрация на тренировку завершена.
                    </p>
                @else
                    @if($canRegister)
                        <form 
                            action="{{ route('training-sessions.register', $trainingSession) }}" 
                            method="POST" 
                            class="inline"
                        >
                            @csrf
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                Зарегистрироваться
                            </button>
                        </form>
                    @elseif($canUnregister)
                        <form 
                            action="{{ route('training-sessions.unregister', $trainingSession) }}" 
                            method="POST" 
                            class="inline"
                            onsubmit="return confirm('Вы уверены, что хотите отменить регистрацию на эту тренировку?')"
                        >
                            @csrf
                            @if(in_array(request()->query('from'), ['profile', 'dashboard'], true))
                                <input type="hidden" name="return_from" value="{{ request()->query('from') }}">
                            @endif
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                            >
                                Отменить регистрацию
                            </button>
                        </form>
                    @elseif($isRegistered && $trainingSession->status !== 'scheduled')
                        <div class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg">
                            Вы зарегистрированы на эту тренировку
                        </div>
                    @elseif($trainingSession->status !== 'scheduled')
                        <p class="text-gray-600">Регистрация доступна только для запланированных тренировок</p>
                    @endif
                @endif
            </div>
        </div>

        <!-- Участники -->
        @if($trainingSession->registrations->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Зарегистрированные участники ({{ $trainingSession->registrations->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата регистрации</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($trainingSession->registrations as $registration)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $registration->user->lastname }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $registration->user->firstname }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $registration->created_at->format('d.m.Y H:i') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Зарегистрированные участники</h2>
                <p class="text-gray-500">Пока никто не зарегистрирован на эту тренировку.</p>
            </div>
        @endif
    </div>
@endsection


