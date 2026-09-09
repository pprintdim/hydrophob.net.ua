document.addEventListener('DOMContentLoaded', function() {
    /* реальна висота фіксованого хедера -> CSS-змінна (кошик, панелі) */
    const headerEl = document.querySelector('.header');
    function syncHeaderH() {
        if (headerEl) document.documentElement.style.setProperty('--header-h', headerEl.offsetHeight + 'px');
    }
    syncHeaderH();
    window.addEventListener('resize', syncHeaderH);

    const langSelectedAll = document.querySelectorAll('.header__lang-selected');
    const searchOpen = document.querySelector('.header__search-open');
    const searchBlock = document.querySelector('.header__search');
    const headerBusket = document.querySelector('.header__busket');
    const busket = document.querySelector('.busket');

    /* кошик рендериться всередині хедера, а хедер — власний stacking context
       (z 100): панель опинялась ПІД глобальним оверлеєм (z 105). Виносимо в body */
    if (busket && busket.parentNode !== document.body) {
        document.body.appendChild(busket);
    }

    /* універсальний оверлей для бічних панелей (кошик, меню): затемнення + блок скролу */
    const sideOverlay = document.createElement('div');
    sideOverlay.className = 'side-overlay';
    document.body.appendChild(sideOverlay);
    window.hpPanel = {
        open: function () {
            sideOverlay.classList.add('is-active');
            document.body.classList.add('no-scroll');
        },
        close: function () {
            sideOverlay.classList.remove('is-active');
            document.body.classList.remove('no-scroll');
        }
    };
    sideOverlay.addEventListener('click', function () {
        if (busket) busket.classList.remove('active');
        const menu = document.querySelector('.header__menu');
        if (menu) menu.classList.remove('active');
        window.hpPanel.close();
    });

    headerBusket.addEventListener('click', () => {
        const open = busket.classList.toggle('active');
        window.hpPanel[open ? 'open' : 'close']();
    });
    const busketClose = document.querySelector('.busket__close');
    if (busketClose) {
        busketClose.addEventListener('click', () => {
            busket.classList.remove('active');
            window.hpPanel.close();
        });
    }
    langSelectedAll.forEach(langSelected => {
        const langBlock = langSelected.closest('.header__lang'); 
        langSelected.addEventListener('click', function(e) {
            e.preventDefault();
            langBlock.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (!langBlock.contains(e.target)) {
                langBlock.classList.remove('active');
            }
        });
    });

    if (searchOpen && searchBlock) {
        // затемнення тримаємо на body — і коли поле просто розкрите, і коли є підказки
        const syncSearchDim = () => {
            document.body.classList.toggle('search-overlay-open', searchBlock.classList.contains('active'));
        };

        searchOpen.addEventListener('click', function(e) {
            e.preventDefault();
            searchBlock.classList.toggle('active');
            syncSearchDim();
            if (searchBlock.classList.contains('active')) {
                const field = searchBlock.querySelector('.header__search-input');
                if (field) field.focus();
            }
        });
        document.addEventListener('click', function(e) {
            if (!searchBlock.contains(e.target)) {
                searchBlock.classList.remove('active');
                syncSearchDim();
            }

            // кнопки «В кошику» самі відкривають шухляду — не закриваємо її одразу
            var opensCart = e.target.closest && e.target.closest('.is-added, [onclick*="hpOpenCart"]');

            if (!opensCart && busket.classList.contains('active') && !busket.contains(e.target) && !headerBusket.contains(e.target)) {
                busket.classList.remove('active');
                window.hpPanel.close();
            }
        });
    }
});

const headerBurger = document.querySelector('.header__burger');
const headerMenu = document.querySelector('.header__menu');
const headerMenuClose = document.querySelector('.header__menu-close');
headerBurger.addEventListener('click', () => {
    headerMenu.classList.add('active');
    if (window.hpPanel) window.hpPanel.open();
});
headerMenuClose.addEventListener('click', () => {
    headerMenu.classList.remove('active');
    if (window.hpPanel) window.hpPanel.close();
});

/* Стрілки слайдерів: показуємо лише при наведенні і лише коли контент
   ширший за видиму частину — інакше кнопки просто заважають. */
