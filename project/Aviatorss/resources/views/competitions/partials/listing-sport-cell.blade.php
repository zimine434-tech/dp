@php
    $showRouteParams = $showRouteParams ?? [];
    $isPersonal = $competition->isPersonalCompetition();
    $displayText = $competition->sportListingText();
    $showParticipantsLink = $competition->showParticipantsListLink();
    $participantsUrl = $competition->participantsListUrl($showRouteParams);
    $emptyTeamLabel = $emptyTeamLabel ?? '—';
    $emptyPersonalLabel = $emptyPersonalLabel ?? 'Виды спорта появятся после формирования состава';
@endphp

@if($displayText)
    <span>{{ $displayText }}</span>
    @if($showParticipantsLink)
        @include('competitions.partials.participants-sports-link-icon', ['href' => $participantsUrl])
    @endif
@elseif($isPersonal)
    <span class="text-gray-500">{{ $emptyPersonalLabel }}</span>
@else
    <span class="text-gray-500">{{ $emptyTeamLabel }}</span>
@endif
