{{-- Rich-text: TinyMCE 6 (self-hosted CDN). Класс wysiwyg на textarea. --}}
<style>
    .wysiwyg-field .tox.tox-tinymce {
        width: 100% !important;
    }
    /* Невидимый слой TinyMCE не должен перехватывать клики по странице */
    .tox-tinymce-aux,
    .tox-silver-sink {
        pointer-events: none;
    }
    .tox-tinymce-aux *,
    .tox-silver-sink * {
        pointer-events: auto;
    }
</style>
<script>
(function () {
    function resetBodyInteraction() {
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
        document.body.style.webkitUserSelect = '';
    }

    function initTinyMCE() {
        if (!window.tinymce || typeof tinymce.init !== 'function') return;

        resetBodyInteraction();

        tinymce.init({
            selector: 'textarea.wysiwyg',
            height: 280,
            min_height: 120,
            menubar: false,
            statusbar: false,
            branding: false,
            plugins: 'lists link',
            toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link removeformat',
            content_style: 'body { font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; line-height: 1.5; margin: 8px; }',
            convert_urls: false,
            setup: function (editor) {
                editor.on('init', function () {
                    editor.focus();
                });
            },
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelector('textarea.wysiwyg')) return;

        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js';
        script.referrerPolicy = 'origin';
        script.onload = initTinyMCE;
        script.onerror = function () {
            document.querySelectorAll('textarea.wysiwyg').forEach(function (el) {
                el.classList.remove('wysiwyg');
            });
        };
        document.head.appendChild(script);
    });
})();
</script>
