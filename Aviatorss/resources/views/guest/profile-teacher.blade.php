@extends('layouts.guest')

@section('title', 'Публичный профиль преподавателя')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="overflow-hidden rounded-lg bg-white shadow-lg">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-8 text-white">
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
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold">Публичный профиль</h1>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Основная информация</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
            </div>
        </div>

        @include('guest.partials.profile-competitions', ['participations' => $participations])
    </div>
@endsection

