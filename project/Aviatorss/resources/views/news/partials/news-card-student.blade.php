<article class="flex h-full min-h-0 min-w-0 w-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md transition hover:shadow-md">
    @if($item->images->isNotEmpty() || filled(data_get($item->getAttributes(), 'image_path')))
        @include('news.partials.news-cover', ['item' => $item, 'stacked' => true])
    @endif
    <div class="flex min-w-0 flex-1 flex-col p-5 sm:p-6">
        <h3 class="mb-2 text-lg font-bold leading-snug text-gray-900">
            <a href="{{ route('news.show', ['news' => $item]) }}" class="transition hover:text-blue-600">
                {{ $item->name }}
            </a>
        </h3>
        <div class="min-h-0 flex-1 text-sm leading-relaxed text-gray-600">
            @if(filled($item->description))
                @include('news.partials.news-description-excerpt', [
                    'description' => $item->description,
                    'url' => route('news.show', ['news' => $item]),
                    'limit' => 220,
                ])
            @else
                <span class="text-gray-400 italic">Описание отсутствует</span>
            @endif
        </div>
        <div class="mt-auto pt-4">
            <p class="mb-2 text-left text-xs text-gray-500">{{ $item->date->format('d.m.Y') }}</p>
            <a
                href="{{ route('news.show', ['news' => $item]) }}"
                class="block w-full rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
            >
                Подробнее
            </a>
        </div>
    </div>
</article>
