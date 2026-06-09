<div class="flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md transition hover:shadow-lg">
    @if($competition->images->isNotEmpty())
        @include('news.partials.news-cover', ['item' => $competition, 'stacked' => true])
    @endif
    <div class="flex min-w-0 flex-1 flex-col p-6">
        <div class="mb-3 flex items-start justify-between gap-3">
            <h3 class="flex-1 text-lg font-bold leading-tight text-gray-900">
                <a href="{{ route('competitions.show', $competition) }}" class="transition hover:text-blue-600">
                    {{ $competition->name }}
                </a>
            </h3>
            @include('competitions.student.partials.competition-status-badges', ['competition' => $competition, 'stacked' => true])
        </div>

        <div class="mb-4 space-y-2 text-sm text-gray-600">
            @include('competitions.student.partials.competition-sport-line', ['competition' => $competition])
            <div class="flex items-center">
                <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span>Категория: {{ $competition->category?->name_category ?? 'Не указана' }}</span>
            </div>
            <div class="flex items-center">
                <svg class="mr-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>
                    @if($competition->start_date && $competition->end_date)
                        @if($competition->start_date->format('Y-m-d') === $competition->end_date->format('Y-m-d'))
                            {{ $competition->start_date->format('d.m.Y') }}
                        @else
                            {{ $competition->start_date->format('d.m.Y') }} — {{ $competition->end_date->format('d.m.Y') }}
                        @endif
                    @elseif($competition->start_date)
                        {{ $competition->start_date->format('d.m.Y') }}
                    @elseif($competition->end_date)
                        {{ $competition->end_date->format('d.m.Y') }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="flex items-start">
                <svg class="mr-2 mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="block">
                    {{ $competition->location?->location ?? 'Не указана' }}
                    @if(filled($competition->location?->address))
                        <span class="block text-sm text-gray-500">Адрес: {{ $competition->location->address }}</span>
                    @endif
                </span>
            </div>
        </div>

        @if(filled($competition->description))
            <p class="mb-4 line-clamp-3 flex-1 text-sm text-gray-600">
                {{ \App\Support\RichTextPlain::fromHtml($competition->description, 150) }}
            </p>
        @endif

        <div class="mt-auto flex flex-col gap-2">
            @include('competitions.student.partials.application-status-action', ['competition' => $competition])
            <a
                href="{{ route('competitions.show', $competition) }}"
                class="block rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-800 transition hover:border-gray-400 hover:bg-gray-50"
            >
                Подробнее
            </a>
        </div>
    </div>
</div>

