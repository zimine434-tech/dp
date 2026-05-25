{{--
  Карусель фото новости (листание, если больше 1).
  @param \Illuminate\Support\Collection|array $images — модели NewsImage с ->url
  @param string $altTitle — подпись / alt
  @param string $description — текст для lightbox
  @param string|null $imgMaxClass — классы ограничения высоты картинки (по умолчанию max-h-[min(70vh,36rem)])
--}}
@php
    /** @var bool $fillCover — на всю ширину/высоту области (object-cover), без полей по бокам */
    $fillCover = $fillCover ?? false;
    $imgExtraClass = $imgMaxClass ?? 'max-h-[min(70vh,36rem)]';
    $total = $images->count();
    $lightboxGalleryUrls = $images->map(function ($i) {
        return $i->url;
    })->values()->all();
@endphp
<div
    class="relative flex w-full max-w-full min-w-0 flex-1 touch-pan-y flex-col news-images-carousel {{ $fillCover ? 'h-full min-h-0' : 'h-full min-h-[10rem]' }}"
    data-news-carousel
    data-carousel-index="0"
>
    {{-- Явная ширина ленты и слайдов относительно вьюпорта карусели, иначе translateX(-100%) даёт сдвиг на всю дорожку и торчит соседний кадр --}}
    <div class="min-h-0 w-full min-w-0 max-w-full overflow-hidden {{ $fillCover ? 'h-full' : 'flex-1' }}">
        <div
            class="flex transition-transform duration-700 ease-out {{ $fillCover ? 'h-full min-h-0' : 'h-full min-h-[10rem]' }}"
            data-carousel-track
            style="width: {{ $total * 100 }}%; transform: translateX(0%);"
        >
            @foreach($images as $img)
                <div
                    class="box-border flex shrink-0 self-stretch {{ $fillCover ? 'h-full min-h-0 items-stretch justify-stretch' : 'min-h-[10rem] items-center justify-center' }}"
                    style="width: {{ 100 / $total }}%;"
                    data-carousel-slide
                >
                    <button
                        type="button"
                        class="relative flex min-h-0 min-w-0 cursor-zoom-in {{ $fillCover ? 'h-full w-full' : 'block h-full min-h-[10rem] w-full max-w-full self-stretch overflow-hidden' }}"
                        data-news-lightbox
                        data-lightbox-src="{{ $img->url }}"
                        data-lightbox-gallery='@json($lightboxGalleryUrls)'
                        data-lightbox-index="{{ $loop->index }}"
                        data-lightbox-alt="{{ $altTitle }}"
                        data-lightbox-title=""
                        data-lightbox-description=""
                        aria-label="Открыть изображение"
                    >
                        @if($fillCover)
                            <img
                                src="{{ $img->url }}"
                                alt="{{ $altTitle }}"
                                class="block h-full min-h-0 w-full min-w-0 max-w-none object-cover object-center"
                                loading="lazy"
                            >
                        @else
                            <span class="pointer-events-none absolute inset-0 overflow-hidden bg-gray-100" aria-hidden="true">
                                <img
                                    src="{{ $img->url }}"
                                    alt=""
                                    class="h-full w-full scale-125 object-cover opacity-80 blur-2xl"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </span>
                            <img
                                src="{{ $img->url }}"
                                alt="{{ $altTitle }}"
                                class="relative z-10 mx-auto block h-auto max-w-full w-auto object-contain object-center {{ $imgExtraClass }}"
                                loading="lazy"
                            >
                        @endif
                    </button>
                </div>
            @endforeach
        </div>
    </div>
    @if($total > 1)
        <button
            type="button"
            data-carousel-prev
            class="absolute left-1 top-1/2 z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white shadow-md hover:bg-black/65 focus:outline-none focus:ring-2 focus:ring-white/80 sm:left-2"
            aria-label="Предыдущее фото"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button
            type="button"
            data-carousel-next
            class="absolute right-1 top-1/2 z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white shadow-md hover:bg-black/65 focus:outline-none focus:ring-2 focus:ring-white/80 sm:right-2"
            aria-label="Следующее фото"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        <div class="absolute bottom-2 left-0 right-0 z-20 flex justify-center gap-2 px-2">
            @foreach($images as $idx => $img)
                <button
                    type="button"
                    data-carousel-dot
                    data-carousel-index="{{ $idx }}"
                    class="h-2 w-2 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 focus:ring-offset-gray-50 {{ $idx === 0 ? 'bg-blue-600' : 'bg-gray-400/80' }}"
                    aria-label="Фото {{ $idx + 1 }} из {{ $total }}"
                    aria-current="{{ $idx === 0 ? 'true' : 'false' }}"
                ></button>
            @endforeach
        </div>
        <span
            data-carousel-counter
            class="pointer-events-none absolute top-2 right-2 z-20 rounded-full bg-black/60 px-2 py-0.5 text-xs font-medium text-white"
        >
            1 / {{ $total }}
        </span>
    @endif
</div>
