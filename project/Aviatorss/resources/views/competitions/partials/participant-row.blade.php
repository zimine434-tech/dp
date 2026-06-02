<tr class="hover:bg-gray-50 competition-participant-row" data-participant-user-id="{{ $participant->user_id }}">
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">
            {{ $participant->user->lastname }}
        </div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">
            {{ $participant->user->firstname }}
        </div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="text-sm text-gray-500">
            @if($participant->user->role === 'teacher')
                Преподаватель
            @else
                {{ $participant->user->group_name ?? 'Группа не указана' }}
            @endif
        </div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="text-sm text-gray-500">
            @if(($competition->result_type ?? 'team') === 'personal')
                {{ $participant->team?->name ?? '—' }}@if($participant->team?->sport) — {{ $participant->team->sport->name }}@endif
            @else
                {{ $competition->team?->name ?? '—' }}@if($competition->team?->sport) — {{ $competition->team->sport->name }}@endif
            @endif
        </div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        @php
            $currentRole = $participant->user->role ?? 'student';
            $roleLabels = [
                'student' => 'Участник',
                'teacher' => 'Преподаватель',
            ];
        @endphp
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            {{ $roleLabels[$currentRole] ?? 'Участник' }}
        </span>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 text-right text-sm font-medium">
        <button
            type="button"
            class="competition-participant-remove text-red-600 hover:text-red-900 px-3 py-1 rounded hover:bg-red-50 transition"
            data-remove-url="{{ route('competitions.participants.remove', [$competition, $participant->user]) }}"
            data-user-id="{{ $participant->user_id }}"
            data-is-student="{{ ($participant->role ?? 'student') === 'student' ? '1' : '0' }}"
        >
            Удалить
        </button>
    </td>
</tr>

