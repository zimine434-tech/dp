@php
    $stacked = $stacked ?? false;
    $wrapperClass = $stacked
        ? 'ml-2 flex shrink-0 flex-col items-end gap-1'
        : 'inline-flex shrink-0 flex-wrap items-center justify-end gap-1';
@endphp

<div class="{{ $wrapperClass }}">
    @include('competitions.student.partials.competition-participation-badge', ['competition' => $competition])
    @include('competitions.student.partials.competition-event-status-badge', ['competition' => $competition])
</div>
