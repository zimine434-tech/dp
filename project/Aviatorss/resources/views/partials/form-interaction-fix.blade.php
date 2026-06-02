{{-- Сброс залипшего user-select и overlay меню (не ломает TinyMCE) --}}
<style>
    body {
        user-select: auto !important;
        -webkit-user-select: auto !important;
    }

    body input,
    body textarea,
    body select {
        pointer-events: auto !important;
        user-select: auto !important;
        -webkit-user-select: auto !important;
    }

    #sidebarOverlay.hidden,
    #sidebarOverlay[aria-hidden="true"] {
        display: none !important;
        pointer-events: none !important;
    }
</style>
<script>
    (function () {
        function resetBodyInteraction() {
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            document.body.style.webkitUserSelect = '';
        }

        resetBodyInteraction();
        document.addEventListener('DOMContentLoaded', resetBodyInteraction);
        window.addEventListener('pageshow', resetBodyInteraction);
    })();
</script>
