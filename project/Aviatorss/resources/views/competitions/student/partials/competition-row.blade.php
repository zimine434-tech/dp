<tr class="hover:bg-gray-50">
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
        <a href="{{ route('competitions.show', $competition) }}" class="hover:text-blue-600 transition">
            {{ $competition->name }}
        </a>
    </td>
    <td class="px-6 py-4 text-sm text-gray-700 hidden md:table-cell">
        @php
            $sportNames = $competition->sportNamesForListing();
            $isPersonal = $competition->isPersonalCompetition();
        @endphp
        @if($sportNames->isNotEmpty())
            {{ $sportNames->join(', ') }}
        @elseif($isPersonal)
            <span class="text-gray-500">Виды спорта появятся после формирования состава</span>
        @else
            —
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
        @if($competition->start_date && $competition->end_date)
            @if($competition->start_date->format('Y-m-d') === $competition->end_date->format('Y-m-d'))
                {{ $competition->start_date->format('d.m.Y') }}
            @else
                {{ $competition->start_date->format('d.m.Y') }} — {{ $competition->end_date->format('d.m.Y') }}
            @endif
        @elseif($competition->start_date)
            {{ $competition->start_date->format('d.m.Y') }}
        @elseif($competition->end_date)
            {{ $competition->end_date->format('d.m.Y') }}
        @else
            —
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 hidden lg:table-cell">
        <div>
            <div>{{ $competition->location?->location ?? '—' }}</div>
            @if(filled($competition->location?->address))
                <div class="text-sm text-gray-500">Адрес: {{ $competition->location->address }}</div>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        @include('competitions.student.partials.competition-participation-badge', ['competition' => $competition])
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        @include('competitions.student.partials.competition-event-status-badge', ['competition' => $competition])
    </td>
    <td class="px-6 py-4 text-right text-sm font-medium">
        <div class="flex flex-col items-end gap-1">
            @php
                $studentApplicationStates = $studentApplicationStates ?? [];
                $applicationState = $studentApplicationStates[$competition->id] ?? null;
            @endphp
            @if($applicationState === 'participant')
                <span class="text-green-700">Вы участвуете</span>
            @elseif($applicationState === 'pending')
                <span class="text-amber-700">Заявка подана</span>
            @elseif($applicationState === 'can_apply')
                <a href="{{ route('competitions.show', $competition) }}#my-application" class="text-blue-600 hover:text-blue-900">Подать заявку</a>
            @endif
            <a href="{{ route('competitions.show', $competition) }}" class="text-blue-600 hover:text-blue-900">Подробнее</a>
        </div>
    </td>
</tr>

