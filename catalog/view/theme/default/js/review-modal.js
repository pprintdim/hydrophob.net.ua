/* Модалка «Залишити відгук».
   На картці товару товар уже відомий (window.product_id) — вибір ховаємо.
   На сторінці відгуків товар обирають автокомплітом: назва + мініатюра. */
(function () {
    var modal = document.getElementById('review-modal');
    if (!modal) return;

    var T = window.hpReviewText || {};

    var form = modal.querySelector('[data-review-form]');
    var done = modal.querySelector('.rvm__done');
    var errBox = modal.querySelector('[data-review-error]');
    var starsBox = modal.querySelector('[data-review-stars]');
    var productField = modal.querySelector('[data-review-product-field]');
    var search = modal.querySelector('[data-review-search]');
    var results = modal.querySelector('[data-review-results]');
    var picked = modal.querySelector('[data-review-picked]');
    var pickedImg = modal.querySelector('[data-review-picked-img]');
    var pickedName = modal.querySelector('[data-review-picked-name]');

    var rating = 0;
    var productId = 0;
    var timer = null;
    var lastFocus = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function paintStars() {
        starsBox.querySelectorAll('.rvm__star').forEach(function (b) {
            b.classList.toggle('is-on', parseInt(b.getAttribute('data-value'), 10) <= rating);
        });
    }

    function open() {
        lastFocus = document.activeElement;

        errBox.hidden = true;
        form.hidden = false;
        done.hidden = true;

        // сторінка товару — товар відомий: показуємо його картку без пошуку
        // й без хрестика; сторінка відгуків — товар обирають автокомплітом
        productId = window.product_id ? parseInt(window.product_id, 10) : 0;

        var fixed = productId && window.hpReviewProduct;

        if (fixed) {
            showPicked(window.hpReviewProduct.name, window.hpReviewProduct.image);
            if (clearBtn) clearBtn.hidden = true;
            productField.hidden = false;
        } else {
            productField.hidden = !!productId;
            if (clearBtn) clearBtn.hidden = false;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('rvm-open');
    }

    function close() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('rvm-open');
        if (lastFocus) lastFocus.focus();
    }

    document.querySelectorAll('[data-review-open]').forEach(function (b) {
        b.addEventListener('click', open);
    });
    modal.querySelectorAll('[data-review-close]').forEach(function (b) {
        b.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
    });

    starsBox.addEventListener('click', function (e) {
        var star = e.target.closest('.rvm__star');
        if (!star) return;
        rating = parseInt(star.getAttribute('data-value'), 10);
        paintStars();
    });

    /* --- картка обраного товару --- */
    function showPicked(name, image) {
        pickedImg.src = image || '';
        pickedImg.hidden = !image;
        pickedImg.alt = name || '';
        pickedName.textContent = name || '';
        picked.hidden = false;
        if (search) search.hidden = true;
        if (results) results.hidden = true;
    }

    /* --- автокомпліт товару --- */
    function pick(product) {
        productId = product.product_id;
        showPicked(product.name, product.image);
        search.value = '';
    }

    if (search) {
        search.addEventListener('input', function () {
            var q = search.value.trim();
            clearTimeout(timer);

            if (q.length < 2) { results.hidden = true; return; }

            timer = setTimeout(function () {
                fetch(T.searchUrl + '&q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var items = data.products || [];
                        if (!items.length) { results.hidden = true; return; }

                        results.innerHTML = items.map(function (p) {
                            return '<button type="button" class="rvm__result" data-id="' + p.product_id + '">' +
                                     '<img src="' + esc(p.image) + '" alt="">' +
                                     '<span>' + esc(p.name) + '</span>' +
                                   '</button>';
                        }).join('');

                        results.querySelectorAll('.rvm__result').forEach(function (btn, i) {
                            btn.addEventListener('click', function () { pick(items[i]); });
                        });

                        results.hidden = false;
                    })
                    .catch(function () {});
            }, 280);
        });
    }

    var clearBtn = modal.querySelector('[data-review-picked-clear]');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            productId = 0;
            picked.hidden = true;
            search.hidden = false;
            search.focus();
        });
    }

    /* --- надсилання --- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errBox.hidden = true;

        if (!productId) { errBox.textContent = T.pick || 'Оберіть товар'; errBox.hidden = false; return; }
        if (!rating) { errBox.textContent = '★'; errBox.hidden = false; return; }

        var fd = new FormData(form);
        fd.append('rating', rating);

        var btn = form.querySelector('.rvm__submit');

        // гість спершу підтверджує пошту кодом: наявний акаунт логіниться,
        // новий створюється — та сама схема, що у швидкому замовленні
        if (!(window.hpCustomer && window.hpCustomer.email) && window.hpOtpStart) {
            btn.disabled = true;

            window.hpOtpStart({
                type: 'checkout',
                email: (form.querySelector('[name="email"]') || {}).value,
                firstname: (form.querySelector('[name="name"]') || {}).value,
                lastname: '',
                telephone: (form.querySelector('[name="telephone"]') || {}).value,
                title: T.title || ''
            }, function () {
                btn.disabled = false;
                form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true }));
            }).then(function (json) {
                btn.disabled = false;

                if (json && json.error) {
                    errBox.textContent = json.error[Object.keys(json.error)[0]];
                    errBox.hidden = false;
                }
            });

            return;
        }

        btn.disabled = true;

        fetch(T.action + '&product_id=' + productId, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            btn.disabled = false;

            if (json.error) { errBox.textContent = json.error; errBox.hidden = false; return; }

            if (typeof gtag === 'function') {
                gtag('event', 'submit_review', { form_name: 'review', item_id: parseInt(productId, 10) || productId, rating: rating });
            }

            form.reset();
            rating = 0;
            paintStars();

            // на сторінці відгуків вибір товару скидаємо, на картці товару він постійний
            if (!(window.product_id && window.hpReviewProduct)) {
                productId = 0;
                picked.hidden = true;
                if (search) search.hidden = false;
            }

            // єдиний для сайту попап успіху
            if (window.hpSuccess) {
                close();
                window.hpSuccess(json.success || (done.textContent || '').trim());
            } else {
                form.hidden = true;
                done.hidden = false;
            }
        })
        .catch(function () {
            btn.disabled = false;
            errBox.textContent = 'Error';
            errBox.hidden = false;
        });
    });
})();