(function () {
    function sync(wrap) {
        var slider = wrap.querySelector('.hm-slider');
        if (!slider) return;
        // 2px допуску — субпіксельні заокруглення інакше дають хибний overflow
        wrap.classList.toggle('is-scrollable', slider.scrollWidth - slider.clientWidth > 2);
    }

    function syncAll() {
        document.querySelectorAll('.hm-slider-wrap').forEach(sync);
    }

    document.addEventListener('DOMContentLoaded', syncAll);
    window.addEventListener('load', syncAll);
    window.addEventListener('resize', syncAll);

    // слайди можуть під'їхати пізніше (ajax, ліниві картинки)
    if ('ResizeObserver' in window) {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.hm-slider').forEach(function (slider) {
                new ResizeObserver(function () {
                    var wrap = slider.closest('.hm-slider-wrap');
                    if (wrap) sync(wrap);
                }).observe(slider);
            });
        });
    }
})();

/* Єдиний оверлей для всіх спливаючих елементів (пошук, панелі, дропдауни).
   Будь-хто може попросити затемнення: hpOverlay.show('ключ') / hpOverlay.hide('ключ') —
   оверлей зникає, коли не лишилось жодного власника. */
(function () {
    var el = null;
    var owners = new Set();

    function ensure() {
        if (el) return el;
        el = document.createElement('div');
        el.className = 'hp-overlay';
        document.body.appendChild(el);
        el.addEventListener('click', function () {
            document.dispatchEvent(new CustomEvent('hp-overlay-click'));
        });
        return el;
    }

    window.hpOverlay = {
        show: function (key) {
            owners.add(key || 'default');
            ensure().classList.add('is-visible');
        },
        hide: function (key) {
            owners.delete(key || 'default');
            if (!owners.size && el) el.classList.remove('is-visible');
        }
    };
})();



/* Кошик теми: стоковий cart.* із common.js розрахований на bootstrap
   ($.fn.button, алерти) і падає без нього. Перевизначаємо під нашу шухляду:
   оновити вміст, перерахувати бейдж на іконці, відкрити панель. */
(function () {
    if (!window.jQuery) return;
    var $ = window.jQuery;

    // Бейдж рахуємо з серверних даних (common/cart/ids), а не парсингом DOM —
    // так число завжди збігається з реальним кошиком.
    window.hpUpdateCartBadge = function (count) {
        var btn = document.querySelector('.header__busket');
        if (!btn) return;

        var badge = btn.querySelector('.hc-badge');

        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'hc-badge';
                btn.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    };

    // відкрити шухляду кошика з будь-якого місця теми
    window.hpOpenCart = function () {
        var busket = document.querySelector('.busket');
        if (busket) busket.classList.add('active');
        if (window.hpPanel) window.hpPanel.open();
    };

    function refreshDrawer(openDrawer) {
        $('#cart > ul').load('index.php?route=common/cart/info ul li', function () {
            if (openDrawer) {
                var busket = document.querySelector('.busket');
                if (busket) busket.classList.add('active');

                // панель без затемнення виглядає відірваною — оверлей завжди в парі
                if (window.hpPanel) window.hpPanel.open();
            }
        });

        document.dispatchEvent(new CustomEvent('hp-cart-changed'));
    }

    window.cart = {
        add: function (product_id, quantity, options) {
            var data = 'product_id=' + product_id + '&quantity=' + (typeof quantity !== 'undefined' ? quantity : 1);

            if (options) {
                Object.keys(options).forEach(function (k) {
                    data += '&' + encodeURIComponent('option[' + k + ']') + '=' + encodeURIComponent(options[k]);
                });
            }

            $.post('index.php?route=checkout/cart/add', data, function (json) {
                if (json.redirect) { location = json.redirect; return; }
                if (json.success) {
                    refreshDrawer(true);
                    if (window.hpMarkInCart) window.hpMarkInCart(product_id);

                    if (window.hpGaEvent) {
                        hpGaEvent('add_to_cart', product_id, quantity);
                    }

                    // те саме сповіщення, що й у обраного — єдиний стиль на сайт
                    if (window.hpNotify && window.hpCartText) {
                        hpNotify(hpCartText.addedToast || hpCartText.inCart, 'ok');
                    }
                }
            }, 'json');
        },
        update: function (key, quantity) {
            $.post('index.php?route=checkout/cart/edit', 'key=' + key + '&quantity=' + (typeof quantity !== 'undefined' ? quantity : 1), function () {
                var route = new URLSearchParams(location.search).get('route');
                if (route === 'checkout/cart' || route === 'checkout/checkout' || location.pathname === '/koshyk' || location.pathname === '/oformlennia') {
                    location.reload();
                } else {
                    refreshDrawer(false);
                    document.dispatchEvent(new CustomEvent('hp-cart-changed'));
                }
            }, 'json');
        },
        remove: function (key) {
            $.post('index.php?route=checkout/cart/remove', 'key=' + key, function () {
                var route = new URLSearchParams(location.search).get('route');
                if (route === 'checkout/cart' || route === 'checkout/checkout' || location.pathname === '/koshyk' || location.pathname === '/oformlennia') {
                    location.reload();
                } else {
                    refreshDrawer(false);
                }
            }, 'json');
        }
    };

    // бейдж при завантаженні сторінки
    document.addEventListener('DOMContentLoaded', function () { refreshDrawer(false); });
})();

