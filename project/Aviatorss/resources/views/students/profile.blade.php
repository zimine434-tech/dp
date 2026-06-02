@extends('layouts.teacher')

@section('title', trim($user->lastname.' '.$user->firstname))

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-8 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4">
                        @if($user->avatar_url)
                            <button
                                type="button"
                                class="h-20 w-20 overflow-hidden rounded-full border-2 border-white/40 bg-white/20 transition hover:scale-[1.03] focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                aria-label="Аватар студента"
                            >
                                <img src="{{ $user->avatar_url }}" alt="Аватар" class="h-full w-full object-cover">
                            </button>
                        @else
                            <div class="h-20 w-20 overflow-hidden rounded-full border-2 border-white/40 bg-white/20">
                                <div class="flex h-full w-full items-center justify-center">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-3xl font-bold">Профиль студента</h1>
                        </div>
                    </div>
                    <a href="{{ route('students.index') }}" class="inline-flex items-center justify-center rounded-lg border border-white/50 bg-white/15 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/25">
                        Назад к списку
                    </a>
                </div>
            </div>

            <div class="p-6 space-y-6">
                @if(session('success'))
                    <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Основная информация</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Логин</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">{{ $user->login }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Имя</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">{{ $user->firstname }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Фамилия</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">{{ $user->lastname }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Отчество</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">{{ $user->patronymic ?? 'Не указано' }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Группа</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">{{ $user->group_name ?? 'Не указана' }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Дополнительная информация</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Статус физического организатора</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">
                                @if($user->status_fizorg)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Да
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Нет
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('students.toggle-fizorg', $user) }}" method="POST" class="mt-4" onsubmit="return confirm('{{ $user->status_fizorg ? 'Снять статус физорга?' : 'Назначить физоргом?' }}')">
                        @csrf
                        <button type="submit" class="{{ $user->status_fizorg ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            {{ $user->status_fizorg ? 'Снять статус физорга' : 'Назначить физоргом' }}
                        </button>
                    </form>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Статистика</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-blue-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-600">Команды</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->participantTeamParticipationDisplayCount() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-green-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-600">Соревнования</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->participantFinishedCompetitionParticipationCount() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-purple-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-600">Тренировки</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->participantFinishedTrainingRegistrationCount() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
