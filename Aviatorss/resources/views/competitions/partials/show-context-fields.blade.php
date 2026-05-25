@foreach($competitionShowContextQuery ?? [] as $contextKey => $contextValue)
    <input type="hidden" name="{{ $contextKey }}" value="{{ $contextValue }}">
@endforeach