/* Кнопки «Додати в кошик» → «В кошику»: і після ajax-додавання, і після перезавантаження.
   Позначаємо всі кнопки цього товару — картку каталогу, слайдери, сторінку товару. */
(function () {
    var TEXT = (window.hpCartText && window.hpCartText.inCart) || 'В кошику';

    function markButton(btn) {
        if (!btn || btn.classList.contains('is-added')) return;
        btn.classList.add('is-added');
        swapToOpenCart(btn);

        // кнопка може мати власний короткий напис («Додано» замість «В кошику»)
        var text = btn.getAttribute('data-in-text') || TEXT;

        var label = btn.querySelector('[data-cart-label]');
        if (label) { label.textContent = text; return; }

        for (var i = 0; i < btn.childNodes.length; i++) {
            var n = btn.childNodes[i];
            if (n.nodeType === 3 && n.textContent.trim()) { n.textContent = ' ' + text + ' '; return; }
        }

        // текст усередині span (компактні кнопки wishlist/рекомендацій)
        var span = btn.querySelector('span');
        if (span) span.textContent = text;
    }

    // доданий товар: повторний клік не додає ще раз, а відкриває кошик
    function swapToOpenCart(btn) {
        if (!btn || btn.dataset.origOnclick) return;

        var orig = btn.getAttribute('onclick');
        if (!orig) return;

        btn.dataset.origOnclick = orig;
        btn.setAttribute('onclick', 'hpOpenCart(); return false;');
    }

    function swapBack(btn) {
        if (!btn || !btn.dataset.origOnclick) return;

        btn.setAttribute('onclick', btn.dataset.origOnclick);
        delete btn.dataset.origOnclick;
    }

    var DEFAULT_LABEL = (window.hpCartText && window.hpCartText.addToCart) || 'Додати в кошик';

    function unmarkButton(btn) {
        if (!btn || !btn.classList.contains('is-added')) return;
        btn.classList.remove('is-added');
        swapBack(btn);

        var label = btn.querySelector('[data-cart-label]');
        if (label) { label.textContent = DEFAULT_LABEL; return; }

        for (var i = 0; i < btn.childNodes.length; i++) {
            var n = btn.childNodes[i];
            if (n.nodeType === 3 && n.textContent.trim()) { n.textContent = ' ' + DEFAULT_LABEL + ' '; return; }
        }
    }

    // після видалення з кошика кнопки мають повернутись у звичайний стан
    window.hpUnmarkMissing = function (ids) {
        var inCart = {};
        (ids || []).forEach(function (id) { inCart[String(id)] = true; });

        document.querySelectorAll('.is-added').forEach(function (btn) {
            var source = btn.dataset.origOnclick || btn.getAttribute('onclick') || '';
            var m = source.match(/cart\.add\(\s*'?(\d+)'?/);
            if (m && !inCart[m[1]]) unmarkButton(btn);
        });

        var page = document.getElementById('button-cart');
        if (page && window.product_id && !inCart[String(window.product_id)]) unmarkButton(page);
    };

    window.hpMarkInCart = function (product_id) {
        var id = String(product_id);

        document.querySelectorAll('[onclick*="cart.add"]').forEach(function (btn) {
            var m = (btn.getAttribute('onclick') || '').match(/cart\.add\(\s*'?(\d+)'?/);
            if (m && m[1] === id) markButton(btn);
        });

        if (window.product_id && String(window.product_id) === id) {
            markButton(document.getElementById('button-cart'));
        }
    };

    /* «Купити в 1 клік» для товару, який уже в кошику, не має сенсу: оформлення
       йде через кошик. Кнопку гасимо й вимикаємо, а після видалення з кошика
       (той самий hp-cart-changed) вона знову стає активною. */
    function syncQuickBtn(ids) {
        var btn = document.querySelector('[data-hp-modal-open="quick"]');
        if (!btn || !window.product_id) return;

        var inCart = ids.map(String).indexOf(String(window.product_id)) > -1;

        btn.classList.toggle('is-in-cart', inCart);
        btn.disabled = inCart;

        if (inCart) {
            btn.setAttribute('aria-disabled', 'true');
            btn.title = btn.getAttribute('data-in-cart-hint') || '';
        } else {
            btn.removeAttribute('aria-disabled');
            btn.title = '';
        }
    }

    function syncAll() {
        fetch('index.php?route=common/cart/ids', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                var items = (json && json.items) ? json.items : [];
                var ids = (json && json.ids) ? json.ids : (Array.isArray(json) ? json : []);

                // мапа product_id → { key, quantity } для сторінки товару
                window.hpCartItems = {};
                var total = 0;
                items.forEach(function (it) {
                    window.hpCartItems[it.product_id] = it;
                    total += it.quantity;
                });

                if (window.hpUpdateCartBadge) window.hpUpdateCartBadge(total);

                ids.forEach(window.hpMarkInCart);
                window.hpUnmarkMissing(ids);
                syncQuickBtn(ids);
                document.dispatchEvent(new CustomEvent('hp-cart-synced'));
            })
            .catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', syncAll);
    document.addEventListener('hp-cart-changed', syncAll);
})();

/* Кнопка звуку на відео (контакти, hero): вмикає/вимикає звук і перемикає іконку */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-media-sound]').forEach(function (btn) {
        var wrap = btn.closest('.contacts__media, .hero__media, section, div');
        var video = wrap ? wrap.querySelector('video') : null;

        if (!video) return;

        var iconOff = btn.querySelector('.hero__sound-off');
        var iconOn = btn.querySelector('.hero__sound-on');

        function sync() {
            var on = !video.muted;

            btn.setAttribute('aria-pressed', on ? 'true' : 'false');

            if (iconOff) iconOff.hidden = on;
            if (iconOn) iconOn.hidden = !on;
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            video.muted = !video.muted;

            // автоплей-відео могло стояти на паузі — з увімкненим звуком запускаємо
            if (!video.muted && video.paused) {
                video.play().catch(function () {});
            }

            sync();
        });

        sync();
    });
});

