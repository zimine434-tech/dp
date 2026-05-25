{{-- Подключает TinyMCE только если на странице есть textarea.wysiwyg --}}
<style>
    .wysiwyg-editor-wrap {
        position: relative;
        width: 100%;
        max-width: 100%;
    }
    .wysiwyg-editor-wrap .tox.tox-tinymce {
        width: 100% !important;
    }
    .wysiwyg-corner-grip {
        position: absolute;
        right: 6px;
        bottom: 6px;
        z-index: 30;
        width: 16px;
        height: 16px;
        cursor: ns-resize;
        border-radius: 3px;
        border: 1px solid #64748b;
        background: linear-gradient(
            -45deg,
            transparent 0 35%,
            #94a3b8 35% 40%,
            transparent 40% 55%,
            #94a3b8 55% 60%,
            transparent 60% 75%,
            #94a3b8 75% 80%,
            transparent 80%
        );
        background-color: #e2e8f0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.15);
        touch-action: none;
        user-select: none;
    }
    .wysiwyg-corner-grip:hover {
        border-color: #2563eb;
        background-color: #dbeafe;
    }
</style>
<script>
(function () {
    var minH = 120;
    var maxH = 720;

    function applyHeight(editor, px) {
        px = Math.max(minH, Math.min(maxH, Math.round(px)));
        var container = editor.getContainer();
        if (!container) return;

        container.style.height = px + 'px';
        container.style.minHeight = px + 'px';

        var header = container.querySelector('.tox-editor-header');
        var headerH = header ? header.offsetHeight : 0;
        var editArea = container.querySelector('.tox-edit-area');
        if (editArea) {
            var bodyH = Math.max(80, px - headerH);
            editArea.style.height = bodyH + 'px';
            editArea.style.minHeight = bodyH + 'px';
        }

        editor.fire('ResizeEditor');
    }

    function syncTinyMceSize(editor) {
        var container = editor.getContainer();
        if (!container) return;
        var w = container.offsetWidth || container.parentElement?.clientWidth || 600;
        var h = container.offsetHeight;
        if (editor.theme && typeof editor.theme.resizeTo === 'function') {
            editor.theme.resizeTo(w, h);
        }
    }

    function heightFromPointer(container, clientY) {
        return clientY - container.getBoundingClientRect().top;
    }

    function attachCornerGrip(editor) {
        var container = editor.getContainer();
        if (!container || container.closest('.wysiwyg-editor-wrap')) return;

        var wrap = document.createElement('div');
        wrap.className = 'wysiwyg-editor-wrap';

        var parent = container.parentNode;
        if (!parent) return;

        parent.insertBefore(wrap, container);
        wrap.appendChild(container);

        var grip = document.createElement('div');
        grip.className = 'wysiwyg-corner-grip';
        grip.setAttribute('role', 'separator');
        grip.setAttribute('aria-label', 'Потяните вверх или вниз, чтобы изменить высоту');
        grip.title = 'Изменить высоту';
        wrap.appendChild(grip);

        var dragging = false;
        var activePointerId = null;
        var rafId = 0;
        var pendingY = 0;

        function flushHeight() {
            rafId = 0;
            if (!dragging) return;
            applyHeight(editor, heightFromPointer(container, pendingY));
        }

        function queueHeight(clientY) {
            pendingY = clientY;
            if (!rafId) {
                rafId = requestAnimationFrame(flushHeight);
            }
        }

        function onPointerMove(e) {
            if (!dragging || e.pointerId !== activePointerId) return;
            e.preventDefault();
            queueHeight(e.clientY);
        }

        function onPointerEnd(e) {
            if (!dragging || (e.pointerId !== activePointerId && e.type !== 'lostpointercapture')) return;
            dragging = false;
            activePointerId = null;
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = 0;
            }
            applyHeight(editor, heightFromPointer(container, pendingY || e.clientY));
            syncTinyMceSize(editor);
            try {
                if (grip.hasPointerCapture(e.pointerId)) {
                    grip.releasePointerCapture(e.pointerId);
                }
            } catch (err) { /* already released */ }
            grip.removeEventListener('pointermove', onPointerMove);
            grip.removeEventListener('pointerup', onPointerEnd);
            grip.removeEventListener('pointercancel', onPointerEnd);
            grip.removeEventListener('lostpointercapture', onPointerEnd);
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        }

        grip.addEventListener('pointerdown', function (e) {
            if (e.button !== 0) return;
            e.preventDefault();
            e.stopPropagation();
            dragging = true;
            activePointerId = e.pointerId;
            pendingY = e.clientY;
            applyHeight(editor, heightFromPointer(container, e.clientY));
            grip.setPointerCapture(e.pointerId);
            document.body.style.cursor = 'ns-resize';
            document.body.style.userSelect = 'none';
            grip.addEventListener('pointermove', onPointerMove);
            grip.addEventListener('pointerup', onPointerEnd);
            grip.addEventListener('pointercancel', onPointerEnd);
            grip.addEventListener('lostpointercapture', onPointerEnd);
        });
    }

    function initTinyMCE() {
        if (!window.tinymce || typeof tinymce.init !== 'function') return;
        tinymce.init({
            selector: 'textarea.wysiwyg',
            height: 280,
            min_height: minH,
            resize: false,
            menubar: false,
            statusbar: false,
            branding: false,
            promotion: false,
            license_key: 'gpl',
            plugins: 'lists link',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link removeformat',
            content_style: 'body { font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.5; margin: 8px; }',
            convert_urls: false,
            setup: function (editor) {
                editor.on('init', function () {
                    attachCornerGrip(editor);
                });
            },
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('textarea.wysiwyg')) return;
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/tinymce@7.4.1/tinymce.min.js';
        script.referrerPolicy = 'origin';
        script.onload = initTinyMCE;
        document.head.appendChild(script);
    });
})();
</script>
