<div class="flex h-full min-w-0 flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
    <div class="flex min-w-0 flex-1 flex-col p-5">
        <div class="mb-3 flex items-start justify-between gap-2">
            <h3 class="min-w-0 flex-1 text-base font-bold leading-snug text-gray-900 sm:text-lg">
                <a href="{{ route('training-sessions.show', $session) }}" class="transition hover:text-blue-600">
                    {{ $session->title }}
                </a>
            </h3>
            @if($session->status === 'scheduled')
                <span class="inline-flex shrink-0 items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                    Запланирована
                </span>
            @elseif($session->status === 'in_progress')
                <span class="inline-flex shrink-0 items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                    Идет
                </span>
            @elseif($session->status === 'cancelled')
                <span class="inline-flex shrink-0 items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                    Отменена
                </span>
            @elseif($session->status === 'completed')
                <span class="inline-flex shrink-0 items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                    Завершена
                </span>
            @endif
        </div>
        <div class="mb-3 space-y-1.5 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <span class="text-gray-400 shrink-0">{{ $session->sport?->name ?? '—' }}</span>
                <span class="text-gray-300">·</span>
                <span class="truncate">{{ $session->team?->name ?? 'Без команды' }}</span>
            </div>
            <div>
                {{ $session->start_time->format('d.m.Y') }},
                {{ $session->start_time->format('H:i') }} – {{ $session->end_time->format('H:i') }}
            </div>
            <div class="line-clamp-2 text-xs text-gray-500">
                {{ $session->location?->location ?? 'Локация не указана' }}
            </div>
        </div>
        @if(filled($session->description))
            <p class="mb-3 line-clamp-2 flex-1 text-xs text-gray-600 sm:text-sm">
                {{ \App\Support\RichTextPlain::fromHtml($session->description, 120) }}
            </p>
        @endif
        <div class="mt-auto">
            <a
                href="{{ route('training-sessions.show', $session) }}"
                class="block w-full rounded-lg bg-blue-600 px-3 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
            >
                Подробнее
            </a>
        </div>
    </div>
</div>
