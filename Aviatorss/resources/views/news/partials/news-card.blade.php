@php
    $hasCover = $item->images->isNotEmpty() || filled(data_get($item->getAttributes(), 'image_path'));
@endphp
@if($type === 'published')
    <article class="flex h-full min-h-0 min-w-0 w-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md transition hover:border-green-300 hover:shadow-lg">
        @if($hasCover)
            @include('news.partials.news-cover', ['item' => $item, 'stacked' => true])
        @endif
        <div class="flex min-h-0 min-w-0 flex-1 flex-col p-5 sm:p-6">
            <h3 class="text-lg font-bold leading-snug break-words text-gray-900">{{ $item->name }}</h3>

            <div class="mt-2 min-h-0 flex-1 text-sm leading-relaxed text-gray-600">
                @if($item->description)
                    @include('news.partials.news-description-excerpt', [
                        'description' => $item->description,
                        'url' => route('news.show', ['news' => $item]),
                        'limit' => 220,
                    ])
                @else
                    <span class="text-gray-400 italic">Описание отсутствует</span>
                @endif
            </div>

            <div class="mt-3 space-y-2 border-t border-gray-100 pt-3 text-xs text-gray-500">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="inline-flex items-center rounded-full border border-green-200 bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">
                        Опубликовано
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $item->date->format('d.m.Y') }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>{{ $item->creator ? $item->creator->firstname.' '.$item->creator->lastname : 'Не указан' }}</span>
                </div>
            </div>

            <div class="mt-auto grid grid-cols-1 gap-2 pt-4 sm:grid-cols-2">
                <a
                    href="{{ route('news.show', ['news' => $item]) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 sm:col-span-1"
                >
                    Подробнее
                </a>
                <a
                    href="{{ route('news.edit', $item) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-center text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 sm:col-span-1"
                >
                    Редактировать
                </a>
                <form
                    action="{{ route('news.destroy', $item) }}"
                    method="POST"
                    class="sm:col-span-2"
                    onsubmit="return confirm('Вы уверены, что хотите удалить эту новость?')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700"
                        title="Удалить новость"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </article>
@else
    <article class="flex h-full min-h-0 min-w-0 w-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md transition hover:border-gray-300 hover:shadow-lg">
        @if($hasCover)
            @include('news.partials.news-cover', ['item' => $item, 'stacked' => true])
        @endif
        <div class="flex min-h-0 min-w-0 flex-1 flex-col p-5 sm:p-6">
            <h3 class="text-lg font-bold leading-snug break-words text-gray-900">{{ $item->name }}</h3>

            <div class="mt-2 min-h-0 flex-1 text-sm leading-relaxed text-gray-600">
                @if($item->description)
                    @include('news.partials.news-description-excerpt', [
                        'description' => $item->description,
                        'url' => route('news.show', ['news' => $item]),
                        'limit' => 220,
                    ])
                @else
                    <span class="text-gray-400 italic">Описание отсутствует</span>
                @endif
            </div>

            <div class="mt-3 space-y-2 border-t border-gray-100 pt-3 text-xs text-gray-500">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-800">
                        Черновик
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $item->date->format('d.m.Y') }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>{{ $item->creator ? $item->creator->firstname.' '.$item->creator->lastname : 'Не указан' }}</span>
                </div>
            </div>

            <div class="mt-auto grid grid-cols-1 gap-2 pt-4 sm:grid-cols-2">
                <form
                    action="{{ route('news.publish', $item) }}"
                    method="POST"
                    class="sm:col-span-2"
                    onsubmit="return confirm('Вы уверены, что хотите опубликовать эту новость?')"
                >
                    @csrf
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-center text-sm font-medium text-white shadow-sm transition hover:bg-green-700"
                    >
                        Опубликовать
                    </button>
                </form>
                <a
                    href="{{ route('news.show', ['news' => $item]) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                >
                    Подробнее
                </a>
                <a
                    href="{{ route('news.edit', $item) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-center text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Редактировать
                </a>
                <form
                    action="{{ route('news.destroy', $item) }}"
                    method="POST"
                    class="sm:col-span-2"
                    onsubmit="return confirm('Вы уверены, что хотите удалить эту новость?')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700"
                        title="Удалить новость"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </article>
@endif
