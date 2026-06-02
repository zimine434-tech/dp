@props([
    'competitionsWithResults',
    'competitionsWithoutResults' => null,
    'competitionShowQuery' => ['from' => 'results'],
])

@php
    $without = $competitionsWithoutResults ?? collect();
    $showQuery = $competitionShowQuery;
@endphp

<div class="grid grid-cols-1 items-stretch gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3 xl:grid-cols-4">
    @foreach($competitionsWithResults as $competition)
        @php
            $sortedResults = $competition->results->sortBy(function ($r) {
                if (is_numeric($r->place)) {
                    return (int) $r->place;
                }

                return 9999 + ord($r->place[0] ?? 'z');
            })->values();
        @endphp
        @foreach($sortedResults as $result)
            <div class="min-w-0">
                @include('competitions.partials.finished-result-card', [
                    'competition' => $competition,
                    'result' => $result,
                    'competitionShowQuery' => $showQuery,
                ])
            </div>
        @endforeach
    @endforeach

    @foreach($without as $competition)
        <div class="min-w-0">
            @include('competitions.partials.finished-result-card', [
                'competition' => $competition,
                'result' => null,
                'competitionShowQuery' => $showQuery,
            ])
        </div>
    @endforeach
</div>