/* Синхронізатор блокування прокрутки. Попапи в темі ставлять різні класи й
   стилі, через що скрол міг лишитись заблокованим після закриття або, навпаки,
   розблокуватись при відкритому другому попапі. Тут одне джерело правди:
   дивимось, чи є на сторінці ХОЧ ОДИН відкритий попап. */
(function () {
    var SELECTORS = [
        '[data-code-modal]:not([hidden])',      /* код підтвердження */
        '[data-question-modal]:not([hidden])',  /* поставити питання */
        '[data-cat-desc-modal]:not([hidden])',  /* опис категорії */
        '.rvm.is-open',                         /* відгук */
        '.qo.is-open',                          /* швидке замовлення */
        '.busket.active',                       /* кошик */
        '.header__menu.active'                  /* мобільне меню */
    ];

    function scrollbarWidth() {
        return window.innerWidth - document.documentElement.clientWidth;
    }

    function sync() {
        var open = SELECTORS.some(function (sel) {
            return document.querySelector(sel);
        });

        if (open) {
            // ширину смуги міряємо ДО блокування, інакше вона вже 0
            if (!document.body.classList.contains('hp-modal-open')) {
                document.documentElement.style.setProperty('--hp-scrollbar', scrollbarWidth() + 'px');
            }

            document.body.classList.add('hp-modal-open');
        } else {
            document.body.classList.remove('hp-modal-open');
            document.documentElement.style.removeProperty('--hp-scrollbar');
            // старий спосіб блокування через inline-стиль html теж знімаємо
            document.documentElement.style.overflow = '';
        }
    }

    // Спостерігач за всім DOM тут занадто дорогий: слайдери мутують
    // інлайн-стилі щокадру і браузер захлинається. Натомість перевіряємо
    // стан після кліків/Escape — попапи відкриваються саме ними.
    var queued = false;

    function queueSync() {
        if (queued) return;
        queued = true;

        requestAnimationFrame(function () {
            queued = false;
            sync();
        });
    }

    document.addEventListener('DOMContentLoaded', sync);
    document.addEventListener('click', queueSync, true);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') queueSync();
    }, true);
})();

