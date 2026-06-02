@php
    $studentApplicationStates = $studentApplicationStates ?? [];
    $applicationState = $applicationState ?? ($studentApplicationStates[$competition->id] ?? null);
    $applyUrl = route('competitions.show', $competition).'#my-application';
@endphp

@if($applicationState === 'participant')
    <p class="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-center text-sm font-medium text-green-900">
        Вы участвуете в этом соревновании
    </p>
@elseif($applicationState === 'pending')
    <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm font-medium text-amber-900">
        Заявка подана
    </p>
@elseif($applicationState === 'can_apply')
    <a
        href="{{ $applyUrl }}"
        class="block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
    >
        Подать заявку
    </a>
@endif
