@php
    $showRouteParams = $showRouteParams ?? [];
    $isPersonal = $competition->isPersonalCompetition();
    $displayText = $competition->sportListingText();
    $showParticipantsLink = $competition->showParticipantsListLink();
    $participantsUrl = $competition->participantsListUrl($showRouteParams);
@endphp

@if($isPersonal)
    <div class="flex items-start">
        <svg class="mr-2 mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
        </svg>
        <span>
            @if($displayText)
                @if($competition->sportNamesForListing()->count() === 1)
                    Вид спорта: {{ $displayText }}
                @else
                    {{ $displayText }}
                @endif
                @if($showParticipantsLink)
                    @include('competitions.partials.participants-sports-link-icon', ['href' => $participantsUrl])
                @endif
            @else
                <span class="text-gray-500">Виды спорта появятся после формирования состава</span>
            @endif
        </span>
    </div>
@elseif($displayText)
    <div class="flex items-start">
        <svg class="mr-2 mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
        </svg>
        <span>{{ $displayText }}</span>
    </div>
@else
    <div class="flex items-center">
        <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
        </svg>
        <span>Не указан</span>
    </div>
@endif
