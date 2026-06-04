@php
    $cellClass = ($cellClass ?? 'px-4 py-3 whitespace-nowrap align-top text-sm text-gray-700')
        . (isset($hiddenBreakpoint) && $hiddenBreakpoint === 'md' ? ' hidden md:table-cell' : '')
        . (isset($hiddenBreakpoint) && $hiddenBreakpoint === 'lg' ? ' hidden lg:table-cell' : '')
        . (isset($hiddenBreakpoint) && $hiddenBreakpoint === 'xl' ? ' hidden xl:table-cell' : '');
@endphp
<td class="{{ trim($cellClass) }}" @if(!empty($rowspan)) rowspan="{{ $rowspan }}" @endif>
    @include('competitions.student.partials.competition-participation-badge', ['competition' => $competition])
</td>
