@extends('layouts.student')

@section('title', 'Авиатор')

@section('content')
    @php
        /** @var \Illuminate\Support\Collection<int, \App\Models\TeamMember> $memberships */
        $hasRows = $memberships->isNotEmpty();
    @endphp
    <div class="mx-auto flex max-w-6xl flex-col gap-5 sm:gap-6">
        <div class="rounded-lg bg-blue-50/40 px-6 py-6 shadow-lg ring-1 ring-blue-100/70 sm:px-8">
            <div class="flex items-start justify-between gap-3 sm:gap-4">
                <h1 class="min-w-0 flex-1 pr-2 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">Команды</h1>
                <a
                    href="{{ route('profile') }}"
                    role="button"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg border border-blue-300/80 bg-white px-4 py-2.5 text-sm font-semibold text-blue-900 shadow-sm transition hover:bg-blue-100/50 hover:border-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 max-sm:py-2 max-sm:px-3 max-sm:text-xs"
                >
                    ← Назад в профиль
                </a>
            </div>
        </div>

        <div class="rounded-lg bg-white px-6 py-6 shadow-lg ring-1 ring-gray-200 sm:px-8 sm:py-8">
                @if($hasRows)
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($memberships as $membership)
                            @php($team = $membership->team)
                            @if($team)
                                <a
                                    href="{{ route('teams.show', ['team' => $team, 'from' => 'profile']) }}"
                                    class="group flex h-full flex-col overflow-hidden rounded-xl border border-blue-100 bg-white p-5 shadow-md transition hover:border-blue-200 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <h2 class="text-lg font-semibold text-gray-900 group-hover:text-blue-800">{{ $team->name }}</h2>
                                        <span class="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">Команда</span>
                                    </div>
                                    @if($membership->joined_at)
                                        <p class="mt-3 text-sm text-gray-600">
                                            Вход: <span class="font-medium text-gray-800">{{ $membership->joined_at->translatedFormat('d F Y') }}</span>
                                        </p>
                                    @endif
                                    <div class="mt-auto pt-4">
                                        @if($membership->out)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-900">
                                                Покинули {{ $membership->out->translatedFormat('d F Y') }}
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">Участник</span>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm font-medium text-blue-600 group-hover:text-blue-800">Открыть →</p>
                                </a>
                            @else
                                <div class="flex h-full flex-col overflow-hidden rounded-xl border border-dashed border-gray-200 bg-gray-50 p-5 shadow-sm">
                                    <h2 class="text-lg font-medium text-gray-500">Команда недоступна</h2>
                                    @if($membership->joined_at)
                                        <p class="mt-3 text-sm text-gray-600">Запись от {{ $membership->joined_at->translatedFormat('d F Y') }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-600">
                        У вас пока нет записей о командах.
                    </div>
                @endif
        </div>
    </div>
@endsection
