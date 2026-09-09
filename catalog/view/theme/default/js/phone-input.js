/* Маска телефону з вибором країни (intl-tel-input).
   Користувач вводить номер БЕЗ коду країни — код показує сам віджет зліва.
   Довжина обмежується під обрану країну (для UA це 9 цифр після +380),
   плейсхолдер — реальний національний приклад номера цієї країни. */
(function () {
    if (!window.intlTelInput) return;

    var UTILS = 'https://cdn.jsdelivr.net/npm/intl-tel-input@23/build/js/utils.js';

    // Скільки цифр у національному номері обраної країни.
    // Джерело — приклад, який бібліотека сама кладе в placeholder (autoPlaceholder),
    // бо глобального intlTelInputUtils у збірці v23 може не бути.
    function nationalLength(input) {
        var example = input.getAttribute('placeholder') || '';
        var digits = example.replace(/\D/g, '').length;

        if (digits >= 4 && digits <= 15) return digits;

        if (window.intlTelInputUtils && input._iti) {
            try {
                var iso = input._iti.getSelectedCountryData().iso2;
                var sample = window.intlTelInputUtils.getExampleNumber(
                    iso, true, window.intlTelInputUtils.numberFormat.NATIONAL
                );
                return (sample || '').replace(/\D/g, '').length;
            } catch (e) {}
        }

        return 0;
    }

    function applyRules(input) {
        if (!input._iti) return;

        input._maxDigits = nationalLength(input);
        input.setAttribute('inputmode', 'numeric');
        input.setAttribute('autocomplete', 'tel-national');
    }

    function trim(input) {
        var max = input._maxDigits;
        if (!max) return;

        var digits = input.value.replace(/\D/g, '');
        if (digits.length <= max) return;

        // ріжемо зайве, зберігаючи введене форматування
        var kept = 0;
        var out = '';

        for (var i = 0; i < input.value.length && kept < max; i++) {
            var ch = input.value[i];
            if (/\d/.test(ch)) kept++;
            out += ch;
        }

        input.value = out;
    }

    document.querySelectorAll('input[name="telephone"], input[type="tel"]').forEach(function (input) {
        if (input._iti) return;

        input._iti = window.intlTelInput(input, {
            initialCountry: 'ua',
            // Україна першою в списку: інакше зверху стоїть Afghanistan
            // і випадковий Enter у пошуку країн обирає саме його
            countryOrder: ['ua', 'pl', 'de', 'cz', 'us', 'gb'],
            separateDialCode: true,
            strictMode: true,           // пускає лише цифри й службові символи
            nationalMode: true,         // вводимо без коду країни
            autoPlaceholder: 'polite',  // приклад номера обраної країни
            utilsScript: UTILS
        });

        // порожнє поле завжди повертається до України
        input.addEventListener('blur', function () {
            if (input.value.trim()) return;

            try {
                if (input._iti.getSelectedCountryData().iso2 !== 'ua') {
                    input._iti.setCountry('ua');
                    applyRules(input);
                }
            } catch (e) {}
        });

        // приклад і ліміт зʼявляються, коли підвантажиться utils
        input.addEventListener('countrychange', function () {
            applyRules(input);
            trim(input);
        });

        input.addEventListener('input', function () { trim(input); });
        input.addEventListener('paste', function () { setTimeout(function () { trim(input); }, 0); });

        // Плагін ставить полю власний padding-left, виміряний по кнопці з
        // прапорцем: у прихованому попапі ширина нульова, а без готових стилів
        // виходили сотні пікселів. Відступ задає CSS — інлайн знімаємо.
        input.style.paddingLeft = '';

        if (input._iti.promise && input._iti.promise.then) {
            input._iti.promise.then(function () {
                applyRules(input);
                setTimeout(function () { applyRules(input); }, 300);
            });
        } else {
            var wait = setInterval(function () {
                if (!window.intlTelInputUtils) return;
                clearInterval(wait);
                applyRules(input);
            }, 200);
            setTimeout(function () { clearInterval(wait); }, 8000);
        }

        // стокові POST-форми: перед відправкою пишемо повний міжнародний номер
        var form = input.form;
        if (form && !form._itiHook) {
            form._itiHook = true;
            form.addEventListener('submit', function () {
                form.querySelectorAll('input[name="telephone"], input[type="tel"]').forEach(function (el) {
                    if (el._iti) el.value = el._iti.getNumber() || el.value;
                });
            });
        }
    });

    // для ajax-збирачів (OTP, швидке замовлення): повний номер конкретного поля
    window.hpPhoneValue = function (el) {
        if (!el) return '';
        if (el._iti) return el._iti.getNumber() || el.value.trim();
        return el.value.trim();
    };
})();
