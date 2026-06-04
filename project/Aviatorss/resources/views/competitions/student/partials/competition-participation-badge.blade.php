@if($competition->isPersonalCompetition())
    <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-medium text-violet-800">
        {{ $competition->resultFormatLabel() }}
    </span>
@else
    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-800">
        {{ $competition->resultFormatLabel() }}
    </span>
@endif
