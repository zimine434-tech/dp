@php
    $firstImg = $news->images->first();
    $legacyPath = data_get($news->getAttributes(), 'image_path');
    $legacyUrl = null;
    if (!$firstImg && filled($legacyPath)) {
        $legacyUrl = asset('storage/' . ltrim(str_replace('\\', '/', $legacyPath), '/'));
    }
@endphp
@if($firstImg)
    <div class="w-full shrink-0 border-t border-gray-200 bg-gray-50 flex flex-col">
        <div class="flex max-h-[min(70vh,36rem)] min-h-[10rem] flex-1 flex-col overflow-hidden p-0">
            @include('news.partials.news-images-carousel', [
                'images' => $news->images,
                'altTitle' => $news->name,
                'description' => $news->description ?? '',
            ])
        </div>
    </div>
@elseif($legacyUrl)
    <div class="w-full shrink-0 border-t border-gray-200 bg-gray-50 flex flex-col">
        <div class="relative flex max-h-[min(70vh,36rem)] min-h-[10rem] flex-1 flex-col overflow-hidden p-0">
            <button
                type="button"
                class="relative block h-full min-h-[10rem] w-full cursor-zoom-in overflow-hidden"
                data-news-lightbox
                data-lightbox-src="{{ $legacyUrl }}"
                data-lightbox-alt="{{ $news->name }}"
                data-lightbox-gallery='@json([$legacyUrl])'
                data-lightbox-index="0"
                aria-label="Открыть изображение"
            >
                <span class="pointer-events-none absolute inset-0 overflow-hidden bg-gray-100" aria-hidden="true">
                    <img
                        src="{{ $legacyUrl }}"
                        alt=""
                        class="h-full w-full scale-125 object-cover opacity-80 blur-2xl"
                        loading="lazy"
                        decoding="async"
                    >
                </span>
                <img
                    src="{{ $legacyUrl }}"
                    alt="{{ $news->name }}"
                    class="relative z-10 mx-auto block h-auto max-h-[min(55vh,24rem)] max-w-full w-auto object-contain object-center md:max-h-[min(70vh,36rem)]"
                    loading="lazy"
                >
            </button>
        </div>
        @if(isset($showStoragePath) && $showStoragePath && filled($legacyPath))
            <div class="border-t border-gray-200 px-3 py-2 pb-3 text-xs text-gray-500 sm:px-4">
                <p class="mb-1 font-medium text-gray-600">Файл в хранилище:</p>
                <p><code class="break-all rounded bg-gray-100 px-1">{{ $legacyPath }}</code></p>
            </div>
        @endif
    </div>
@endif

@include('news.partials.news-lightbox')
