/* Єдиний рушій попапів-форм магазину.
   Замість окремого скрипта на кожен попап (питання, швидке замовлення,
   відгук) — одна поведінка, налаштована атрибутами розмітки:

     <div class="cdm" data-hp-modal="question" hidden>
       <form data-hp-form
             data-action="index.php?route=information/question/send"
             data-verify="1"          ← гостю спершу підтвердити пошту кодом
             data-reload="1"          ← перезавантажити після реєстрації/входу
             data-event="generate_lead">
       ...
     <button data-hp-modal-open="question">

   Обробник: валідація браузера → (за потреби) код на пошту → POST →
   спільний попап успіху (window.hpSuccess). Кастомний JS попапу потрібен
   лише для того, що справді унікальне (наприклад підтягнути кількість
   товару) — його вішають на подію hp-modal-open. */
(function () {
    function byName(form, name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function val(form, name) {
        var el = byName(form, name);

        return el ? el.value.trim() : '';
    }

    function showError(form, text) {
        var box = form.querySelector('[data-hp-error]');

        if (!box) return;

        box.textContent = text;
        box.hidden = false;
    }

    function clearErrors(form) {
        form.querySelectorAll('[data-hp-error], [data-hp-error-for]').forEach(function (el) {
            el.textContent = '';
            el.hidden = true;
        });
    }

    /* помилки з бекенду під відповідні поля, решта — у спільний рядок */
    function applyErrors(form, errors) {
        var rest = [];

        Object.keys(errors).forEach(function (key) {
            var box = form.querySelector('[data-hp-error-for="' + key + '"]');

            if (box) {
                box.textContent = errors[key];
                box.hidden = false;
            } else {
                rest.push(errors[key]);
            }
        });

        if (rest.length) showError(form, rest[0]);
    }

    function open(modal) {
        modal.hidden = false;
        document.body.classList.add('no-scroll');

        var form = modal.querySelector('[data-hp-form]');

        if (form) {
            clearErrors(form);

            var first = form.querySelector('input:not([type=hidden]):not([readonly]), textarea');
            if (first) setTimeout(function () { first.focus(); }, 60);
        }

        modal.dispatchEvent(new CustomEvent('hp-modal-open', { bubbles: true }));
    }

    function close(modal) {
        modal.hidden = true;
        document.body.classList.remove('no-scroll');
    }

    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-hp-modal-open]');

        if (opener) {
            var modal = document.querySelector('[data-hp-modal="' + opener.getAttribute('data-hp-modal-open') + '"]');

            if (modal) {
                e.preventDefault();
                open(modal);
            }

            return;
        }

        var closer = e.target.closest('[data-hp-modal-close]');

        if (closer) {
            var box = closer.closest('[data-hp-modal]');
            if (box) close(box);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        document.querySelectorAll('[data-hp-modal]:not([hidden])').forEach(close);
    });

    /* --- відправка --- */
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-hp-form]');
        if (!form) return;

        e.preventDefault();
        clearErrors(form);

        if (!form.checkValidity()) {
            form.reportValidity();

            return;
        }

        var modal = form.closest('[data-hp-modal]');
        var needVerify = form.getAttribute('data-verify') === '1' && !(window.hpCustomer && window.hpCustomer.email);

        if (needVerify && window.hpOtpStart) {
            verify(form).then(function (ok) { if (ok) send(form, modal, true); });

            return;
        }

        send(form, modal, false);
    });

    /* гість підтверджує пошту кодом: наявний акаунт логіниться, новий створюється */
    function verify(form) {
        return new Promise(function (resolve) {
            window.hpOtpStart({
                type: 'checkout',
                email: val(form, 'email'),
                firstname: val(form, 'firstname') || val(form, 'name'),
                lastname: val(form, 'lastname'),
                telephone: val(form, 'telephone'),
                title: form.getAttribute('data-verify-title') || ''
            }, function () { resolve(true); })
            .then(function (json) {
                if (json && json.error) {
                    applyErrors(form, json.error);
                    resolve(false);
                }
            });
        });
    }

    function send(form, modal, reloadAfter) {
        var btn = form.querySelector('[type="submit"]');
        var action = form.getAttribute('data-action') || form.action;

        if (btn) btn.disabled = true;

        fetch(action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(new FormData(form)),
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            if (btn) btn.disabled = false;

            if (json.error) {
                applyErrors(form, typeof json.error === 'object' ? json.error : { warning: json.error });

                return;
            }

            var event = form.getAttribute('data-event');

            if (event && typeof gtag === 'function') {
                gtag('event', event, json.ga || { form_name: form.getAttribute('data-hp-form') || '', form_destination: location.pathname });
            }

            var text = json.message || json.success || form.getAttribute('data-success') || '';

            form.reset();
            if (modal) close(modal);

            // після реєстрації/входу шапку треба перемалювати
            if (reloadAfter && form.getAttribute('data-reload') === '1') {
                if (window.hpSuccessAfterReload) window.hpSuccessAfterReload(text);
                window.location.reload();

                return;
            }

            if (window.hpSuccess) window.hpSuccess(text);
        })
        .catch(function () {
            if (btn) btn.disabled = false;
            showError(form, (window.hpFormText && window.hpFormText.error) || 'Помилка. Спробуйте ще раз.');
        });
    }
})();
