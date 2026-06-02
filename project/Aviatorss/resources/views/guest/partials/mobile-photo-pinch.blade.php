{{-- Pinch-to-zoom и одним пальцем смещение при увеличении — только max-md --}}
<script>
(function () {
    var mq = window.matchMedia('(max-width: 767px)');
    function mobile() {
        return mq.matches;
    }

    var pinchState = null;
    var panState = null;
    /** Пока жест не завершён полностью — какое фото масштабировали (pinch без pan тоже) */
    var gestureTargetImg = null;

    function getPinch(img) {
        if (!img.__guestPinch) {
            img.__guestPinch = { scale: 1, tx: 0, ty: 0 };
        }
        return img.__guestPinch;
    }

    function brakeCarouselRoots(img) {
        var r = img.closest('[data-news-carousel],[data-home-carousel]');
        if (r) {
            r.setAttribute('data-guest-photo-pinch-brake', '1');
        }
    }

    function clearBrakes() {
        document.querySelectorAll('[data-guest-photo-pinch-brake]').forEach(function (el) {
            el.removeAttribute('data-guest-photo-pinch-brake');
        });
    }

    function clampPan(img) {
        var p = getPinch(img);
        var pad = img.parentElement;
        if (!pad || p.scale <= 1.02) {
            p.tx = 0;
            p.ty = 0;
            return;
        }
        var cw = pad.clientWidth;
        var ch = pad.clientHeight;
        var bw = img.offsetWidth * p.scale;
        var bh = img.offsetHeight * p.scale;
        var maxX = Math.max(0, (bw - cw) / 2);
        var maxY = Math.max(0, (bh - ch) / 2);
        p.tx = Math.max(-maxX, Math.min(maxX, p.tx));
        p.ty = Math.max(-maxY, Math.min(maxY, p.ty));
    }

    function applyImg(img) {
        var p = getPinch(img);
        if (p.scale <= 1.02) {
            p.scale = 1;
            p.tx = 0;
            p.ty = 0;
            img.style.transform = '';
            img.removeAttribute('data-photo-pinch-active');
            clearBrakes();
            return;
        }
        img.style.transformOrigin = 'center center';
        img.style.transform =
            'translate3d(' + p.tx + 'px,' + p.ty + 'px,0) scale(' + p.scale + ')';
        img.setAttribute('data-photo-pinch-active', '1');
    }

    function dist(a, b) {
        return Math.hypot(b.clientX - a.clientX, b.clientY - a.clientY);
    }

    function isPhotoImg(node) {
        if (!node || !node.closest) return null;
        var img = node.nodeName === 'IMG' ? node : node.closest('main img');
        if (!img || img.nodeName !== 'IMG') return null;
        if (!img.closest('main')) return null;
        if (!img.src || img.src.indexOf('data:image/svg+xml') === 0) return null;
        return img;
    }

    function findPinchImg(t0, t1) {
        var el0 = document.elementFromPoint(t0.clientX, t0.clientY);
        var el1 = document.elementFromPoint(t1.clientX, t1.clientY);
        var img0 = el0 && el0.closest ? el0.closest('main img') : null;
        var img1 = el1 && el1.closest ? el1.closest('main img') : null;
        if (img0 && img0 === img1) return img0;
        if (img0 && img1 && img0.parentElement === img1.parentElement) return img0;
        return img0 || img1 || null;
    }

    function resetGuestPhotoPinchInstant(img) {
        if (!img) return;
        delete img.__guestPinch;
        img.style.transition = '';
        img.style.transform = '';
        img.style.transformOrigin = '';
        img.removeAttribute('data-photo-pinch-active');
        clearBrakes();
    }

    /** Мгновенный сброс (лайтбокс, смена кадра) */
    window.resetGuestPhotoPinch = function (img) {
        resetGuestPhotoPinchInstant(img);
    };

    /** После жеста — плавный возврат к 1× и без смещения */
    function releaseGuestPhotoPinchAnimated(img) {
        if (!img) return;
        var p = img.__guestPinch;
        if (
            (!p || (p.scale <= 1.02 && Math.abs(p.tx) < 0.5 && Math.abs(p.ty) < 0.5)) &&
            img.getAttribute('data-photo-pinch-active') !== '1'
        ) {
            resetGuestPhotoPinchInstant(img);
            return;
        }
        img.style.transition = 'transform 0.32s ease-out';
        img.style.transformOrigin = 'center center';
        requestAnimationFrame(function () {
            img.style.transform = 'translate3d(0,0,0) scale(1)';
        });
        delete img.__guestPinch;
        img.removeAttribute('data-photo-pinch-active');
        clearBrakes();
        var fallbackTimer = setTimeout(function () {
            img.removeEventListener('transitionend', onTe);
            resetGuestPhotoPinchInstant(img);
        }, 420);
        function onTe(ev) {
            if (ev.propertyName !== 'transform') return;
            clearTimeout(fallbackTimer);
            img.removeEventListener('transitionend', onTe);
            resetGuestPhotoPinchInstant(img);
        }
        img.addEventListener('transitionend', onTe);
    }

    document.addEventListener(
        'touchstart',
        function (e) {
            if (!mobile()) return;
            if (e.touches.length === 2) {
                var img = findPinchImg(e.touches[0], e.touches[1]);
                if (!img) return;
                gestureTargetImg = img;
                brakeCarouselRoots(img);
                var p = getPinch(img);
                pinchState = {
                    img: img,
                    d0: dist(e.touches[0], e.touches[1]),
                    s0: p.scale,
                    lastMidX: (e.touches[0].clientX + e.touches[1].clientX) / 2,
                    lastMidY: (e.touches[0].clientY + e.touches[1].clientY) / 2
                };
                panState = null;
                return;
            }
            if (e.touches.length === 1) {
                var imgOne = isPhotoImg(e.target);
                if (imgOne && getPinch(imgOne).scale > 1.02) {
                    gestureTargetImg = imgOne;
                    brakeCarouselRoots(imgOne);
                    var p2 = getPinch(imgOne);
                    panState = {
                        img: imgOne,
                        sx: e.touches[0].clientX,
                        sy: e.touches[0].clientY,
                        tx0: p2.tx,
                        ty0: p2.ty
                    };
                }
            }
        },
        true
    );

    document.addEventListener(
        'touchmove',
        function (e) {
            if (!mobile()) return;
            if (pinchState && e.touches.length >= 2) {
                var ps = pinchState;
                var i0 = e.touches[0];
                var i1 = e.touches[1];
                if (!i0 || !i1) return;
                var d = dist(i0, i1);
                var mx = (i0.clientX + i1.clientX) / 2;
                var my = (i0.clientY + i1.clientY) / 2;
                var p = getPinch(ps.img);

                p.tx += mx - ps.lastMidX;
                p.ty += my - ps.lastMidY;
                ps.lastMidX = mx;
                ps.lastMidY = my;

                var oldScale = p.scale;
                var newScale = Math.min(4, Math.max(1, ps.s0 * (d / Math.max(ps.d0, 8))));
                if (Math.abs(newScale - oldScale) > 1e-6) {
                    var rect = ps.img.getBoundingClientRect();
                    var icx = rect.left + rect.width / 2;
                    var icy = rect.top + rect.height / 2;
                    var r = newScale / oldScale;
                    p.tx += (mx - icx) * (1 - r);
                    p.ty += (my - icy) * (1 - r);
                }
                p.scale = newScale;

                clampPan(ps.img);
                applyImg(ps.img);
                e.preventDefault();
                return;
            }
            if (panState && e.touches.length === 1) {
                var pv = panState;
                var t = e.touches[0];
                var p3 = getPinch(pv.img);
                p3.tx = pv.tx0 + (t.clientX - pv.sx);
                p3.ty = pv.ty0 + (t.clientY - pv.sy);
                clampPan(pv.img);
                applyImg(pv.img);
                e.preventDefault();
            }
        },
        { passive: false, capture: true }
    );

    document.addEventListener(
        'touchend',
        function (e) {
            if (!mobile()) return;
            if (pinchState && e.touches.length < 2) {
                pinchState = null;
            }
            if (e.touches.length === 1) {
                var tr = e.touches[0];
                var el = document.elementFromPoint(tr.clientX, tr.clientY);
                var imgLeft = isPhotoImg(el);
                if (imgLeft && getPinch(imgLeft).scale > 1.02) {
                    var pr = getPinch(imgLeft);
                    panState = {
                        img: imgLeft,
                        sx: tr.clientX,
                        sy: tr.clientY,
                        tx0: pr.tx,
                        ty0: pr.ty
                    };
                    brakeCarouselRoots(imgLeft);
                }
            }
            if (!e.touches.length) {
                var imgReset =
                    (panState && panState.img) ||
                    gestureTargetImg ||
                    (e.changedTouches &&
                        e.changedTouches.length &&
                        isPhotoImg(
                            document.elementFromPoint(
                                e.changedTouches[e.changedTouches.length - 1].clientX,
                                e.changedTouches[e.changedTouches.length - 1].clientY
                            )
                        ));
                panState = null;
                pinchState = null;
                gestureTargetImg = null;
                if (imgReset) {
                    releaseGuestPhotoPinchAnimated(imgReset);
                } else {
                    document.querySelectorAll('main img[data-photo-pinch-active="1"]').forEach(function (im) {
                        releaseGuestPhotoPinchAnimated(im);
                    });
                }
            }
        },
        true
    );

    document.addEventListener(
        'touchcancel',
        function () {
            if (!mobile()) return;
            pinchState = null;
            panState = null;
            var im =
                gestureTargetImg ||
                document.querySelector('main img[data-photo-pinch-active="1"]');
            gestureTargetImg = null;
            if (im) {
                releaseGuestPhotoPinchAnimated(im);
            }
        },
        true
    );
})();
</script>
