@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                initSportComboboxes(document);
            });

            document.addEventListener('sport-combobox:sync', function () {
                document.querySelectorAll('[data-sport-combobox]').forEach(function (root) {
                    if (typeof root._syncSportCombobox === 'function') {
                        root._syncSportCombobox();
                    }
                });
            });

            function initSportComboboxes(scope) {
                (scope || document).querySelectorAll('[data-sport-combobox]').forEach(function (root) {
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

                    root._syncSportCombobox = function () {
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
        </script>
    @endpush
@endonce
