@extends('layouts.teacher')

@section('title', 'Профиль преподавателя')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-8 text-white">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4">
                        @if($user->avatar_url)
                            <button
                                type="button"
                                class="h-20 w-20 overflow-hidden rounded-full border-2 border-white/40 bg-white/20 transition hover:scale-[1.03] focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                aria-label="Открыть аватар в полном размере"
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
                            <h1 class="text-3xl font-bold">Профиль преподавателя</h1>
                        </div>
                    </div>
                    <a href="{{ route('profile.avatar.edit') }}" class="inline-flex items-center rounded-lg border border-white/50 bg-white/15 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/25">
                        Изменить фотографию
                    </a>
                </div>
            </div>

            <!-- Основная информация -->
            <div class="p-6 space-y-6">
                @if(session('success'))
                    <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @error('avatar')
                    <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $message }}
                    </div>
                @enderror

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
                    </div>
                </div>

                <!-- Статистика -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Статистика</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">Созданные новости</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->createdNews->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">Созданные соревнования</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->createdCompetitions->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <div>
                                    <p class="text-sm text-gray-600">Созданные виды спорта</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $user->createdSports->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

