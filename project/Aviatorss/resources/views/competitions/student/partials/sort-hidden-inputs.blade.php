@php
    $cardsSortStack = $cardsSortStack ?? [];
    $listSortStack = $listSortStack ?? [];
@endphp
@if(count($cardsSortStack) === 0)
    <input type="hidden" name="cards_sort" value="none">
@else
    @foreach($cardsSortStack as $item)
        <input type="hidden" name="cards_sort[]" value="{{ $item['field'] }}">
        <input type="hidden" name="cards_order[]" value="{{ $item['order'] }}">
    @endforeach
@endif
@if(count($listSortStack) === 0)
    <input type="hidden" name="list_sort" value="none">
@else
    @foreach($listSortStack as $item)
        <input type="hidden" name="list_sort[]" value="{{ $item['field'] }}">
        <input type="hidden" name="list_order[]" value="{{ $item['order'] }}">
    @endforeach
@endif
