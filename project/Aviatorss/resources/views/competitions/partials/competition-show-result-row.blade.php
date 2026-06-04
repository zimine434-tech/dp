@php
    /** @var \App\Models\Competition $competition */
    /** @var \App\Models\CompetitionResult $result */
    $isPersonalRow = \App\Support\CompetitionResultPage::isPersonalResultListing($competition, $result);
    $participantName = $isPersonalRow
        ? \App\Support\CompetitionResultPage::formatResultParticipantName($competition, $result)
        : null;
    $sportName = $isPersonalRow
        ? \App\Support\CompetitionResultPage::resolveSportNameForUser($competition, $result->user_id ? (int) $result->user_id : null)
        : null;
    $listingTd = 'px-3 sm:px-6 py-3 sm:py-4 align-top border-b border-gray-300';
@endphp
<tr class="results-listing-row" data-result-id="{{ $result->id }}">
    @if($isPersonalRow)
        <td class="{{ $listingTd }}">
            <span class="text-sm font-semibold text-gray-900">{{ $competition->name }}</span>
        </td>
    @endif
    <td class="{{ $listingTd }} whitespace-nowrap">
        @include('partials.competition-place-badge', ['place' => $result->place])
    </td>
    @if($isPersonalRow)
        <td class="{{ $listingTd }}">
            <span class="text-sm font-medium text-gray-900">{{ $participantName }}</span>
        </td>
        <td class="{{ $listingTd }} whitespace-nowrap">
            <span class="text-sm text-gray-900">{{ $sportName }}</span>
        </td>
    @else
        <td class="{{ $listingTd }} whitespace-nowrap">
            <span class="text-sm text-gray-900">{{ $competition->category->name_category ?? 'Не указана' }}</span>
        </td>
    @endif
    <td class="{{ $listingTd }} text-right text-sm font-medium">
        <div class="flex items-center justify-end gap-2">
            <button
                type="button"
                onclick="editResult({{ $result->id }}, '{{ addslashes($result->place) }}')"
                class="text-blue-600 hover:text-blue-900 px-3 py-1 rounded hover:bg-blue-50 transition"
            >
                Редактировать
            </button>
            <form
                action="{{ route('competitions.results.destroy', [$competition, $result]) }}"
                method="POST"
                class="inline"
                onsubmit="return confirm('Вы уверены, что хотите удалить этот результат?')"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="text-red-600 hover:text-red-900 px-3 py-1 rounded hover:bg-red-50 transition"
                >
                    Удалить
                </button>
            </form>
        </div>
    </td>
</tr>
