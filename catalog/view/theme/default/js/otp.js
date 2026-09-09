/* OTP-модалка магазину: беззапарольний вхід/реєстрація та зміна email по коду.
   Кнопка [data-code-trigger] задає тип (login/register/email) і збирає поля
   зі свого scope; код надсилається через common/user_popup/sendCode,
   перевіряється через common/user_popup/verifyCode. */
(function () {
    var modal = document.querySelector('[data-code-modal]');
    if (!modal) return;

    var T = window.hpOtpText || {};
    var mailEl = modal.querySelector('[data-code-mail]');
    var titleEl = modal.querySelector('[data-code-title]');
    var errEl = modal.querySelector('[data-code-error]');
    var cells = [].slice.call(modal.querySelectorAll('[data-code-cell]'));
    var confirmBtn = modal.querySelector('[data-code-confirm]');
    var timerEl = modal.querySelector('[data-code-timer]');
    var resendBtn = modal.querySelector('[data-code-resend]');

    var ctx = null;      /* { type, email, data, redirect } */
    var timer = null;

    function post(route, data) {
        var body = new URLSearchParams();
        Object.keys(data).forEach(function (k) {
            if (data[k] !== undefined && data[k] !== null) body.append(k, data[k]);
        });
        return fetch('index.php?route=' + route, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); });
    }

    function showError(msg) {
        errEl.textContent = msg || T.error || 'Помилка';
        errEl.hidden = false;
    }

    function clearError() {
        errEl.hidden = true;
        errEl.textContent = '';
    }


    /* Помилки з бекенду розкладаємо під відповідні поля форми.
       Ключі json.error збігаються з name полів (firstname, telephone, email). */
    function fieldOf(scope, key) {
        if (key === 'email') {
            return scope.querySelector('[data-code-email]') || scope.querySelector('[name="email"]');
        }
        return scope.querySelector('[name="' + key + '"]');
    }

    function clearFieldErrors(scope) {
        scope.querySelectorAll('[data-code-field-error]').forEach(function (el) { el.remove(); });
        scope.querySelectorAll('.has-code-error').forEach(function (el) { el.classList.remove('has-code-error'); });
    }

    function showFieldErrors(scope, errors) {
        clearFieldErrors(scope);
        var first = null;

        Object.keys(errors).forEach(function (key) {
            var field = fieldOf(scope, key);
            if (!field) return;
            field.classList.add('has-code-error');
            var box = document.createElement('div');
            box.className = 'account__field-error';
            box.setAttribute('data-code-field-error', '');
            box.textContent = errors[key];
            field.insertAdjacentElement('afterend', box);
            if (!first) first = field;
        });

        if (first) {
            first.focus();
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return true;
        }
        return false;
    }

    function firstError(json) {
        if (!json || !json.error) return null;
        var keys = Object.keys(json.error);
        return keys.length ? json.error[keys[0]] : null;
    }

    function openModal() {
        modal.hidden = false;
        document.documentElement.style.overflow = 'hidden';
        cells.forEach(function (c) { c.value = ''; });
        clearError();
        syncConfirm();
        setTimeout(function () { cells[0].focus(); }, 60);
    }

    function closeModal() {
        modal.hidden = true;
        document.documentElement.style.overflow = '';
        if (timer) { clearInterval(timer); timer = null; }
        ctx = null;
    }

    function startTimer() {
        var left = 60;
        resendBtn.hidden = true;
        timerEl.textContent = (T.resendIn || 'Повторно через %s с').replace('%s', left);
        if (timer) clearInterval(timer);
        timer = setInterval(function () {
            left -= 1;
            if (left <= 0) {
                clearInterval(timer);
                timer = null;
                timerEl.textContent = '';
                resendBtn.hidden = false;
            } else {
                timerEl.textContent = (T.resendIn || 'Повторно через %s с').replace('%s', left);
            }
        }, 1000);
    }

    function code() {
        return cells.map(function (c) { return c.value.trim(); }).join('');
    }

    function syncConfirm() {
        var full = code().length === 6;
        confirmBtn.setAttribute('aria-disabled', full ? 'false' : 'true');
    }

    cells.forEach(function (cell, i) {
        cell.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 1);
            if (this.value && cells[i + 1]) cells[i + 1].focus();
            clearError();
            syncConfirm();
        });
        cell.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && cells[i - 1]) cells[i - 1].focus();
        });
        cell.addEventListener('paste', function (e) {
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (!text) return;
            e.preventDefault();
            text.split('').slice(0, cells.length).forEach(function (ch, k) { cells[k].value = ch; });
            syncConfirm();
            cells[Math.min(text.length, cells.length) - 1].focus();
        });
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal || e.target.closest('[data-modal-close]')) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    /* --- надсилання коду --- */
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-code-trigger]');
        if (!trigger) return;
        e.preventDefault();

        var scope = trigger.closest('form, .account__panel, section') || document;
        var emailField = scope.querySelector('[data-code-email]') || document.querySelector('[data-code-email]');
        var email = emailField ? emailField.value.trim() : '';
        var type = trigger.getAttribute('data-code-type') || 'login';

        var payload = { type: type, email: email };

        if (type === 'register') {
            var fn = scope.querySelector('[name="firstname"]');
            var ln = scope.querySelector('[name="lastname"]');
            var tel = scope.querySelector('[name="telephone"]');
            payload.firstname = fn ? fn.value.trim() : '';
            payload.lastname = ln ? ln.value.trim() : '';
            payload.telephone = tel ? (window.hpPhoneValue ? window.hpPhoneValue(tel) : tel.value.trim()) : '';
        }

        var redirect = trigger.getAttribute('data-code-redirect');
        if (redirect) payload.redirect = redirect;

        var label = trigger.innerHTML;
        trigger.disabled = true;

        // текст «Надсилаємо…» лише для текстових кнопок; іконна кнопка просто гасне
        if (!trigger.closest('[data-email-row]')) {
            trigger.textContent = T.sending || 'Надсилаємо…';
        }

        post('common/user_popup/sendCode', payload).then(function (json) {
            trigger.disabled = false;
            trigger.innerHTML = label;

            if (json && json.error) {
                if (showFieldErrors(scope, json.error)) return;
                var err = firstError(json);
                if (err) showError(err);
                return;
            }

            clearFieldErrors(scope);

            ctx = { type: type, email: email, data: payload };
            if (mailEl) mailEl.textContent = email;
            var title = trigger.getAttribute('data-code-title');
            if (title && titleEl) titleEl.textContent = title;
            openModal();
            startTimer();
        }).catch(function () {
            trigger.disabled = false;
            trigger.innerHTML = label;
            hpNotify(T.error || 'Помилка', 'error');
        });
    });

    /* --- підтвердження коду --- */
    confirmBtn.addEventListener('click', function () {
        if (!ctx) return;
        if (code().length !== 6) {
            showError(T.incomplete || 'Введіть усі 6 цифр');
            return;
        }

        confirmBtn.disabled = true;

        post('common/user_popup/verifyCode', Object.assign({}, ctx.data, { code: code() })).then(function (json) {
            confirmBtn.disabled = false;

            var err = firstError(json);
            if (err) {
                showError(err);
                cells.forEach(function (c) { c.value = ''; });
                syncConfirm();
                cells[0].focus();
                return;
            }

            // код підтверджено — це або реєстрація, або вхід
            if (typeof gtag === 'function') {
                gtag('event', ctx.type === 'register' ? 'sign_up' : 'login', { method: 'email_code' });
            }

            // програмний сценарій (чекаут): не редіректимо, віддаємо керування колбеку
            if (ctx.onDone) {
                var done = ctx.onDone;
                closeModal();
                done(json);
                return;
            }

            if (json.redirect) {
                window.location.href = json.redirect;
            } else {
                window.location.reload();
            }
        }).catch(function () {
            confirmBtn.disabled = false;
            showError(T.error);
        });
    });

    /* --- повторна відправка --- */
    resendBtn.addEventListener('click', function () {
        if (!ctx) return;
        resendBtn.hidden = true;
        post('common/user_popup/sendCode', ctx.data).then(function (json) {
            var err = firstError(json);
            if (err) { showError(err); resendBtn.hidden = false; return; }
            clearError();
            startTimer();
        }).catch(function () {
            resendBtn.hidden = false;
            showError(T.error);
        });
    });

    /* --- програмний запуск (чекаут): sendCode → модалка → колбек після логіну --- */
    window.hpOtpStart = function (payload, onDone) {
        return post('common/user_popup/sendCode', payload).then(function (json) {
            if (json && json.error) return json;

            ctx = { type: payload.type, email: payload.email, data: payload, onDone: onDone };
            if (mailEl) mailEl.textContent = payload.email;
            if (payload.title && titleEl) titleEl.textContent = payload.title;
            openModal();
            startTimer();

            // код уже був надісланий раніше й ще дійсний — підказуємо це у вікні
            if (json && json.already_sent && json.message) {
                showError(json.message);
            }

            return json;
        });
    };

    /* --- зручності форми: Enter надсилає код, кнопка чекає на пошту й згоду --- */
    (function () {
        var triggers = [].slice.call(document.querySelectorAll('[data-code-trigger]'));
        if (!triggers.length) return;

        triggers.forEach(function (trigger) {
            // кнопкою зміни e-mail керує власна логіка (порівняння з поточною адресою)
            if (trigger.closest('[data-email-row]')) return;

            var scope = trigger.closest('form, .account__panel, section') || document;
            var emailField = scope.querySelector('[data-code-email]') || document.querySelector('[data-code-email]');
            if (!emailField) return;

            // згода з умовами обовʼязкова там, де вона є на сторінці
            var agree = scope.querySelector('input[name="agree"]') ||
                        document.querySelector('input[name="agree"]');

            function valid() {
                var ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(emailField.value.trim());
                return ok && (!agree || agree.checked);
            }

            function sync() {
                var ok = valid();
                trigger.disabled = !ok;
                trigger.classList.toggle('is-disabled', !ok);
                trigger.title = ok ? '' : (T.needEmail || 'Вкажіть e-mail, щоб отримати код');
            }

            emailField.addEventListener('input', sync);
            emailField.addEventListener('blur', sync);
            if (agree) agree.addEventListener('change', sync);

            // Enter у полях форми не перезавантажує сторінку, а відкриває попап коду
            scope.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter') return;
                var t = e.target;
                if (!t.matches || !t.matches('input')) return;
                e.preventDefault();
                if (valid()) trigger.click(); else sync();
            });

            sync();
        });
    })();
})();
