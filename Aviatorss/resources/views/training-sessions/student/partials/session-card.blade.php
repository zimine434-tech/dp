@php
    $isRegistered = $session->registrations->count() > 0;
    $canRegister = $session->status === 'scheduled' && ! $isRegistered;
@endphp
<div
    class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg"
    data-listing-search-haystack="{{ mb_strtolower((string) ($session->title ?? ''), 'UTF-8') }}"
>
    <div class="flex min-w-0 flex-1 flex-col p-6">
        <div class="mb-3 flex items-start justify-between gap-3">
            <h3 class="flex-1 text-lg font-bold leading-tight text-gray-900">
                <a href="{{ route('training-sessions.show', $session) }}" class="transition hover:text-blue-600">
                    {{ $session->title }}
                </a>
            </h3>
            @if($session->status === 'scheduled')
                <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                    Запланирована
                </span>
            @elseif($session->status === 'in_progress')
                <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                    Идет
                </span>
            @elseif($session->status === 'cancelled')
                <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                    Отменена
                </span>
            @elseif($session->status === 'completed')
                <span class="ml-2 inline-flex shrink-0 items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                    Завершена
                </span>
            @endif
        </div>

        <div class="mb-4 space-y-2 text-sm text-gray-600">
            <div class="flex items-center">
                <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ $session->sport?->name ?? 'Не указан' }}</span>
            </div>
            <div class="flex items-center">
                <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>
                    @if($session->start_time->format('Y-m-d') === $session->end_time->format('Y-m-d'))
                        {{ $session->start_time->format('d.m.Y') }}, {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                    @else
                        {{ $session->start_time->format('d.m.Y H:i') }} - {{ $session->end_time->format('d.m.Y H:i') }}
                    @endif
                </span>
            </div>
            <div class="flex items-start">
                <svg class="mr-2 mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ $session->location?->location ?? 'Не указана' }}</span>
            </div>
        </div>

        @if(filled($session->description))
            <p class="mb-4 line-clamp-3 flex-1 text-sm text-gray-600">
                {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($session->description ?? '')))), 150) }}
            </p>
        @endif

        <div class="mt-auto flex flex-col gap-2 sm:flex-row sm:gap-2">
            @if($session->status === 'completed')
                <a
                    href="{{ route('training-sessions.show', $session) }}"
                    class="block w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    Подробнее
                </a>
            @else
                @if($canRegister)
                    <a
                        href="{{ route('training-sessions.show', $session) }}"
                        class="block w-full rounded-lg bg-green-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-green-700 sm:min-w-0 sm:flex-1"
                    >
                        Записаться
                    </a>
                @elseif($isRegistered)
                    <span class="flex w-full items-center justify-center rounded-lg bg-blue-100 px-4 py-2 text-center text-sm font-medium text-blue-800 sm:min-w-0 sm:flex-1">
                        Записан
                    </span>
                @endif
                <a
                    href="{{ route('training-sessions.show', $session) }}"
                    class="block w-full rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700 sm:min-w-0 sm:flex-1"
                >
                    Подробнее
                </a>
            @endif
        </div>
    </div>
</div>