/* ── Єдина система сповіщень ──────────────────────────────────
   Одна поведінка на весь сайт: тост знизу, зникає за 3 с, є хрестик.
   Серверні повідомлення приходять розміткою [data-toast], програмні —
   через window.hpNotify('текст', 'ok'|'error'|'info'). */
(function () {
    var LIFETIME = 3000;

    function bind(toast) {
        if (toast.dataset.bound) return;
        toast.dataset.bound = '1';

        var timer = setTimeout(hide, LIFETIME);

        function hide() {
            clearTimeout(timer);
            toast.classList.add('is-hidden');
            setTimeout(function () { toast.remove(); }, 320);
        }

        var close = toast.querySelector('[data-toast-close]');
        if (close) close.addEventListener('click', hide);

        // наведення тримає сповіщення на екрані, поки читають
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () { timer = setTimeout(hide, LIFETIME); });

        // повторний hpNotify оновив текст — таймер життя починається заново
        toast.addEventListener('hp-toast-refresh', function () {
            clearTimeout(timer);
            toast.classList.remove('is-hidden');
            timer = setTimeout(hide, LIFETIME);
        });
    }

    function icon(type) {
        if (type === 'error') {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 17h.01"/></svg>';
        }

        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
    }

    window.hpNotify = function (text, type) {
        if (!text) return;

        type = type || 'ok';

        // одне активне сповіщення: швидкі повторні дії (тогл сердечка)
        // оновлюють поточний тост, а не плодять стопку
        var existing = document.querySelector('.hp-toast[data-live]');

        if (existing) {
            existing.className = 'hp-toast hp-toast--' + type;
            existing.setAttribute('data-live', '');
            existing.querySelector('span').textContent = text;
            existing.dispatchEvent(new CustomEvent('hp-toast-refresh'));
            return;
        }

        var toast = document.createElement('div');
        toast.className = 'hp-toast hp-toast--' + type;
        toast.setAttribute('data-toast', '');
        toast.setAttribute('data-live', '');
        toast.innerHTML = icon(type) +
            '<span></span>' +
            '<button type="button" class="hp-toast__close" data-toast-close aria-label="×">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>';

        toast.querySelector('span').textContent = text;

        document.body.appendChild(toast);
        bind(toast);
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-toast]').forEach(bind);
    });
})();

/* ── Автозаповнення форм даними залогіненого покупця ──────────
   Одна логіка для всіх попапів (швидке замовлення, питання, відгук):
   порожні поля отримують ім'я/пошту/телефон з акаунта. Заповнене
   руками не чіпаємо. */
