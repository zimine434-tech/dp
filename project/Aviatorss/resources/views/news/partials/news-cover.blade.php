@php
    /** @var \App\Models\News|\App\Models\Competition $item */
    /** @var bool $stacked */
    $stacked = $stacked ?? false;
    $firstImg = $item->images->first();
    $legacyPath = data_get($item->getAttributes(), 'image_path');
    $legacyUrl = null;
    if (!$firstImg && filled($legacyPath)) {
        $legacyUrl = asset('storage/' . ltrim(str_replace('\\', '/', $legacyPath), '/'));
    }
    $coverOuter = $stacked
        ? 'w-full min-w-0 shrink-0 overflow-hidden bg-gray-100 flex flex-col border-b border-gray-200 h-48 sm:h-52'
        : 'w-full md:w-96 lg:w-[28rem] shrink-0 bg-gray-50 flex flex-col md:border-r border-gray-200 min-h-[10rem] max-h-[min(70vh,36rem)] md:h-full';
    $imgMax = $stacked ? 'max-h-48' : 'max-h-[min(70vh,36rem)]';
@endphp
@if($firstImg)
    <div class="{{ $coverOuter }}">
        <div class="{{ $stacked ? 'flex h-full w-full min-h-0 flex-1' : 'flex flex-1 items-center justify-center p-2 sm:p-3 min-h-0' }}">
            @include('news.partials.news-images-carousel', [
                'images' => $item->images,
                'altTitle' => $item->name,
                'description' => $item->description ?? '',
                'imgMaxClass' => $imgMax,
                'fillCover' => $stacked,
            ])
        </div>
    </div>
@elseif($legacyUrl)
    <div class="{{ $coverOuter }}">
        <div class="{{ $stacked ? 'flex h-full w-full min-h-0 flex-1' : 'flex flex-1 items-center justify-center p-2 sm:p-3' }}">
            <button
                type="button"
                class="relative block cursor-zoom-in {{ $stacked ? 'h-full w-full' : 'max-w-full' }}"
                data-news-lightbox
                data-lightbox-src="{{ $legacyUrl }}"
                data-lightbox-alt="{{ $item->name }}"
                data-lightbox-title=""
                data-lightbox-description=""
                data-lightbox-gallery='{!! json_encode([(string) $legacyUrl]) !!}'
                data-lightbox-index="0"
                aria-label="Открыть изображение"
            >
                <img
                    src="{{ $legacyUrl }}"
                    alt="{{ $item->name }}"
                    class="@if($stacked) block h-full w-full object-cover object-center @else block max-w-full {{ $imgMax }} w-auto h-auto object-contain object-center @endif"
                    loading="lazy"
                />
            </button>
        </div>
    </div>
@endif

@include('news.partials.news-lightbox')
