@if($news->count() > 0)
    <div class="grid w-full grid-cols-1 items-stretch gap-4 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
        @foreach($news as $item)
            @if($type === 'student')
                @include('news.partials.news-card-student', ['item' => $item])
            @elseif($type === 'guest')
                @include('news.partials.news-card-guest', ['item' => $item])
            @else
                @include('news.partials.news-card', ['item' => $item, 'type' => $type])
            @endif
        @endforeach
    </div>
@endif

