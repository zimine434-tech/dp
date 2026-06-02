@extends('layouts.student')

@section('title', $competition->name)

@section('content')
    @php
        $place = trim((string) ($result->place ?? ''));
        $placeInt = is_numeric($place) ? (int) $place : null;
        $placeBadgeClass = match ($placeInt) {
            1 => 'bg-yellow-100 text-yellow-900 border-yellow-200',
            2 => 'bg-gray-100 text-gray-900 border-gray-200',
            3 => 'bg-orange-100 text-orange-900 border-orange-200',
            default => 'bg-blue-100 text-blue-900 border-blue-200',
        };
        $participationBadgeClass = $isPersonal
            ? 'bg-indigo-100 text-indigo-800'
            : 'bg-green-100 text-green-800';
        $backUrl = route('profile.portfolio', $portfolioListQuery ?? []);
    @endphp

    <div class="mx-auto max-w-2xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 break-words">{{ $competition->name }}</h1>
            </div>
            <a
                href="{{ $backUrl }}"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
            >
                Назад в портфолио
            </a>
        </div>

        <div class="rounded-xl border-2 {{ $placeBadgeClass }} bg-white p-6 shadow-md">
            <div class="mb-4">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $participationBadgeClass }}">
                    {{ $isPersonal ? 'Личное участие' : 'Командное участие' }}
                </span>
            </div>

            <div class="text-center">
                <div class="text-4xl font-bold text-gray-900 sm:text-5xl">
                    {{ $place !== '' ? $place.' место' : '—' }}
                </div>
                @if($isPersonal)
                    <p class="mt-1 text-sm text-gray-600">Ваше личное место</p>
                @else
                    <p class="mt-1 text-sm text-gray-600">Место команды</p>
                @endif
            </div>

            <dl class="mt-6 space-y-3 border-t border-gray-200/80 pt-5 text-sm">
                @if(filled($sportName))
                    <div class="flex flex-col gap-0.5 sm:flex-row sm:justify-between sm:gap-4">
                        <dt class="font-medium text-gray-500">Вид спорта</dt>
                        <dd class="text-gray-900 sm:text-right">{{ $sportName }}</dd>
                    </div>
                @endif

                @if(!$isPersonal && $competition->team?->name)
                    <div class="flex flex-col gap-0.5 sm:flex-row sm:justify-between sm:gap-4">
                        <dt class="font-medium text-gray-500">Команда</dt>
                        <dd class="text-gray-900 sm:text-right">{{ $competition->team->name }}</dd>
                    </div>
                @endif

                <div class="flex flex-col gap-0.5 sm:flex-row sm:justify-between sm:gap-4">
                    <dt class="font-medium text-gray-500">Дата</dt>
                    <dd class="text-gray-900 sm:text-right">{{ $datesLabel }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">О соревновании</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Категория</dt>
                    <dd class="mt-1 text-gray-900">{{ $competition->category?->name_category ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Локация</dt>
                    <dd class="mt-1 text-gray-900">{{ $competition->location?->location ?? '—' }}</dd>
                </div>
            </dl>

            @if(filled($competition->description))
                <details class="mt-4 group">
                    <summary class="cursor-pointer text-sm font-medium text-blue-600 hover:text-blue-800">
                        Показать описание
                    </summary>
                    <div class="mt-3 rounded-lg border border-gray-100 bg-gray-50 p-4">
                        @include('partials.rich-text', ['html' => $competition->description, 'class' => 'text-sm text-gray-700'])
                    </div>
                </details>
            @endif
        </div>
    </div>
@endsection
