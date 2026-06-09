@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                initFilterComboboxes(document);
            });

            document.addEventListener('sport-combobox:sync', function () {
                initFilterComboboxes(document, true);
            });

            document.addEventListener('filter-combobox:sync', function () {
                initFilterComboboxes(document, true);
                // После AJAX-замены блока списка появляются новые combobox — их нужно инициализировать.
                initFilterComboboxes(document, false);
            });

            function initFilterComboboxes(scope, syncOnly) {
                (scope || document).querySelectorAll('[data-filter-combobox], [data-sport-combobox]').forEach(function (root) {
                    if (syncOnly) {
                        if (typeof root._syncFilterCombobox === 'function') {
                            root._syncFilterCombobox();
                        }
                        return;
                    }
                    if (root.dataset.comboboxInit === '1') return;
                    root.dataset.comboboxInit = '1';

                    var trigger = root.querySelector('[data-combobox-trigger]');
                    var panel = root.querySelector('[data-combobox-panel]');
                    var list = root.querySelector('[data-combobox-list]');
                    var hidden = root.querySelector('[data-combobox-hidden]');
                    var label = root.querySelector('[data-combobox-label]');
                    var chevron = root.querySelector('[data-combobox-chevron]');
                    if (!trigger || !panel || !list || !hidden || !label) return;

                    var isFilter = root.getAttribute('data-combobox-variant') === 'filter';

                    function open() {
                        panel.classList.remove('hidden');
                        trigger.setAttribute('aria-expanded', 'true');
                        if (chevron) chevron.classList.add('rotate-180');
                    }

                    function close() {
                        panel.classList.add('hidden');
                        trigger.setAttribute('aria-expanded', 'false');
                        if (chevron) chevron.classList.remove('rotate-180');
                    }

                    function toggle() {
                        if (panel.classList.contains('hidden')) open();
                        else close();
                    }

                    function applySelection(btn) {
                        var v = btn.getAttribute('data-value') || '';
                        hidden.value = v;
                        label.textContent = btn.textContent.trim();
                        list.querySelectorAll('[data-combobox-option]').forEach(function (b) {
                            b.classList.remove('bg-sky-100', 'font-semibold', 'bg-blue-50/80', 'font-medium');
                            var on = b === btn;
                            if (on) {
                                if (isFilter) {
                                    b.classList.add('bg-sky-100', 'font-semibold');
                                } else {
                                    b.classList.add('bg-blue-50/80', 'font-medium');
                                }
                                b.setAttribute('aria-selected', 'true');
                            } else {
                                b.setAttribute('aria-selected', 'false');
                            }
                        });
                    }

                    root._syncFilterCombobox = function () {
                        var v = hidden.value || '';
                        var match = null;
                        list.querySelectorAll('[data-combobox-option]').forEach(function (b) {
                            if ((b.getAttribute('data-value') || '') === v) match = b;
                        });
                        if (match) {
                            applySelection(match);
                        } else {
                            var emptyBtn = list.querySelector('[data-combobox-option][data-value=""]');
                            if (emptyBtn) applySelection(emptyBtn);
                        }
                    };
                    root._syncSportCombobox = root._syncFilterCombobox;

                    trigger.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        toggle();
                    });

                    panel.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });

                    list.querySelectorAll('[data-combobox-option]').forEach(function (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            applySelection(btn);
                            close();
                            hidden.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    });

                    document.addEventListener('click', function (e) {
                        if (!root.contains(e.target)) close();
                    });

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
                            close();
                            trigger.focus();
                        }
                    });
                });
            }

            window.initFilterComboboxes = initFilterComboboxes;

            window.renderPerPageComboboxHtml = function (inputId, currentValue) {
                var current = String(currentValue || '10');
                var values = [10, 25, 50, 100];
                var optionsHtml = values
                    .map(function (n) {
                        var v = String(n);
                        var selectedClass = v === current ? ' bg-sky-100 font-semibold' : '';
                        var aria = v === current ? 'true' : 'false';
                        return (
                            '<button type="button" role="option" data-combobox-option data-value="' +
                            v +
                            '" aria-selected="' +
                            aria +
                            '" class="flex w-full px-4 py-2.5 text-left text-sm text-gray-900 transition hover:bg-sky-50 focus:bg-sky-50 focus:outline-none' +
                            selectedClass +
                            '">' +
                            v +
                            '</button>'
                        );
                    })
                    .join('');

                var labelText = values.indexOf(parseInt(current, 10)) !== -1 ? current : '10';

                return (
                    '<div class="mr-auto flex items-center gap-2">' +
                    '<label for="' +
                    inputId +
                    '_combobox_trigger" class="text-xs font-medium uppercase tracking-wide text-gray-500">Показывать по</label>' +
                    '<div class="relative min-w-[5rem]" data-filter-combobox data-combobox-variant="filter">' +
                    '<input type="hidden" value="' +
                    current +
                    '" data-combobox-hidden autocomplete="off" id="' +
                    inputId +
                    '">' +
                    '<button type="button" id="' +
                    inputId +
                    '_combobox_trigger" data-combobox-trigger aria-haspopup="listbox" aria-expanded="false" class="flex h-10 w-full min-w-[5rem] items-center justify-between gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 text-left text-sm text-gray-900 outline-none transition hover:border-gray-300 focus:border-blue-500 focus:outline-none focus:ring-0">' +
                    '<span class="min-w-0 truncate" data-combobox-label>' +
                    labelText +
                    '</span>' +
                    '<svg data-combobox-chevron class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>' +
                    '</svg></button>' +
                    '<div data-combobox-panel class="absolute left-0 right-0 z-50 mt-1 hidden min-w-full" role="presentation">' +
                    '<div data-combobox-list class="overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5" role="listbox" aria-labelledby="' +
                    inputId +
                    '_combobox_trigger">' +
                    '<div class="max-h-52 overflow-y-auto overscroll-contain py-1 [scrollbar-width:thin] [scrollbar-color:#9ca3af_transparent] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400/80">' +
                    optionsHtml +
                    '</div></div></div></div></div>'
                );
            };
        </script>
    @endpush
@endonce
