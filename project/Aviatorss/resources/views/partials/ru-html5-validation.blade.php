<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindRuValidation(input) {
        if (input.dataset.ruValidationBound === '1') {
            return;
        }
        input.dataset.ruValidationBound = '1';

        const requiredMsg = input.dataset.msgRequired || 'Заполните это поле.';
        const minMsg = input.dataset.msgMin || 'Указанная дата слишком ранняя.';
        const maxMsg = input.dataset.msgMax || 'Указанная дата слишком поздняя.';
        const badMsg = input.dataset.msgBad || 'Введите корректную дату.';

        input.addEventListener('invalid', function () {
            input.setCustomValidity('');
            if (input.validity.valueMissing) {
                input.setCustomValidity(requiredMsg);
            } else if (input.validity.rangeUnderflow) {
                input.setCustomValidity(minMsg);
            } else if (input.validity.rangeOverflow) {
                input.setCustomValidity(maxMsg);
            } else if (input.validity.badInput || input.validity.typeMismatch) {
                input.setCustomValidity(badMsg);
            }
        });

        input.addEventListener('input', function () {
            input.setCustomValidity('');
        });
    }

    document.querySelectorAll('input[type="date"], input[type="datetime-local"], input[type="time"]').forEach(bindRuValidation);
});
</script>
