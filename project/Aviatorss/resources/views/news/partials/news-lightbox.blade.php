@once
    <div id="newsImageLightbox" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true" aria-label="Просмотр изображения">
        <div class="absolute inset-0 bg-black/55 backdrop-blur-md backdrop-saturate-150" data-lightbox-backdrop></div>
        <div class="relative z-10 flex h-full min-h-0 w-full flex-col p-3 pt-11 sm:p-6 sm:pt-12">
            <button type="button" data-lightbox-close class="absolute right-3 top-3 z-30 rounded-lg bg-black/35 px-2.5 py-1 text-2xl leading-none text-white shadow-lg backdrop-blur-sm hover:bg-black/55" aria-label="Закрыть">&times;</button>
            <div class="flex min-h-0 min-w-0 flex-1 flex-col gap-2 sm:gap-3">
                <div class="flex min-h-0 min-w-0 flex-1 flex-row items-center justify-center gap-2 sm:gap-4">
                    <button type="button" data-lightbox-prev class="lightbox-nav-prev z-20 hidden max-md:hidden md:inline-flex shrink-0 self-center items-center justify-center rounded-full bg-black/45 p-2 text-white shadow-lg backdrop-blur-sm hover:bg-black/60 sm:p-2.5" aria-label="Предыдущее фото">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="relative flex min-h-0 min-w-0 flex-1 touch-pan-y items-center justify-center" data-lightbox-stage>
                        <img id="newsLightboxImage" src="" alt="" class="max-h-[min(calc(100vh-12rem),52rem)] max-w-full select-none object-contain opacity-0 transition-opacity duration-400 ease-in-out md:max-h-[min(calc(100vh-14rem),52rem)]">
                        <span id="newsLightboxCounter" class="pointer-events-none absolute bottom-2 left-1/2 hidden -translate-x-1/2 rounded-full bg-black/55 px-2.5 py-1 text-xs text-white backdrop-blur-sm"></span>
                    </div>
                    <button type="button" data-lightbox-next class="lightbox-nav-next z-20 hidden max-md:hidden md:inline-flex shrink-0 self-center items-center justify-center rounded-full bg-black/45 p-2 text-white shadow-lg backdrop-blur-sm hover:bg-black/60 sm:p-2.5" aria-label="Следующее фото">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <aside
                    id="newsLightboxThumbs"
                    class="lightbox-thumbs hidden max-h-[7rem] w-full min-w-0 shrink-0 flex-row flex-nowrap items-center justify-start gap-2 overflow-x-auto overflow-y-hidden border-t border-white/10 py-2 [-ms-overflow-style:none] [scrollbar-width:thin] sm:max-h-[7.75rem] sm:justify-center sm:gap-3 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-white/25"
                    aria-label="Миниатюры галереи"
                ></aside>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function initNewsCarousel(root) {
                if (!root || root.getAttribute('data-carousel-ready') === '1') return;
                var track = root.querySelector('[data-carousel-track]');
                if (!track) return;
                var slides = Array.from(track.querySelectorAll('[data-carousel-slide]'));
                var count = slides.length;
                if (count <= 1) {
                    root.setAttribute('data-carousel-count', String(count));
                    root.setAttribute('data-carousel-index', '0');
                    root.setAttribute('data-carousel-ready', '1');
                    return;
                }

                var clone = slides[0].cloneNode(true);
                clone.setAttribute('data-carousel-slide-clone', '1');
                track.appendChild(clone);

                var total = count + 1;
                track.style.width = (total * 100) + '%';
                Array.from(track.children).forEach(function (slide) {
                    slide.style.width = (100 / total) + '%';
                    slide.classList.add('shrink-0');
                });

                root.setAttribute('data-carousel-count', String(count));
                root.setAttribute('data-carousel-index', '0');
                root.setAttribute('data-carousel-ready', '1');
                updateNewsCarouselUi(root, 0);
            }

            function setNewsCarouselTransform(root, virtualIndex, animate) {
                var track = root.querySelector('[data-carousel-track]');
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (!track || count <= 1) return;
                var total = count + 1;
                if (animate) {
                    track.style.removeProperty('transition');
                    void track.offsetHeight;
                    track.style.transform = 'translateX(' + (-(virtualIndex * 100) / total) + '%)';
                    return;
                }
                track.style.transition = 'none';
                track.style.transform = 'translateX(' + (-(virtualIndex * 100) / total) + '%)';
                void track.offsetHeight;
                track.style.removeProperty('transition');
            }

            function updateNewsCarouselUi(root, realIndex) {
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (count <= 0) return;
                root.setAttribute('data-carousel-index', String(realIndex));
                root.querySelectorAll('[data-carousel-dot]').forEach(function (dot, i) {
                    dot.classList.toggle('bg-blue-600', i === realIndex);
                    dot.classList.toggle('bg-gray-400/80', i !== realIndex);
                    dot.setAttribute('aria-current', i === realIndex ? 'true' : 'false');
                });
                var counter = root.querySelector('[data-carousel-counter]');
                if (counter) counter.textContent = (realIndex + 1) + ' / ' + count;
            }

            function newsCarouselGo(root, targetIndex) {
                initNewsCarousel(root);
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (count <= 1) return;
                var realIndex = ((targetIndex % count) + count) % count;
                setNewsCarouselTransform(root, realIndex, true);
                updateNewsCarouselUi(root, realIndex);
            }

            // Always moves forward visually, including last -> first.
            function newsCarouselNext(root) {
                initNewsCarousel(root);
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (count <= 1) return;
                if (root.getAttribute('data-carousel-busy') === '1') return;

                var current = parseInt(root.getAttribute('data-carousel-index') || '0', 10);
                if (current < count - 1) {
                    newsCarouselGo(root, current + 1);
                    return;
                }

                root.setAttribute('data-carousel-busy', '1');
                var track = root.querySelector('[data-carousel-track]');
                if (!track) {
                    root.setAttribute('data-carousel-busy', '0');
                    return;
                }
                setNewsCarouselTransform(root, count, true);
                updateNewsCarouselUi(root, 0);

                var done = false;
                function finishWrap() {
                    if (done) return;
                    done = true;
                    track.removeEventListener('transitionend', onEnd);
                    setNewsCarouselTransform(root, 0, false);
                    root.setAttribute('data-carousel-index', '0');
                    root.setAttribute('data-carousel-busy', '0');
                }
                function onEnd(e) {
                    if (e.target !== track || e.propertyName !== 'transform') return;
                    finishWrap();
                }
                track.addEventListener('transitionend', onEnd);
                setTimeout(finishWrap, 850);
            }

            function clampNewsCarouselTxPx(root, virtualStart, dxFromStart) {
                var track = root.querySelector('[data-carousel-track]');
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (!track || count <= 1) return 0;
                var total = count + 1;
                var slideW = track.offsetWidth / total;
                var rawTx = -virtualStart * slideW + dxFromStart;
                var minTx = virtualStart < count - 1 ? -(virtualStart + 1) * slideW : -count * slideW;
                var maxTx = virtualStart > 0 ? -(virtualStart - 1) * slideW : 0;
                return Math.max(minTx, Math.min(maxTx, rawTx));
            }

            function setNewsCarouselTxPx(root, txPx, animate) {
                var track = root.querySelector('[data-carousel-track]');
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (!track || count <= 1) return;
                var tw = track.offsetWidth;
                if (!tw) return;
                if (!animate) {
                    track.style.transition = 'none';
                    track.style.transform = 'translateX(' + (txPx / tw * 100) + '%)';
                    return;
                }
                track.style.removeProperty('transition');
                track.style.transform = 'translateX(' + (txPx / tw * 100) + '%)';
            }

            function snapNewsCarouselFromTx(root, txPx, virtualStart) {
                initNewsCarousel(root);
                var track = root.querySelector('[data-carousel-track]');
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (!track || count <= 1) return;
                if (root.getAttribute('data-carousel-busy') === '1') return;
                var total = count + 1;
                var slideW = track.offsetWidth / total;
                if (!slideW) return;
                var pos = -txPx / slideW;
                var targetVirtual = Math.round(Math.max(0, Math.min(count, pos)));
                if (targetVirtual === virtualStart) {
                    setNewsCarouselTransform(root, virtualStart, true);
                    updateNewsCarouselUi(root, virtualStart);
                    return;
                }
                if (targetVirtual === count) {
                    newsCarouselNext(root);
                    return;
                }
                newsCarouselGo(root, targetVirtual);
            }

            document.addEventListener('click', function (e) {
                var prevBtn = e.target.closest('[data-carousel-prev]');
                var nextBtn = e.target.closest('[data-carousel-next]');
                var dot = e.target.closest('[data-carousel-dot]');
                if (!prevBtn && !nextBtn && !dot) return;

                var root = (prevBtn || nextBtn || dot).closest('[data-news-carousel]');
                if (!root) return;
                e.preventDefault();
                e.stopPropagation();

                initNewsCarousel(root);
                var count = parseInt(root.getAttribute('data-carousel-count') || '0', 10);
                if (count <= 1) return;

                if (nextBtn) newsCarouselNext(root);
                else if (prevBtn) {
                    var cur = parseInt(root.getAttribute('data-carousel-index') || '0', 10);
                    newsCarouselGo(root, cur - 1);
                } else {
                    newsCarouselGo(root, parseInt(dot.getAttribute('data-carousel-index') || '0', 10));
                }
            });

            document.querySelectorAll('[data-news-carousel]').forEach(function (carouselRoot) {
                var swipeTid = null;
                var swipeSx = 0;
                var swipeSy = 0;
                var swipeActive = false;
                var swipeLockedHorizontal = false;
                var swipeVirtualStart = 0;
                var swipeLastDx = 0;
                var swipeLastDy = 0;

                function endSwipeSuppress() {
                    carouselRoot.removeAttribute('data-carousel-swipe-suppress');
                }

                carouselRoot.addEventListener(
                    'touchstart',
                    function (e) {
                        initNewsCarousel(carouselRoot);
                        var cnt = parseInt(carouselRoot.getAttribute('data-carousel-count') || '0', 10);
                        if (cnt <= 1 || !e.touches || e.touches.length !== 1) return;
                        if (carouselRoot.getAttribute('data-guest-photo-pinch-brake') === '1') return;
                        if (e.target && e.target.closest && e.target.closest('img[data-photo-pinch-active="1"]')) return;
                        if (carouselRoot.getAttribute('data-carousel-busy') === '1') return;
                        swipeTid = e.touches[0].identifier;
                        swipeSx = e.touches[0].clientX;
                        swipeSy = e.touches[0].clientY;
                        swipeLastDx = 0;
                        swipeLastDy = 0;
                        swipeActive = true;
                        swipeLockedHorizontal = false;
                        swipeVirtualStart = parseInt(carouselRoot.getAttribute('data-carousel-index') || '0', 10);
                        endSwipeSuppress();
                    },
                    { passive: true }
                );
                carouselRoot.addEventListener(
                    'touchmove',
                    function (e) {
                        if (!swipeActive || swipeTid === null || !e.touches || e.touches.length !== 1) return;
                        if (carouselRoot.getAttribute('data-guest-photo-pinch-brake') === '1') {
                            swipeActive = false;
                            swipeTid = null;
                            swipeLockedHorizontal = false;
                            return;
                        }
                        var tch = null;
                        for (var i = 0; i < e.touches.length; i++) {
                            if (e.touches[i].identifier === swipeTid) {
                                tch = e.touches[i];
                                break;
                            }
                        }
                        if (!tch) return;
                        var dx = tch.clientX - swipeSx;
                        var dy = tch.clientY - swipeSy;
                        swipeLastDx = dx;
                        swipeLastDy = dy;
                        if (!swipeLockedHorizontal) {
                            if (Math.abs(dy) > Math.abs(dx) * 1.18 && Math.abs(dy) > 16) {
                                swipeActive = false;
                                swipeTid = null;
                                return;
                            }
                            if (Math.abs(dx) > Math.abs(dy) * 1.06 && Math.abs(dx) > 12) {
                                swipeLockedHorizontal = true;
                                carouselRoot.setAttribute('data-carousel-swipe-suppress', '1');
                            } else {
                                return;
                            }
                        }
                        var cnt = parseInt(carouselRoot.getAttribute('data-carousel-count') || '0', 10);
                        if (cnt <= 1) return;
                        e.preventDefault();
                        var txPx = clampNewsCarouselTxPx(carouselRoot, swipeVirtualStart, dx);
                        setNewsCarouselTxPx(carouselRoot, txPx, false);
                    },
                    { passive: false }
                );
                carouselRoot.addEventListener(
                    'touchcancel',
                    function () {
                        if (swipeLockedHorizontal) {
                            var txPx = clampNewsCarouselTxPx(carouselRoot, swipeVirtualStart, swipeLastDx);
                            snapNewsCarouselFromTx(carouselRoot, txPx, swipeVirtualStart);
                        }
                        swipeActive = false;
                        swipeTid = null;
                        swipeLockedHorizontal = false;
                        setTimeout(endSwipeSuppress, 120);
                    },
                    { passive: true }
                );
                carouselRoot.addEventListener(
                    'touchend',
                    function (e) {
                        if (!swipeActive || swipeTid === null) return;
                        var te = null;
                        for (var ti = 0; ti < e.changedTouches.length; ti++) {
                            if (e.changedTouches[ti].identifier === swipeTid) {
                                te = e.changedTouches[ti];
                                break;
                            }
                        }
                        swipeActive = false;
                        swipeTid = null;
                        if (!te) return;
                        initNewsCarousel(carouselRoot);
                        var cnt = parseInt(carouselRoot.getAttribute('data-carousel-count') || '0', 10);
                        if (cnt <= 1) {
                            endSwipeSuppress();
                            return;
                        }
                        if (carouselRoot.getAttribute('data-carousel-busy') === '1') {
                            endSwipeSuppress();
                            return;
                        }
                        if (!swipeLockedHorizontal) {
                            var dx = te.clientX - swipeSx;
                            var dy = te.clientY - swipeSy;
                            if (Math.abs(dx) < 46 || Math.abs(dx) <= Math.abs(dy) * 1.08) {
                                endSwipeSuppress();
                                return;
                            }
                            e.preventDefault();
                            carouselRoot.setAttribute('data-carousel-swipe-suppress', '1');
                            if (dx < 0) {
                                newsCarouselNext(carouselRoot);
                            } else {
                                var cur = parseInt(carouselRoot.getAttribute('data-carousel-index') || '0', 10);
                                newsCarouselGo(carouselRoot, cur - 1);
                            }
                            setTimeout(endSwipeSuppress, 380);
                            return;
                        }
                        e.preventDefault();
                        var finalDx = te.clientX - swipeSx;
                        var txPx = clampNewsCarouselTxPx(carouselRoot, swipeVirtualStart, finalDx);
                        snapNewsCarouselFromTx(carouselRoot, txPx, swipeVirtualStart);
                        setTimeout(endSwipeSuppress, 380);
                    },
                    { passive: false }
                );
            });

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-news-carousel]').forEach(initNewsCarousel);

                var modal = document.getElementById('newsImageLightbox');
                var img = document.getElementById('newsLightboxImage');
                var counter = document.getElementById('newsLightboxCounter');
                var thumbStrip = document.getElementById('newsLightboxThumbs');
                var stage = modal ? modal.querySelector('[data-lightbox-stage]') : null;
                if (!modal || !img || !counter) return;
                var backdrop = modal.querySelector('[data-lightbox-backdrop]');
                var closeBtn = modal.querySelector('[data-lightbox-close]');
                var prevBtn = modal.querySelector('[data-lightbox-prev]');
                var nextBtn = modal.querySelector('[data-lightbox-next]');
                var gallery = [];
                var index = 0;
                var lightboxSwapBusy = false;

                function syncLightboxThumbnails() {
                    if (!thumbStrip) return;
                    thumbStrip.querySelectorAll('[data-thumb-index]').forEach(function (btn) {
                        var i = parseInt(btn.getAttribute('data-thumb-index'), 10);
                        var on = i === index;
                        btn.classList.toggle('ring-2', on);
                        btn.classList.toggle('ring-blue-400', on);
                        btn.classList.toggle('ring-offset-2', on);
                        btn.classList.toggle('ring-offset-transparent', on);
                        btn.setAttribute('aria-current', on ? 'true' : 'false');
                    });
                    var active = thumbStrip.querySelector('[data-thumb-index="' + index + '"]');
                    if (active && typeof active.scrollIntoView === 'function') {
                        active.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
                    }
                }

                function renderLightboxThumbnails() {
                    if (!thumbStrip) return;
                    thumbStrip.innerHTML = '';
                    if (gallery.length <= 1) {
                        thumbStrip.classList.add('hidden');
                        thumbStrip.classList.remove('flex');
                        return;
                    }
                    thumbStrip.classList.remove('hidden');
                    thumbStrip.classList.add('flex');
                    gallery.forEach(function (url, i) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.setAttribute('data-thumb-index', String(i));
                        btn.setAttribute('aria-label', 'Показать фото ' + (i + 1));
                        btn.className =
                            'group relative h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-black/40 ring-2 ring-transparent ring-offset-1 ring-offset-black/50 transition hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 sm:h-[5.25rem] sm:w-[5.25rem]';
                        var th = document.createElement('img');
                        th.src = url;
                        th.alt = '';
                        th.className = 'h-full w-full object-cover';
                        th.loading = 'lazy';
                        btn.appendChild(th);
                        thumbStrip.appendChild(btn);
                    });
                    syncLightboxThumbnails();
                }

                function updateLightboxChrome() {
                    counter.textContent = (index + 1) + ' / ' + gallery.length;
                    var many = gallery.length > 1;
                    prevBtn.classList.toggle('hidden', !many);
                    nextBtn.classList.toggle('hidden', !many);
                    counter.classList.toggle('hidden', !many);
                    // `hidden md:inline-flex` — на md display может переопределить `hidden`,
                    // поэтому дополнительно принудительно скрываем кнопки, если фото одно.
                    if (prevBtn) prevBtn.style.display = many ? '' : 'none';
                    if (nextBtn) nextBtn.style.display = many ? '' : 'none';
                    syncLightboxThumbnails();
                }

                function revealLightboxImage() {
                    img.onload = null;
                    img.onerror = null;
                    requestAnimationFrame(function () {
                        img.classList.remove('opacity-0');
                        lightboxSwapBusy = false;
                    });
                }

                function loadLightboxUrl(url) {
                    img.onload = function () {
                        revealLightboxImage();
                    };
                    img.onerror = function () {
                        revealLightboxImage();
                    };
                    img.src = url;
                    if (img.complete && img.naturalWidth > 0) {
                        revealLightboxImage();
                    }
                }

                /** opts.skipFadeOut — при первом открытии без затухания предыдущего кадра */
                function apply(opts) {
                    opts = opts || {};
                    if (typeof window.resetGuestPhotoPinch === 'function') window.resetGuestPhotoPinch(img);
                    if (!gallery.length) return;
                    index = ((index % gallery.length) + gallery.length) % gallery.length;
                    var url = gallery[index];
                    updateLightboxChrome();

                    if (opts.skipFadeOut || !img.getAttribute('src')) {
                        img.classList.add('opacity-0');
                        loadLightboxUrl(url);
                        return;
                    }

                    if (lightboxSwapBusy) return;
                    lightboxSwapBusy = true;

                    var settled = false;
                    function afterFadeOut() {
                        if (settled) return;
                        settled = true;
                        img.removeEventListener('transitionend', onOpacityEnd);
                        clearTimeout(fallbackTimer);
                        img.classList.add('opacity-0');
                        loadLightboxUrl(url);
                    }
                    function onOpacityEnd(e) {
                        if (e.propertyName !== 'opacity') return;
                        afterFadeOut();
                    }
                    var fallbackTimer = setTimeout(afterFadeOut, 500);
                    img.addEventListener('transitionend', onOpacityEnd);
                    img.classList.add('opacity-0');
                }

                function open(trigger) {
                    var src = trigger.getAttribute('data-lightbox-src') || '';
                    var alt = trigger.getAttribute('data-lightbox-alt') || '';
                    var galleryRaw = trigger.getAttribute('data-lightbox-gallery');
                    var idxRaw = trigger.getAttribute('data-lightbox-index');
                    var arr = [];
                    if (galleryRaw) {
                        try { arr = JSON.parse(galleryRaw); } catch (_) { arr = []; }
                    }
                    if (!Array.isArray(arr) || !arr.length) arr = [src];
                    gallery = arr.filter(Boolean);
                    if (!gallery.length) return;
                    index = idxRaw == null || idxRaw === '' ? 0 : parseInt(idxRaw, 10);
                    if (isNaN(index)) index = 0;
                    img.alt = alt;
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    renderLightboxThumbnails();
                    apply({ skipFadeOut: true });
                }

                function close() {
                    if (typeof window.resetGuestPhotoPinch === 'function') window.resetGuestPhotoPinch(img);
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    img.src = '';
                    img.alt = '';
                    gallery = [];
                    index = 0;
                    lightboxSwapBusy = false;
                    if (thumbStrip) {
                        thumbStrip.innerHTML = '';
                        thumbStrip.classList.add('hidden');
                    }
                }

                if (thumbStrip) {
                    thumbStrip.addEventListener('click', function (e) {
                        var btn = e.target.closest('[data-thumb-index]');
                        if (!btn || lightboxSwapBusy) return;
                        var i = parseInt(btn.getAttribute('data-thumb-index'), 10);
                        if (isNaN(i) || i === index) return;
                        index = i;
                        apply({});
                    });
                }

                var swipeTracking = false;
                var swipeTouchId = null;
                var swipeStartX = 0;
                var swipeStartY = 0;
                var swipeIgnoreGesture = false;

                function findTouchById(e, id) {
                    var list = e.changedTouches || [];
                    for (var i = 0; i < list.length; i++) {
                        if (list[i].identifier === id) return list[i];
                    }
                    return null;
                }

                if (stage) {
                    stage.addEventListener(
                        'touchstart',
                        function (e) {
                            if (modal.classList.contains('hidden') || gallery.length <= 1) return;
                            if (e.touches.length !== 1) return;
                            var lbImg = document.getElementById('newsLightboxImage');
                            if (lbImg && lbImg.getAttribute('data-photo-pinch-active') === '1') return;
                            var t = e.touches[0];
                            swipeTracking = true;
                            swipeIgnoreGesture = false;
                            swipeTouchId = t.identifier;
                            swipeStartX = t.clientX;
                            swipeStartY = t.clientY;
                        },
                        { passive: true }
                    );
                    stage.addEventListener(
                        'touchmove',
                        function (e) {
                            if (!swipeTracking || swipeTouchId === null) return;
                            var t = null;
                            for (var j = 0; j < e.touches.length; j++) {
                                if (e.touches[j].identifier === swipeTouchId) {
                                    t = e.touches[j];
                                    break;
                                }
                            }
                            if (!t) return;
                            var mx = t.clientX - swipeStartX;
                            var my = t.clientY - swipeStartY;
                            if (Math.abs(my) > 55 && Math.abs(my) > Math.abs(mx) * 1.25) {
                                swipeIgnoreGesture = true;
                            }
                        },
                        { passive: true }
                    );
                    stage.addEventListener(
                        'touchend',
                        function (e) {
                            if (!swipeTracking || swipeTouchId === null) return;
                            var t = findTouchById(e, swipeTouchId);
                            swipeTracking = false;
                            var tid = swipeTouchId;
                            swipeTouchId = null;
                            if (!t || swipeIgnoreGesture) return;
                            if (modal.classList.contains('hidden') || gallery.length <= 1) return;
                            if (lightboxSwapBusy) return;

                            var dx = t.clientX - swipeStartX;
                            var dy = t.clientY - swipeStartY;
                            var minPx = 42;
                            if (Math.abs(dx) < minPx) return;
                            if (Math.abs(dx) <= Math.abs(dy) * 1.05) return;

                            if (dx < 0) {
                                index += 1;
                            } else {
                                index -= 1;
                            }
                            apply({});
                        },
                        { passive: true }
                    );
                    stage.addEventListener(
                        'touchcancel',
                        function () {
                            swipeTracking = false;
                            swipeTouchId = null;
                            swipeIgnoreGesture = false;
                        },
                        { passive: true }
                    );
                }

                document.addEventListener('click', function (e) {
                    var trigger = e.target.closest('[data-news-lightbox]');
                    if (!trigger) return;
                    var swipeRoot = trigger.closest('[data-news-carousel]');
                    if (swipeRoot && swipeRoot.getAttribute('data-carousel-swipe-suppress') === '1') return;
                    e.preventDefault();
                    open(trigger);
                });
                if (backdrop) backdrop.addEventListener('click', close);
                if (closeBtn) closeBtn.addEventListener('click', close);
                if (prevBtn) prevBtn.addEventListener('click', function () {
                    if (gallery.length <= 1) return;
                    index -= 1;
                    apply({});
                });
                if (nextBtn) nextBtn.addEventListener('click', function () {
                    if (gallery.length <= 1) return;
                    index += 1;
                    apply({});
                });
                document.addEventListener('keydown', function (e) {
                    if (modal.classList.contains('hidden')) return;
                    if (e.key === 'Escape') close();
                    else if (e.key === 'ArrowLeft') {
                        if (gallery.length <= 1) return;
                        e.preventDefault();
                        index -= 1;
                        apply({});
                    }
                    else if (e.key === 'ArrowRight') {
                        if (gallery.length <= 1) return;
                        e.preventDefault();
                        index += 1;
                        apply({});
                    }
                });
            });
        </script>
    @endpush
@endonce
