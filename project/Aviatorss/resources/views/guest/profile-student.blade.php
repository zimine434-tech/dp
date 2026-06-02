@extends('layouts.guest')

@section('title', 'Публичный профиль участника')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="overflow-hidden rounded-lg bg-white shadow-lg">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-8 text-white">
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
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Группа</label>
                            <p class="text-lg text-gray-900 bg-gray-50 px-4 py-2 rounded border">{{ $user->group_name ?? 'Не указана' }}</p>
                        </div>
                    </div>
                </div>

                @isset($teamCoaches)
                    @if($teamCoaches->isNotEmpty())
                        <div>
                            <h2 class="mb-4 border-b pb-2 text-xl font-semibold text-gray-800">Тренер</h2>
                            <ul class="space-y-3">
                                @foreach($teamCoaches as $coach)
                                    <li class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Преподаватель</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                {{ trim(($coach->lastname ?? '').' '.($coach->firstname ?? '').' '.($coach->patronymic ?? '')) }}
                                            </p>
                                        </div>
                                        <a
                                            href="{{ route('guest.users.show', $coach) }}"
                                            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                                        >
                                            Подробнее
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endisset
            </div>
        </div>

        @include('guest.partials.profile-competitions', ['participations' => $participations])
    </div>
@endsection

