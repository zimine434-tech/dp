<tr class="hover:bg-gray-50 session-row" data-status="{{ $session->status }}" data-start-time="{{ $session->start_time->timestamp }}" data-end-time="{{ $session->end_time->timestamp }}">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">{{ $session->title }}</div>
        @if($session->description)
            <div class="text-xs text-gray-500 line-clamp-1 hidden sm:block mt-1">
                {{ Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($session->description ?? '')))), 50) }}
            </div>
        @endif
    </td>
    @if(!isset($hideSport) || !$hideSport)
        <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
            <div class="text-sm text-gray-900">{{ $session->sport->name }}</div>
            <div class="text-xs text-gray-500">{{ $session->team->name ?? 'Без команды' }}</div>
        </td>
    @endif
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">
            {{ $session->start_time->format('d.m.Y') }}
        </div>
        <div class="text-xs text-gray-500">
            {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
        <div class="text-sm text-gray-900">{{ $session->location->location ?? 'Не указана' }}</div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        @if($session->status === 'scheduled')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Запланирована
            </span>
        @elseif($session->status === 'in_progress')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Идет
            </span>
        @elseif($session->status === 'cancelled')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Отменена
            </span>
        @elseif($session->status === 'completed')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Завершена
            </span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <div class="flex items-center justify-end gap-2">
            <a 
                href="{{ route('training-sessions.show', ['trainingSession' => $session]) }}" 
                class="text-blue-600 hover:text-blue-900"
            >
                Подробнее
            </a>
        </div>
    </td>
</tr>

