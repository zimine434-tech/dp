@php
    /** @var \App\Models\Competition $competition */
    /** @var \App\Models\CompetitionResult|null $result */
    $hasResult = $result !== null;
    $isPersonalRow = $hasResult && \App\Support\CompetitionResultPage::isPersonalResultListing($competition, $result);
    $sportUserId = $isPersonalRow && $result->user_id ? (int) $result->user_id : null;
    $sportName = \App\Support\CompetitionResultPage::resolveSportNameForUser($competition, $sportUserId);
    $sportId = \App\Support\CompetitionResultPage::resolveSportIdForUser($competition, $sportUserId);
    $participantName = $isPersonalRow
        ? \App\Support\CompetitionResultPage::formatResultParticipantName($competition, $result)
        : null;
    $listingTd = 'px-4 py-3 align-top border-b border-gray-300';
    $listingTdParticipant = 'pl-6 pr-4 py-3 align-top border-b border-gray-300';
    $competitionShowQuery = $competitionShowQuery ?? ['from' => 'results'];
    $linkToCompetitionShow = ! empty($linkNameToCompetitionShow);
    $competitionNameHref = $linkToCompetitionShow
        ? route('competitions.show', array_merge(['competition' => $competition], $competitionShowQuery))
        : route('competitions.results.show', $competition);
    $participantListHref = $linkToCompetitionShow
        ? route('competitions.show', array_merge(['competition' => $competition], $competitionShowQuery))
        : route('competitions.results.show', $competition);
    $detailHref = $linkToCompetitionShow
        ? route('competitions.show', array_merge(['competition' => $competition], $competitionShowQuery))
        : route('competitions.results.show', $competition);
@endphp
<tr class="competition-row results-listing-row"
    data-competition-id="{{ $competition->id }}"
    data-start-date="{{ $competition->start_date->format('Y-m-d') }}"
    data-end-date="{{ $competition->end_date->format('Y-m-d') }}"
    data-sport-id="{{ $sportId ?? '' }}"
    data-name="{{ mb_strtolower($competition->name) }}"
    @if(!empty($rowGroup)) data-group="{{ $rowGroup }}" @endif
>
    <td class="{{ $listingTd }} max-w-md">
        <div class="font-semibold text-gray-900">
            @include('competitions.partials.listing-name-link', [
                'competition' => $competition,
                'href' => $competitionNameHref,
                'linkClass' => 'text-gray-900 hover:text-blue-600 transition',
            ])
        </div>
        <div class="text-sm text-gray-500 mt-1 lg:hidden">
            @if($isPersonalRow)
                {{ $sportName }} · {{ $participantName }}
                · {{ $competition->start_date->format('d.m.Y') }}–{{ $competition->end_date->format('d.m.Y') }}
            @else
                {{ $sportName }} • {{ $competition->resultFormatLabel() }} • {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
            @endif
        </div>
    </td>
    <td class="{{ $listingTd }} text-sm text-gray-700 hidden lg:table-cell">
        {{ $sportName }}
    </td>
    <td class="{{ $listingTd }} text-sm text-gray-700 hidden xl:table-cell">
        {{ $competition->start_date->format('d.m.Y') }} - {{ $competition->end_date->format('d.m.Y') }}
    </td>
    <td class="{{ $listingTd }} text-sm text-gray-700 hidden xl:table-cell">
        {{ $competition->category->name_category ?? 'Не указана' }}
    </td>
    @include('competitions.partials.participation-type-cell', [
        'competition' => $competition,
        'hiddenBreakpoint' => 'xl',
        'cellClass' => $listingTd . ' whitespace-nowrap text-sm text-gray-700',
    ])

    <td class="{{ $listingTd }}">
        @if($hasResult)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if(is_numeric($result->place) && (int) $result->place === 1) bg-yellow-100 text-yellow-800
                @elseif(is_numeric($result->place) && (int) $result->place === 2) bg-gray-200 text-gray-800
                @elseif(is_numeric($result->place) && (int) $result->place === 3) bg-orange-100 text-orange-800
                @else bg-blue-100 text-blue-800
                @endif">
                {{ $result->place }}
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Нет места
            </span>
        @endif
    </td>

    <td class="{{ $listingTdParticipant }}">
        @if($isPersonalRow)
            <span class="text-sm font-medium text-gray-900">{{ $participantName }}</span>
        @else
            <div class="flex items-center min-h-[32px]">
                <a
                    href="{{ $participantListHref }}"
                    class="inline-flex items-center text-blue-600 hover:text-blue-900 py-1 rounded hover:bg-blue-50 transition"
                >
                    Список участников
                </a>
            </div>
        @endif
    </td>

    @if(auth()->user()->role === 'teacher')
        <td class="px-3 sm:px-4 py-3 text-right text-sm font-medium align-top border-b border-gray-300">
            <div class="flex flex-col sm:flex-row sm:flex-wrap items-end sm:items-center justify-end gap-1 sm:gap-2">
                @if(!empty($showAddResultAction) || ! $hasResult)
                    @include('competitions.partials.add-result-action', ['competition' => $competition])
                @endif
                <a
                    href="{{ route('competitions.photos', $competition) }}"
                    class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded hover:bg-indigo-50 transition whitespace-nowrap"
                >
                    Добавить фотографии
                </a>
                @if(!empty($showDetailLink))
                    <a
                        href="{{ $detailHref }}"
                        class="text-gray-700 hover:text-gray-900 px-3 py-1 rounded hover:bg-gray-100 transition"
                    >
                        Подробнее
                    </a>
                @endif
            </div>
        </td>
    @endif
</tr>
