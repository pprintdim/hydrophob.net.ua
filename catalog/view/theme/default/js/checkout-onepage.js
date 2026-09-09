/* Одностраничний чекаут: збираємо форму й проганяємо через стокові кроки
   OpenCart одним ланцюжком (адреса → доставка → оплата → підтвердження),
   як зроблено на hydrophob.net. */
(function () {
    var form = document.querySelector('[data-checkout-form]');
    if (!form || !window.hpCheckout) return;

    var C = window.hpCheckout;

    var errBox = form.querySelector('[data-checkout-error]');
    var submitBtn = form.querySelector('[data-checkout-submit]');
    var thanks = document.querySelector('[data-checkout-thanks]');

    var carrierSelect = form.querySelector('[name="shipping_method"]');
    var cityWrap = form.querySelector('[data-city-wrap]');
    var cityInput = form.querySelector('[data-city-input]');
    var cityList = form.querySelector('[data-city-list]');
    var typesWrap = form.querySelector('[data-dest-types]');
    var destWrap = form.querySelector('[data-dest-wrap]');
    var destLabel = form.querySelector('[data-dest-label]');
    var destInput = form.querySelector('[data-dest-input]');
    var destList = form.querySelector('[data-dest-list]');
    var pickupInfo = form.querySelector('[data-pickup-info]');

    // які способи отримання доступні в кожного перевізника
    var DEST_TYPES = {
        novaposhta: ['branch', 'postomat', 'courier'],
        meest:      ['branch', 'courier'],
        courier:    ['courier'],
        pickup:     []
    };

    function val(name) {
        var el = form.querySelector('[name="' + name + '"]');
        return el ? el.value.trim() : '';
    }

    function showError(msg) {
        errBox.textContent = msg;
        errBox.hidden = false;
        submitBtn.disabled = false;
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* --- перемикання полів під обраного перевізника --- */
    function currentCarrier() {
        return carrierSelect.value.split('.')[1] || '';
    }

    /* --- новий покупець / вхід у наявний акаунт --- */
    var authTabs = form.querySelector('[data-auth-tabs]');
    var authHint = form.querySelector('[data-auth-hint]');
    var authLogin = form.querySelector('[data-auth-login]');
    var authMode = 'register';

    function syncAuth() {
        if (!authTabs) return;

        var login = (authMode === 'login');

        form.querySelectorAll('[data-auth-field="register"]').forEach(function (box) {
            box.hidden = login;

            box.querySelectorAll('input').forEach(function (input) {
                // приховані поля не мають блокувати reportValidity
                input.required = !login && input.name !== 'lastname';
                input.disabled = login;
            });
        });

        authTabs.querySelectorAll('[data-auth-mode]').forEach(function (tab) {
            tab.classList.toggle('is-active', tab.getAttribute('data-auth-mode') === authMode);
        });

        authHint.textContent = login ? (C.text.hintLogin || '') : (C.text.hintRegister || '');
        authLogin.hidden = !login;

        // у режимі входу пошта й кнопка коду стоять в одному рядку
        authLogin.closest('.checkout__block').classList.toggle('is-login', login);

        submitBtn.closest('.account__actions').hidden = login;
    }

    if (authTabs) {
        authTabs.addEventListener('click', function (e) {
            var tab = e.target.closest('[data-auth-mode]');
            if (!tab) return;

            authMode = tab.getAttribute('data-auth-mode');
            errBox.hidden = true;
            syncAuth();
        });

        // вхід: код на пошту, після підтвердження сторінка перезавантажується
        // вже із залогіненим покупцем і заповненими контактами
        form.querySelector('[data-auth-send-code]').addEventListener('click', function () {
            var btn = this;
            var email = val('email');

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
                showError(C.text.email || C.text.common);
                return;
            }

            if (!window.hpOtpStart) { showError(C.text.common); return; }

            btn.disabled = true;

            window.hpOtpStart({ type: 'login', email: email, title: C.otpTitle || '' }, function () {
                window.location.reload();
            }).then(function (json) {
                btn.disabled = false;

                if (json && json.error) {
                    showError(json.error[Object.keys(json.error)[0]]);
                }
            }).catch(function () {
                btn.disabled = false;
                showError(C.text.common);
            });
        });

        syncAuth();
    }

    function destType() {
        var checked = form.querySelector('[name="dest_type"]:checked');
        return checked ? checked.value : 'branch';
    }

    // «Відділення» і «Адреса» — одне поле: змінюємо лише підпис,
    // а автокомпліт вмикаємо тільки там, де є що підказувати
    function syncFields() {
        var carrier = currentCarrier();
        var allowed = DEST_TYPES[carrier] || ['branch'];
        var pickup = (carrier === 'pickup');

        // показуємо лише ті способи, що є в цього перевізника
        typesWrap.hidden = (allowed.length < 2);

        form.querySelectorAll('[data-dest-option]').forEach(function (opt) {
            var code = opt.getAttribute('data-dest-option');
            var ok = allowed.indexOf(code) > -1;

            opt.hidden = !ok;

            if (!ok && opt.querySelector('input').checked) {
                var first = form.querySelector('[data-dest-option="' + allowed[0] + '"] input');
                if (first) first.checked = true;
            }
        });

        var type = destType();

        cityWrap.hidden = pickup;
        // поле відділення/адреси зʼявляється лише після вибору міста
        destWrap.hidden = pickup || cityInput.value.trim().length < 2;
        pickupInfo.hidden = !pickup;

        // самовивіз — поля доставки не потрібні: чистимо й знімаємо обовʼязковість
        if (pickup) {
            cityInput.value = '';
            destInput.value = '';
            cityList.hidden = true;
            destList.hidden = true;
        }

        cityInput.required = !pickup;
        destInput.required = !pickup && !destWrap.hidden;

        destLabel.textContent = C.labels[type] || C.labels.branch;
        destInput.placeholder = C.placeholders[type] || '';
        destInput.setAttribute('data-suggest', (type === 'branch' || type === 'postomat') ? '1' : '0');

        if (type === 'courier') destList.hidden = true;
    }

    carrierSelect.addEventListener('change', function () {
        destInput.value = '';
        destList.hidden = true;
        syncFields();
    });

    form.querySelectorAll('[name="dest_type"]').forEach(function (r) {
        r.addEventListener('change', function () {
            destInput.value = '';
            syncFields();
        });
    });

    // зі збереженої адреси підставляємо спосіб отримання, а не тільки текст
    if (C.defaultDestType) {
        var preset = form.querySelector('[data-dest-option="' + C.defaultDestType + '"] input');
        if (preset) preset.checked = true;
    }

    syncFields();

    /* --- автокомпліт міста й відділення --- */
    function bindSuggest(input, list, urlFn, onPick, opts) {
        if (!input || !list) return;
        var timer = null;
        var minLen = (opts && typeof opts.minLength === 'number') ? opts.minLength : 2;

        function request(q) {
            clearTimeout(timer);

            // на кур'єрській адресі підказок нема — вводимо вручну
            if (input.getAttribute('data-suggest') === '0') { list.hidden = true; return; }

            if (q.length < minLen) { list.hidden = true; return; }

            timer = setTimeout(function () {
                fetch(urlFn(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (items) {
                        // tool/city віддає масив, warehouse_suggest — {items:[…]}
                        items = Array.isArray(items) ? items : (items.items || items.warehouses || items.cities || []);
                        if (!items.length) { list.hidden = true; return; }

                        list.innerHTML = items.map(function (it) {
                            var label = typeof it === 'string' ? it : (it.name || it.Description || '');
                            return '<button type="button" class="checkout__suggest-item">' + esc(label) + '</button>';
                        }).join('');

                        list.querySelectorAll('button').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                input.value = btn.textContent;
                                list.hidden = true;
                                if (onPick) onPick();
                            });
                        });

                        list.hidden = false;
                    })
                    .catch(function () {});
            }, 280);
        }

        input.addEventListener('input', function () { request(input.value.trim()); });

        // клік/фокус у полі одразу показує список (для відділень — усі варіанти міста)
        input.addEventListener('focus', function () { request(input.value.trim()); });
        input.addEventListener('click', function () { request(input.value.trim()); });

        document.addEventListener('click', function (e) {
            if (!input.parentNode.contains(e.target)) list.hidden = true;
        });
    }

    bindSuggest(cityInput, cityList, function (q) {
        return C.citySearch + (C.citySearch.indexOf('?') > -1 ? '&' : '?') + 'q=' + encodeURIComponent(q);
    }, function () {
        destInput.value = '';
        syncFields();
    });

    // показ/приховання поля призначення в міру набору міста
    cityInput.addEventListener('input', syncFields);

    // місто вже обране — відділення показуємо з порожнім полем, одразу списком
    bindSuggest(destInput, destList, function (q) {
        return C.warehouseSearch + (C.warehouseSearch.indexOf('?') > -1 ? '&' : '?') +
               'city=' + encodeURIComponent(cityInput.value.trim()) +
               '&carrier=' + encodeURIComponent(currentCarrier()) +
               '&type=' + encodeURIComponent(destType()) +
               '&q=' + encodeURIComponent(q);
    }, null, { minLength: 0 });

    /* --- відправка --- */
    function post(route, data) {
        return fetch('index.php?route=' + route, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(data)
        }).then(function (r) { return r.json().catch(function () { return {}; }); });
    }

    submitBtn.addEventListener('click', function () {
        errBox.hidden = true;

        if (!form.reportValidity()) return;

        var carrier = currentCarrier();
        var type = destType();
        var city = cityInput.value.trim();
        var address1 = '';
        var address2 = '';

        if (carrier === 'pickup') {
            city = '';
            address1 = C.pickupAddress || '-';
        } else {
            if (city.length < 2) { showError(C.text.city); return; }

            address1 = destInput.value.trim();

            if (!address1) {
                showError(type === 'courier' ? C.text.address : (C.text[type] || C.text.warehouse));
                return;
            }

            // спосіб отримання видно в замовленні окремим рядком адреси
            address2 = C.labels[type] || '';
        }

        submitBtn.disabled = true;

        var telephone = form.querySelector('[name="telephone"]');
        var phone = (window.hpPhoneValue ? window.hpPhoneValue(telephone) : telephone.value.trim());

        var addressData = {
            firstname: val('firstname'),
            lastname: val('lastname') || '-',
            email: val('email'),
            telephone: phone,
            company: '',
            address_1: address1,
            address_2: address2,
            city: city || '-',
            postcode: '',
            country_id: '220',
            zone_id: '0',
            shipping_address: '1'
        };

        var comment = val('comment');

        // без акаунта замовлення не приймаємо: підтверджуємо пошту кодом —
        // існуючого логінимо, нового реєструємо (як на hydrophob.net)
        if (!C.logged) {
            if (!window.hpOtpStart) { showError(C.text.common); return; }

            window.hpOtpStart({
                type: 'checkout',
                email: addressData.email,
                firstname: addressData.firstname,
                lastname: addressData.lastname,
                telephone: addressData.telephone,
                title: C.otpTitle || ''
            }, function () {
                C.logged = true;
                runChain(addressData, comment);
            }).then(function (json) {
                if (json && json.error) {
                    var first = Object.keys(json.error)[0];
                    showError(json.error[first]);
                }
            });

            submitBtn.disabled = false;
            return;
        }

        runChain(addressData, comment);
    });

    function runChain(addressData, comment) {
        submitBtn.disabled = true;

        var chain = post('checkout/payment_address/save', addressData).then(function (json) {
            if (json.error) throw json.error;
            return post('checkout/shipping_address/save', addressData);
        });

        chain.then(function (json) {
            if (json && json.error) throw json.error;
            // спершу треба, щоб сесія отримала список методів доставки
            return fetch('index.php?route=checkout/shipping_method', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        }).then(function () {
            return post('checkout/shipping_method/save', { shipping_method: carrierSelect.value, comment: comment });
        }).then(function (json) {
            if (json && json.error) throw json.error;
            return fetch('index.php?route=checkout/payment_method', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        }).then(function () {
            var payment = form.querySelector('[name="payment_method"]:checked').value;

            return post('checkout/payment_method/save', {
                payment_method: payment,
                comment: comment,
                agree: '1'
            }).then(function (json2) {
                if (json2 && json2.error) throw json2.error;
                return fetch('index.php?route=checkout/confirm', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); });
            }).then(function (html) {
                if (payment === 'wayforpay') {
                    // картка: фіксуємо замовлення й віддаємо браузер формі WayForPay
                    return fetch('index.php?route=extension/payment/wayforpay/confirm', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function () {
                        var holder = document.createElement('div');
                        holder.style.display = 'none';
                        holder.innerHTML = html;

                        var w4pForm = holder.querySelector('#wayforpay_submit, form[action*="wayforpay"]');
                        if (!w4pForm) throw C.text.common;

                        document.body.appendChild(holder);
                        w4pForm.submit();
                        return new Promise(function () {});
                    });
                }

                // офлайн-оплати: підтверджуємо замовлення й ведемо на success
                return post('extension/payment/' + payment + '/confirm', {}).then(function () {
                    window.location.href = 'index.php?route=checkout/success';
                });
            });
        }).catch(function (err) {
            var message = C.text.common;

            if (typeof err === 'string') {
                message = err;
            } else if (err && typeof err === 'object') {
                var first = Object.values(err)[0];
                if (typeof first === 'string') message = first;
            }

            showError(message);
        });
    }
})();