(function () {
    function customer() {
        return window.hpCustomer || {};
    }

    function fullName() {
        var C = customer();

        return ((C.firstname || '') + ' ' + (C.lastname || '')).trim();
    }

    function fill(scope) {
        var C = customer();
        if (!C.email) return;

        var fullname = fullName();

        scope.querySelectorAll('input').forEach(function (input) {
            if (input.value.trim() !== '') { lock(input); return; }

            switch (input.name) {
                case 'firstname':
                    input.value = C.firstname || '';
                    break;
                case 'lastname':
                    input.value = C.lastname || '';
                    break;
                case 'name':
                    input.value = fullname;
                    break;
                case 'email':
                    input.value = C.email || '';
                    break;
                case 'contact':
                    input.value = C.email || C.telephone || '';
                    break;
                case 'telephone':
                    input.value = C.telephone || '';
                    // маска має підхопити код країни з повного номера
                    if (input._iti && C.telephone) {
                        try { input._iti.setNumber(C.telephone); } catch (e) {}
                    }
                    break;
            }

            if (input.value) input.dispatchEvent(new Event('input', { bubbles: true }));

            lock(input);
        });
    }

    /* Контакти з акаунта у формах не редагують: поле стає лише для читання,
       а поруч зʼявляється олівець із посиланням на кабінет. Так дані форми
       завжди збігаються з акаунтом, а міняти їх — в одному місці. */
    function lock(input) {
        var C = customer();

        if (!C.edit_url) return;
        if (['firstname', 'lastname', 'name', 'email', 'telephone', 'contact'].indexOf(input.name) === -1) return;
        if (!input.value.trim() || input.dataset.hpLocked) return;

        input.dataset.hpLocked = '1';
        input.readOnly = true;

        var host = input.closest('.iti') || input;

        if (!host.parentNode) return;

        // олівець кладемо в обгортку навколо самого поля, а не в контейнер
        // рядка: інакше при підказці під полем іконка з'їжджає вниз
        var wrap = document.createElement('span');
        wrap.className = 'hp-field-locked';

        host.parentNode.insertBefore(wrap, host);
        wrap.appendChild(host);

        var link = document.createElement('a');
        link.className = 'hp-field-edit';
        link.href = C.edit_url;
        link.title = C.edit_hint || '';
        link.setAttribute('aria-label', C.edit_hint || '');
        link.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';

        wrap.appendChild(link);
    }

    // позначаємо документ як «покупець увійшов» — цим користується CSS
    if (customer().email) {
        document.documentElement.classList.add('hp-logged');
    }

    // доступно ззовні: форму, додану динамічно, можна заповнити вручну
    window.hpFillForms = function (scope) {
        (scope ? [scope] : [].slice.call(document.querySelectorAll('form'))).forEach(fill);
    };

    document.addEventListener('DOMContentLoaded', function () {
        // усі форми (попапи теж у DOM з самого старту) — одразу
        document.querySelectorAll('form').forEach(fill);

        // після reset (відправлена форма попапа) — заповнюємо знову
        document.addEventListener('reset', function (e) {
            if (e.target && e.target.tagName === 'FORM') {
                setTimeout(function () { fill(e.target); }, 0);
            }
        }, true);

        // перший фокус у полі — страховка, якщо форму додали динамічно
        document.addEventListener('focusin', function (e) {
            var form = e.target && e.target.closest ? e.target.closest('form') : null;
            if (form) fill(form);
        });
    });
})();

/* ── Обране (wishlist) ────────────────────────────────────────
   Тогл без перезавантаження і без скролу вгору: сердечко стає
   червоним, повторний клік прибирає товар. Бейдж на іконці в
   хедері тримає актуальну кількість. */
(function () {
    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams(data),
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); });
    }

    function badge(total) {
        var link = document.querySelector('.header__wishlist');
        if (!link) return;

        var b = link.querySelector('.hc-badge');

        if (total > 0) {
            if (!b) {
                b = document.createElement('span');
                b.className = 'hc-badge';
                link.appendChild(b);
            }
            b.textContent = total;
        } else if (b) {
            b.remove();
        }
    }

    function paint(productId, active) {
        document.querySelectorAll('[data-wishlist="' + productId + '"]').forEach(function (btn) {
            btn.classList.toggle('is-active', active);
        });
    }

    // повна заміна стокового wishlist.* (alert + скрол вгору)
    window.wishlist = {
        add: function (productId) { window.wishlist.toggle(productId); },
        remove: function (productId) { window.wishlist.toggle(productId); },
        toggle: function (productId) {
            post('index.php?route=account/wishlist/toggle', { product_id: productId }).then(function (json) {
                paint(productId, !!json.in);
                badge(json.total || 0);

                if (window.hpGaEvent && json.in) {
                    hpGaEvent('add_to_wishlist', productId, 1);
                }

                if (window.hpNotify && window.hpWishlistText) {
                    hpNotify(json.in ? hpWishlistText.added : hpWishlistText.removed, 'ok');
                }
            }).catch(function () {});
        }
    };

    // кнопки без onclick (пошук тощо) теж працюють
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-wishlist]');
        if (!btn || btn.hasAttribute('onclick')) return;

        e.preventDefault();
        window.wishlist.toggle(btn.getAttribute('data-wishlist'));
    });

    // розмітка після завантаження: активні сердечка + бейдж
    document.addEventListener('DOMContentLoaded', function () {
        fetch('index.php?route=account/wishlist/ids', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                (json.ids || []).forEach(function (id) { paint(id, true); });
                badge(json.total || 0);
            })
            .catch(function () {});
    });
})();

