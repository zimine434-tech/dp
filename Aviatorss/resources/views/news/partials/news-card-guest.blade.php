<article class="min-w-0 flex h-full min-h-0 w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
    @include('news.partials.news-cover', ['item' => $item, 'stacked' => true])
    <div class="flex min-h-0 flex-1 flex-col p-5">
        <h3 class="mb-2 text-lg font-semibold leading-snug text-gray-900">
            <a href="{{ route('guest.news.show', ['news' => $item]) }}" class="transition hover:text-blue-600">
                {{ $item->name }}
            </a>
        </h3>
        <p class="mb-0 min-h-0 flex-1 text-left text-sm leading-snug text-gray-600 break-words">@if(filled($item->description))@include('news.partials.news-description-excerpt', ['description' => $item->description, 'url' => route('guest.news.show', ['news' => $item])])@else<span class="text-gray-400 italic">Описание отсутствует</span>@endif</p>
        <div class="mt-4 text-sm text-gray-500">
            <span>{{ $item->date->format('d.m.Y') }}</span>
        </div>
        <a
            href="{{ route('guest.news.show', ['news' => $item]) }}"
            class="mt-4 block rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-blue-700"
        >
            Подробнее
        </a>
    </div>
</article>
