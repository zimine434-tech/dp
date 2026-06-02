@extends('layouts.guest')

@section('title', $sport->name)

@section('content')
    <div class="space-y-8">
        <div
            class="flex flex-col gap-4 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-800 p-6 text-white shadow-lg sm:flex-row sm:items-center sm:justify-between sm:p-8"
        >
            <h1 class="min-w-0 break-words text-3xl font-bold tracking-tight sm:text-4xl">{{ $sport->name }}</h1>
            <a
                href="{{ route('guest.sports') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium backdrop-blur transition hover:bg-white/20 sm:self-auto"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Назад к списку
            </a>
        </div>

        <div
            class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-md sm:p-8"
        >
            <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl bg-gradient-to-b from-blue-500 to-indigo-600" aria-hidden="true"></div>
            <div class="pl-4 sm:pl-5">
                <div class="mb-4 flex items-center gap-2">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                        aria-hidden="true"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7"
                            />
                        </svg>
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900 sm:text-xl">Описание</h2>
                </div>
                @if($sport->description)
                    @include('partials.rich-text', ['html' => $sport->description, 'class' => 'text-base leading-relaxed text-gray-700'])
                @else
                    <p class="rounded-lg border border-dashed border-gray-200 bg-gray-50/80 px-4 py-6 text-center text-sm italic text-gray-500">
                        Описание пока не добавлено.
                    </p>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-md sm:p-8">
            <div class="mb-4 flex items-center gap-2">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                    aria-hidden="true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                </span>
                <h2 class="text-lg font-semibold text-gray-900 sm:text-xl">Команды</h2>
            </div>

            @if($sport->teams->isNotEmpty())
                <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3" role="list">
                    @foreach($sport->teams as $team)
                        <li>
                            <a
                                href="{{ route('guest.teams.show', ['team' => $team]) }}"
                                class="group flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/80 px-4 py-3 transition hover:border-blue-200 hover:bg-white hover:shadow-sm"
                            >
                                <span class="min-w-0 truncate font-medium text-gray-900 group-hover:text-blue-700">{{ $team->name }}</span>
                                <svg
                                    class="h-5 w-5 shrink-0 text-gray-400 transition group-hover:text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="rounded-lg border border-dashed border-gray-200 bg-gray-50/80 px-4 py-6 text-center text-sm text-gray-500">
                    Команд по этому виду спорта пока нет.
                </p>
            @endif
        </div>
    </div>
@endsection