/* Друкування секційних заголовків при появі у в'юпорті — той самий ефект,
   що в верстці (help-typing.js), розкатаний на всі секції сайту. */
(function () {
    var SELECTOR = '.hm-sec__title, .product__viewed-title, .product__related-title, ' +
        '.product__faq-title, .dx-steps__title, .delivery__name, .contacts__name, ' +
        '.about__name, .contacts__title, .contacts__help-name, .dx-cta__text, ' +
        '.seo-desc__title, .faq-group__title';

    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var items = [].slice.call(document.querySelectorAll(SELECTOR)).filter(function (el) {
        // заголовки з вкладеною розміткою не чіпаємо — textContent її б знищив
        return !el.children.length && el.textContent.trim();
    });
    if (!items.length) return;

    var states = new Map();

    items.forEach(function (el) {
        states.set(el, { full: el.textContent.replace(/\s+/g, ' ').trim(), done: false });
        el.textContent = '';
        el.style.minHeight = '1.2em'; /* висота не стрибає під час друку */
    });

    function type(el) {
        var s = states.get(el);
        if (!s || s.done) return;
        s.done = true;
        var i = 0;
        (function step() {
            i += 1;
            el.textContent = s.full.slice(0, i);
            if (i < s.full.length) setTimeout(step, 45);
        })();
    }

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            type(entry.target);
            io.unobserve(entry.target);
        });
    }, { threshold: 0.6 });

    items.forEach(function (el) { io.observe(el); });
})();

/* GA4: спільний хелпер подій товару. Дані беремо з тієї ж картки, що вже
   є в DOM (на сторінці товару — з window.hpGaItem), тож зайвих запитів немає. */
(function () {
    function num(text) {
        // «1 500 грн» / «$11.96» → 1500 / 11.96
        var clean = String(text || '').replace(/\s| /g, '').replace(/[^0-9.,]/g, '').replace(',', '.');
        var value = parseFloat(clean);

        return isNaN(value) ? 0 : value;
    }

    // картка товару в DOM: шукаємо за кнопкою кошика або сердечком
    function findCard(productId) {
        var anchor = document.querySelector('[onclick*="cart.add(\'' + productId + '\'"]')
            || document.querySelector('[data-wishlist="' + productId + '"]');

        return anchor ? anchor.closest('.product__item, .product, .swiper-slide') : null;
    }

    function itemFrom(productId) {
        // сторінка товару вже має повний набір даних із сервера
        if (window.hpGaItem && window.hpGaItem.items && window.hpGaItem.items.length
            && String(window.hpGaItem.items[0].item_id) === String(productId)) {
            return JSON.parse(JSON.stringify(window.hpGaItem.items[0]));
        }

        var item = { item_id: parseInt(productId, 10) || productId, item_brand: 'Hydrophob' };
        var card = findCard(productId);

        if (card) {
            var name = card.querySelector('.product__item-title, .product__name');
            var priceNew = card.querySelector('.product__item-price-new');
            var price = priceNew || card.querySelector('.product__item-price, .product__price');

            if (name) item.item_name = name.textContent.trim();
            if (price) item.price = num(price.textContent);
        }

        return item;
    }

    window.hpGaEvent = function (name, productId, quantity) {
        if (typeof gtag !== 'function') return;

        var item = itemFrom(productId);

        item.quantity = parseInt(quantity, 10) || 1;

        gtag('event', name, {
            currency: window.hpGaCurrency || 'UAH',
            value: (item.price || 0) * item.quantity,
            items: [item]
        });
    };
})();
