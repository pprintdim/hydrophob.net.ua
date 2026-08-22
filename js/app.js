/* === hero-slider.js === */
/* Hero-відеослайдер: автоплей, повне програвання кожного відео → наступний слайд.
   Ліниве завантаження: при відкритті сторінки вантажиться лише перше відео,
   решта створюються при перемиканні (з лоадером). Кнопка звуку на кожному слайді. */
(function () {
    var root = document.querySelector('[data-hero-slider]');
    if (!root) return;

    var slides = [].slice.call(root.querySelectorAll('.hero__slide'));
    var dots = [].slice.call(root.querySelectorAll('[data-hero-dot]'));
    var loader = root.querySelector('[data-hero-loader]');
    var current = 0;
    var soundOn = false;

    function videoOf(slide) {
        return slide.querySelector('video');
    }

    function ensureVideo(slide, cb) {
        var v = videoOf(slide);
        if (v) { cb(v); return; }
        var box = slide.querySelector('.hero__video');
        var src = box.getAttribute('data-video');
        if (!src) { cb(null); return; }
        if (loader) loader.hidden = false;
        v = document.createElement('video');
        v.src = src;
        v.muted = true;
        v.playsInline = true;
        v.setAttribute('playsinline', '');
        v.preload = 'auto';
        v.addEventListener('canplay', function onReady() {
            v.removeEventListener('canplay', onReady);
            if (loader) loader.hidden = true;
            var img = box.querySelector('img');
            if (img) img.remove();
            cb(v);
        });
        v.addEventListener('error', function () {
            if (loader) loader.hidden = true;
            cb(null);
        });
        box.appendChild(v);
        v.load();
    }

    function applySound(v, slide) {
        if (!v) return;
        v.muted = !soundOn;
        var btn = slide.querySelector('[data-hero-sound]');
        if (btn) {
            btn.querySelector('.hero__sound-off').hidden = soundOn;
            btn.querySelector('.hero__sound-on').hidden = !soundOn;
        }
    }

    function show(i) {
        current = (i + slides.length) % slides.length;
        slides.forEach(function (s, si) {
            var active = si === current;
            s.classList.toggle('is-active', active);
            var v = videoOf(s);
            if (v && !active) { v.pause(); }
        });
        dots.forEach(function (d, di) {
            d.classList.toggle('is-active', di === current);
        });
        var slide = slides[current];
        ensureVideo(slide, function (v) {
            if (!v) return;
            applySound(v, slide);
            v.currentTime = 0;
            var p = v.play();
            if (p && p.catch) p.catch(function () {});
            v.onended = function () { show(current + 1); };
        });
    }

    /* перший слайд: відео вже в розмітці */
    var first = videoOf(slides[0]);
    if (first) {
        first.onended = function () { show(1); };
        applySound(first, slides[0]);
    }

    dots.forEach(function (d) {
        d.addEventListener('click', function () {
            show(parseInt(d.getAttribute('data-hero-dot'), 10));
        });
    });

    root.querySelectorAll('[data-hero-sound]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            soundOn = !soundOn;
            slides.forEach(function (s) { applySound(videoOf(s), s); });
            /* після ввімкнення звуку перезапускаємо активне відео play (мобільні політики) */
            var v = videoOf(slides[current]);
            if (v) { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
        });
    });
})();

/* === home-sections.js === */
/* Секції головної знімка: Акційні / Рекомендовані / FAQ / Переглянуті — з data/*.json.
   + трекер переглянутих товарів (localStorage) на product-*.html
   + collapse довгого SEO-опису на головній */
(function () {
    var PROD = 'https://hydrophob.net.ua/';
    var VIEWED_KEY = 'hydro_viewed';

    /* --- трекер переглянутих: на сторінці товару пишемо id --- */
    var pm = (location.pathname + location.search).match(/product\.php\?.*\bid=(\d+)/);
    if (pm) {
        try {
            var seen = JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]');
            var id = parseInt(pm[1], 10);
            seen = [id].concat(seen.filter(function (x) { return x !== id; })).slice(0, 12);
            localStorage.setItem(VIEWED_KEY, JSON.stringify(seen));
        } catch (e) {}
    }

    var mainEl = document.querySelector('main');
    var isHome = mainEl && document.querySelector('.hero');

    /* --- collapse опису (.desc) на головній --- */
    var desc = document.querySelector('.desc');
    if (desc && !desc.classList.contains('is-collapsible')) {
        desc.classList.add('is-collapsible');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'desc-toggle';
        var arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        btn.innerHTML = '<span>Показати більше</span>' + arrow;
        desc.parentNode.insertBefore(btn, desc.nextSibling);
        btn.addEventListener('click', function () {
            var open = desc.classList.toggle('is-open');
            btn.classList.toggle('is-open', open);
            btn.querySelector('span').textContent = open ? 'Згорнути' : 'Показати більше';
            if (!open) desc.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    if (!isHome) return;

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }
    var badgesMap = {};
    function card(p, oldPrice) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product.php?id=' + p.product_id;
        var price = oldPrice
            ? '<span class="product__item-price-new">' + fmt(p.price) + '</span> <span class="product__item-price-old">' + fmt(oldPrice) + '</span>'
            : fmt(p.price);
        var badges = (badgesMap[String(p.product_id)] || []).slice();
        if (oldPrice && badges.indexOf('sale') === -1) badges.unshift('sale');
        var badgesHtml = badges.length
            ? '<div class="product__item-badges">' + badges.map(function (b) {
                var label = b === 'sale' ? 'Акція' : (b === 'new' ? 'Новинка' : 'Топ');
                return '<span class="product__item-badge product__item-badge--' + b + '">' + label + '</span>';
            }).join('') + '</div>'
            : '';
        return '<div class="product__item hm-slide">' +
            '<div class="product__item-media">' +
            '<a class="product__item-image" href="' + href + '"><img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'"></a>' +
            badgesHtml +
            '<button type="button" class="product__item-wish" title="Додати до обраного" aria-label="Додати до обраного">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0l-1.4 1.4-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4l8.8 8.8 8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg>' +
            '</button>' +
            '</div>' +
            '<div class="product__item-content">' +
            '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
            '<p class="product__item-price">' + price + '</p>' +
            '<button class="product__item-add btn-2" type="button">В кошик' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.56706 2.90039H6.90039V6.90039L2.90039 6.90039V9.56706H6.90039V13.5671H9.56706V9.56706H13.5671V6.90039L9.56706 6.90039V2.90039Z" fill="white"/></svg>' +
            '</button>' +
            '</div></div>';
    }
    function sliderSection(id, title, cardsHtml) {
        if (!cardsHtml) return '';
        return '<section class="hm-sec" id="' + id + '"><div class="container">' +
            '<h2 class="hm-sec__title page-name">' + title + '</h2>' +
            '<div class="hm-slider-wrap">' +
            '<button type="button" class="hm-arrow hm-arrow--prev" aria-label="Назад"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
            '<div class="hm-slider">' + cardsHtml + '</div>' +
            '<button type="button" class="hm-arrow hm-arrow--next" aria-label="Вперед"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
            '</div></div></section>';
    }

    Promise.all([
        fetch('data/products.json').then(function (r) { return r.json(); }),
        fetch('data/home.json').then(function (r) { return r.json(); })
    ]).then(function (res) {
        var products = res[0], home = res[1];
        var byId = {};
        products.forEach(function (p) { byId[p.product_id] = p; });
        badgesMap = home.badges || {};

        var html = '';

        /* Акційні */
        var promoCards = (home.promo || []).map(function (row) {
            var p = byId[row.product_id];
            return p ? card(p, row.old_price) : '';
        }).join('');
        html += sliderSection('promo', 'Акційні пропозиції', promoCards);

        /* Рекомендовані */
        var recCards = (home.recommended || []).map(function (id) {
            var p = byId[id];
            return p ? card(p) : '';
        }).join('');
        html += sliderSection('recommended', 'Рекомендовані товари', recCards);

        /* Галерея-плашки (фото/відео, відкриття в попапі) */
        if (home.gallery && home.gallery.length) {
            html += '<section class="hm-sec" id="gallery"><div class="container">' +
                '<h2 class="hm-sec__title page-name">Hydrophob у дії</h2>' +
                '<div class="hm-gallery">' +
                home.gallery.map(function (g, i) {
                    var item = typeof g === 'string' ? { type: 'image', src: g } : g;
                    var wide = (i % 4 === 0) ? ' hm-gallery__item--wide' : '';
                    var thumb = item.type === 'video' ? (item.poster || '') : item.src;
                    var play = item.type === 'video'
                        ? '<span class="hm-gallery__play" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>'
                        : '';
                    return '<button type="button" class="hm-gallery__item' + wide + '" data-lightbox-type="' + item.type + '" data-lightbox-src="' + item.src + '"' + (item.poster ? ' data-lightbox-poster="' + item.poster + '"' : '') + '>' +
                        '<img src="' + thumb + '" alt="Hydrophob" loading="lazy">' + play +
                        '</button>';
                }).join('') +
                '</div></div></section>';
        }

        /* FAQ */
        if (home.faq && home.faq.length) {
            html += '<section class="hm-sec hm-faq-sec" id="faq"><div class="container">' +
                '<h2 class="hm-sec__title page-name">Питання та відповіді</h2>' +
                '<div class="hm-faq">' +
                home.faq.map(function (item, i) {
                    return '<div class="hm-faq__item' + (i === 0 ? ' is-open' : '') + '">' +
                        '<button type="button" class="hm-faq__btn" aria-expanded="' + (i === 0 ? 'true' : 'false') + '">' +
                        '<span>' + item.q + '</span>' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>' +
                        '</button>' +
                        '<div class="hm-faq__answer"><p>' + item.a + '</p></div>' +
                        '</div>';
                }).join('') +
                '</div></div></section>';
        }

        /* Переглянуті */
        var viewed = [];
        try { viewed = JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]'); } catch (e) {}
        var viewedCards = viewed.map(function (id) {
            var p = byId[id];
            return p ? card(p) : '';
        }).join('');
        html += sliderSection('viewed', 'Переглянуті нещодавно', viewedCards);

        mainEl.insertAdjacentHTML('beforeend', html);

        /* стрілки слайдерів */
        mainEl.querySelectorAll('.hm-slider-wrap').forEach(function (wrap) {
            var slider = wrap.querySelector('.hm-slider');
            var step = function () {
                var el = slider.querySelector('.hm-slide');
                return el ? el.getBoundingClientRect().width + 24 : 300;
            };
            wrap.querySelector('.hm-arrow--prev').addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); });
            wrap.querySelector('.hm-arrow--next').addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); });
        });

        /* FAQ акордеон */
        mainEl.querySelectorAll('.hm-faq__btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.parentNode;
                var open = item.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });

        /* лайтбокс галереї (фото + відео) з гортанням */
        var lbItems = (home.gallery || []).map(function (g) {
            return typeof g === 'string' ? { type: 'image', src: g } : g;
        });
        var lbIndex = 0;

        var lb = document.createElement('div');
        lb.className = 'hm-lightbox';
        lb.innerHTML = '<button type="button" class="hm-lightbox__close" aria-label="Закрити">' +
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>' +
            '</button>' +
            '<button type="button" class="hm-lightbox__nav hm-lightbox__nav--prev" aria-label="Попереднє">' +
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>' +
            '</button>' +
            '<div class="hm-lightbox__body"></div>' +
            '<button type="button" class="hm-lightbox__nav hm-lightbox__nav--next" aria-label="Наступне">' +
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>' +
            '</button>' +
            '<div class="hm-lightbox__counter"></div>';
        document.body.appendChild(lb);
        var lbBody = lb.querySelector('.hm-lightbox__body');
        var lbCounter = lb.querySelector('.hm-lightbox__counter');

        function showLb(i) {
            lbIndex = (i + lbItems.length) % lbItems.length;
            var item = lbItems[lbIndex];
            if (item.type === 'video') {
                lbBody.innerHTML = '<video src="' + item.src + '"' + (item.poster ? ' poster="' + item.poster + '"' : '') + ' controls autoplay playsinline></video>';
            } else {
                lbBody.innerHTML = '<img src="' + item.src + '" alt="Hydrophob">';
            }
            lbCounter.textContent = (lbIndex + 1) + ' / ' + lbItems.length;
        }
        function openLb(i) {
            showLb(i);
            lb.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
        function closeLb() {
            lb.classList.remove('is-open');
            lbBody.innerHTML = '';
            document.body.style.overflow = '';
        }
        lb.addEventListener('click', function (e) {
            if (e.target === lb || e.target.closest('.hm-lightbox__close')) closeLb();
        });
        lb.querySelector('.hm-lightbox__nav--prev').addEventListener('click', function () { showLb(lbIndex - 1); });
        lb.querySelector('.hm-lightbox__nav--next').addEventListener('click', function () { showLb(lbIndex + 1); });
        document.addEventListener('keydown', function (e) {
            if (!lb.classList.contains('is-open')) return;
            if (e.key === 'Escape') closeLb();
            if (e.key === 'ArrowLeft') showLb(lbIndex - 1);
            if (e.key === 'ArrowRight') showLb(lbIndex + 1);
        });

        mainEl.querySelectorAll('[data-lightbox-src]').forEach(function (el, i) {
            el.addEventListener('click', function () { openLb(i); });
        });
    }).catch(function (e) {
        
    });
})();

/* === catalog.js === */
/* Рендер каталогу знімка з data/products.json.
   Використання: <div id="json-catalog" data-category="33"></div> — category "33" = всі товари (коренева). */
(function () {
    var box = document.getElementById('json-catalog');
    if (!box) return;

    var catId = parseInt(box.getAttribute('data-category'), 10);
    var PER_PAGE = 12;
    var PROD = 'https://hydrophob.net.ua/';

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        var ext = path.substring(dot + 1);
        var base = path.substring(0, dot);
        return PROD + 'image/cache/' + encodeURI(base) + '-' + size + 'x' + size + '.' + ext;
    }

    function fmtPrice(p) {
        var n = Math.round(parseFloat(p));
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }

    var badgesMap = {};
    function cardHtml(p) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product-' + p.product_id + '.html';
        var badges = badgesMap[String(p.product_id)] || [];
        var badgesHtml = badges.length
            ? '<div class="product__item-badges">' + badges.map(function (b) {
                var label = b === 'sale' ? 'Акція' : (b === 'new' ? 'Новинка' : 'Топ');
                return '<span class="product__item-badge product__item-badge--' + b + '">' + label + '</span>';
            }).join('') + '</div>'
            : '';
        return '<div class="product__item">' +
            '<div class="product__item-media">' +
            '<a class="product__item-image" href="' + href + '">' +
            '<img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'">' +
            '</a>' + badgesHtml +
            '<button type="button" class="product__item-wish" title="Додати до обраного" aria-label="Додати до обраного">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0l-1.4 1.4-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4l8.8 8.8 8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg>' +
            '</button>' +
            '</div>' +
            '<div class="product__item-content">' +
            '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
            '<p class="product__item-price">' + fmtPrice(p.price) + '</p>' +
            '<button class="product__item-add btn-2" type="button">В кошик' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.56706 2.90039H6.90039V6.90039L2.90039 6.90039V9.56706H6.90039V13.5671H9.56706V9.56706H13.5671V6.90039L9.56706 6.90039V2.90039Z" fill="white"/></svg>' +
            '</button>' +
            '</div>' +
            '</div>';
    }

    function render(products, page) {
        var pages = Math.max(1, Math.ceil(products.length / PER_PAGE));
        page = Math.min(Math.max(1, page), pages);
        var slice = products.slice((page - 1) * PER_PAGE, page * PER_PAGE);

        box.innerHTML = slice.map(cardHtml).join('');

        var pag = document.getElementById('json-pagination');
        if (pag) {
            var from = products.length ? (page - 1) * PER_PAGE + 1 : 0;
            var to = Math.min(page * PER_PAGE, products.length);
            var results = '<p class="cui-results">Показано з ' + from + ' по ' + to + ' із ' + products.length + ' (сторінок: ' + pages + ')</p>';
            if (pages <= 1) { pag.innerHTML = results; return; }
            var html = '<ul class="pagination">';
            for (var i = 1; i <= pages; i++) {
                html += i === page
                    ? '<li class="active"><a>' + i + '</a></li>'
                    : '<li><a href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            html += '</ul>' + results;
            pag.innerHTML = html;
            pag.querySelectorAll('a[data-page]').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    render(products, parseInt(this.getAttribute('data-page'), 10));
                    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        }
    }

    Promise.all([
        fetch('data/products.json').then(function (r) { return r.json(); }),
        fetch('data/home.json').then(function (r) { return r.json(); }).catch(function () { return {}; })
    ])
        .then(function (res) {
            var all = res[0];
            badgesMap = (res[1] || {}).badges || {};
            var products = isNaN(catId)
                ? all
                : all.filter(function (p) { return p.categories.indexOf(catId) !== -1; });
            if (!products.length) products = all; // коренева "Каталог" (33): товари привʼязані до підкатегорій
            render(products, 1);
        })
        .catch(function (e) {
            box.innerHTML = '<p>Не вдалося завантажити товари (' + e + ')</p>';
        });
})();

/* === catalog-ui.js === */
/* Редизайн каталогу знімка:
   - тулбар: кнопка «Фільтри» + сортування + перемикач сітки 4/2;
   - drawer (виїжджає справа): фільтри акордеонами, початково згорнуті;
   - сайдбар праворуч: список категорій + популярні товари з data/*.json. */
(function () {
    var PROD = 'https://hydrophob.net.ua/';
    var body = document.querySelector('.catalog__body');
    var filters = document.querySelector('.catalog__filters');
    if (!body || !filters) return;

    document.body.classList.add('cui');

    /* --- розбираємо старий блок фільтрів --- */
    var sortSelect = filters.querySelector('#input-sort');
    var catSelect = filters.querySelector('#category-select');
    var priceForm = filters.querySelector('.catalog__price');
    var searchForm = filters.querySelector('.catalog__search');
    filters.remove();

    /* --- drawer --- */
    var overlay = document.createElement('div');
    overlay.className = 'cui-overlay';
    document.body.appendChild(overlay);

    var drawer = document.createElement('aside');
    drawer.className = 'cui-drawer';
    drawer.innerHTML = '<div class="cui-drawer__head"><p>Фільтри</p>' +
        '<button type="button" class="cui-drawer__close" aria-label="Закрити">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>' +
        '</button></div>' +
        '<div class="cui-drawer__body"></div>';
    document.body.appendChild(drawer);
    var drawerBody = drawer.querySelector('.cui-drawer__body');

    function accordion(title, node) {
        if (!node) return;
        var group = document.createElement('div');
        group.className = 'cui-group';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cui-group__btn';
        btn.innerHTML = '<span>' + title + '</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        var content = document.createElement('div');
        content.className = 'cui-group__body';
        content.hidden = true; /* початково згорнуто */
        content.appendChild(node);
        btn.addEventListener('click', function () {
            var open = group.classList.toggle('is-open');
            content.hidden = !open;
        });
        group.appendChild(btn);
        group.appendChild(content);
        drawerBody.appendChild(group);
    }

    accordion('Категорія', catSelect);
    accordion('Ціна', priceForm);
    accordion('Пошук', searchForm);

    /* --- тулбар: фільтри + сортування + перемикач сітки --- */
    var toolbar = document.createElement('div');
    toolbar.className = 'cui-toolbar';

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'cui-filter-btn';
    trigger.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5h10M11 9h7M11 13h4M3 17l3 3 3-3M6 18V4"/></svg> Фільтри';
    toolbar.appendChild(trigger);

    if (sortSelect) {
        sortSelect.classList.add('cui-sort');
        toolbar.appendChild(sortSelect);
    }

    var gridBox = document.createElement('div');
    gridBox.className = 'cui-grid-toggle';
    gridBox.innerHTML =
        '<button type="button" class="cui-grid-btn" data-cols="4" aria-label="По чотири" title="По чотири">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></button>' +
        '<button type="button" class="cui-grid-btn" data-cols="2" aria-label="По два" title="По два">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="18"/><rect x="13" y="3" width="8" height="18"/></svg></button>';
    toolbar.appendChild(gridBox);

    body.parentNode.insertBefore(toolbar, body);

    /* сітка 4/2 */
    var grid = document.querySelector('.catalog__content') || document.getElementById('json-catalog');
    function setCols(n) {
        if (!grid) return;
        grid.classList.toggle('cui-cols-2', n === 2);
        gridBox.querySelectorAll('.cui-grid-btn').forEach(function (b) {
            b.classList.toggle('is-active', parseInt(b.getAttribute('data-cols'), 10) === n);
        });
        try { localStorage.setItem('hydro_grid', n); } catch (e) {}
    }
    gridBox.querySelectorAll('.cui-grid-btn').forEach(function (b) {
        b.addEventListener('click', function () { setCols(parseInt(b.getAttribute('data-cols'), 10)); });
    });
    var saved = 4;
    try { saved = parseInt(localStorage.getItem('hydro_grid'), 10) || 4; } catch (e) {}
    setCols(saved === 2 ? 2 : 4);

    function open() { document.body.classList.add('cui-drawer-open'); }
    function close() { document.body.classList.remove('cui-drawer-open'); }
    trigger.addEventListener('click', open);
    overlay.addEventListener('click', close);
    drawer.querySelector('.cui-drawer__close').addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    /* --- сайдбар праворуч --- */
    body.classList.add('cui-body');
    var aside = body.querySelector('.catalog__aside');
    if (!aside) {
        aside = document.createElement('aside');
        aside.className = 'catalog__aside';
        body.appendChild(aside);
    }
    aside.classList.add('cui-aside');

    var oldToggle = document.querySelector('.catalog__aside-toggle');
    if (oldToggle) oldToggle.remove();

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }

    Promise.all([
        fetch('data/categories.json').then(function (r) { return r.json(); }),
        fetch('data/products.json').then(function (r) { return r.json(); }),
        fetch('data/home.json').then(function (r) { return r.json(); })
    ]).then(function (res) {
        var cats = res[0], products = res[1], home = res[2];
        var byId = {};
        products.forEach(function (p) { byId[p.product_id] = p; });

        var current = new URLSearchParams(location.search).get('id');

        var catList = cats
            .filter(function (c) { return String(c.category_id) !== '33'; })
            .map(function (c) {
                var name = (c.translations['uk-ua'] || {}).name || '';
                var active = String(c.category_id) === current ? ' is-active' : '';
                return '<a class="catalog__filter-item' + active + '" href="catalog.php?id=' + c.category_id + '">' + name + '</a>';
            }).join('');

        var popular = (home.popular || []).map(function (id) {
            var p = byId[id];
            if (!p) return '';
            var name = (p.translations['uk-ua'] || {}).name || '';
            return '<a class="cui-pop" href="product.php?id=' + p.product_id + '">' +
                '<img class="cui-pop__img" src="' + cacheImg(p.image, 200) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'">' +
                '<span class="cui-pop__meta"><span class="cui-pop__name">' + name + '</span>' +
                '<span class="cui-pop__price">' + fmt(p.price) + '</span></span>' +
                '</a>';
        }).join('');

        aside.innerHTML =
            '<div class="catalog__filter"><p class="catalog__filter-title">Категорії</p>' +
            '<div class="catalog__filter-list"><a class="catalog__filter-item' + (current === '33' || !current ? ' is-active' : '') + '" href="catalog.php">Всі товари</a>' + catList + '</div></div>' +
            (popular ? '<div class="catalog__filter"><p class="catalog__filter-title">Популярні товари</p>' +
                '<div class="cui-pop-list">' + popular + '</div></div>' : '');
    }).catch(function () {});
})();

/* === product-ui.js === */
/* Сторінка товару знімка: collapse опису + секції «З цим купують» / FAQ / «Переглянуті» (стилі hm-*). */
(function () {
    var PROD = 'https://hydrophob.net.ua/';
    var pm = (location.pathname + location.search).match(/product\.php\?.*\bid=(\d+)/);
    if (!pm) return;
    var currentId = parseInt(pm[1], 10);

    /* --- collapse опису товару --- */
    var descBox = document.querySelector('.product__result-content.active');
    if (descBox && descBox.scrollHeight > 420) {
        descBox.classList.add('pui-collapsible');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'desc-toggle';
        var arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
        btn.innerHTML = '<span>Показати більше</span>' + arrow;
        descBox.parentNode.insertBefore(btn, descBox.nextSibling);
        btn.addEventListener('click', function () {
            var open = descBox.classList.toggle('is-open');
            btn.classList.toggle('is-open', open);
            btn.querySelector('span').textContent = open ? 'Згорнути' : 'Показати більше';
        });
        /* кнопка стосується лише вкладки з описом — на інших вкладках її ховаємо */
        var tabs = document.querySelectorAll('.product__selects-btn');
        tabs.forEach(function (tab, ti) {
            tab.addEventListener('click', function () {
                btn.style.display = ti === 0 ? '' : 'none';
            });
        });
    }

    /* --- секції --- */
    var main = document.querySelector('main');
    if (!main) return;
    var mount = document.createElement('div');
    main.appendChild(mount);

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }
    var badgesMap = {};
    function badgeHtml(id) {
        var list = badgesMap[String(id)] || [];
        if (!list.length) return '';
        return '<div class="product__item-badges">' + list.map(function (b) {
            var label = b === 'sale' ? 'Акція' : (b === 'new' ? 'Новинка' : 'Топ');
            return '<span class="product__item-badge product__item-badge--' + b + '">' + label + '</span>';
        }).join('') + '</div>';
    }
    function card(p) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product.php?id=' + p.product_id;
        return '<div class="product__item hm-slide">' +
            '<div class="product__item-media">' +
            '<a class="product__item-image" href="' + href + '"><img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'"></a>' +
            badgeHtml(p.product_id) +
            '<button type="button" class="product__item-wish" title="Додати до обраного" aria-label="Додати до обраного">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0l-1.4 1.4-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4l8.8 8.8 8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg>' +
            '</button>' +
            '</div>' +
            '<div class="product__item-content">' +
            '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
            '<p class="product__item-price">' + fmt(p.price) + '</p>' +
            '<button class="product__item-add btn-2" type="button">В кошик' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.56706 2.90039H6.90039V6.90039L2.90039 6.90039V9.56706H6.90039V13.5671H9.56706V9.56706H13.5671V6.90039L9.56706 6.90039V2.90039Z" fill="white"/></svg>' +
            '</button>' +
            '</div></div>';
    }
    function sliderSection(id, title, cardsHtml) {
        if (!cardsHtml) return '';
        return '<section class="hm-sec" id="' + id + '"><div class="container">' +
            '<h2 class="hm-sec__title page-name">' + title + '</h2>' +
            '<div class="hm-slider-wrap">' +
            '<button type="button" class="hm-arrow hm-arrow--prev" aria-label="Назад"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
            '<div class="hm-slider">' + cardsHtml + '</div>' +
            '<button type="button" class="hm-arrow hm-arrow--next" aria-label="Вперед"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
            '</div></div></section>';
    }

    Promise.all([
        fetch('data/products.json').then(function (r) { return r.json(); }),
        fetch('data/home.json').then(function (r) { return r.json(); })
    ]).then(function (res) {
        var products = res[0], home = res[1];
        var byId = {};
        products.forEach(function (p) { byId[p.product_id] = p; });

        var current = byId[currentId];
        var html = '';
        badgesMap = home.badges || {};

        /* стікери-бейджі поверх фото поточного товару */
        var myBadges = badgesMap[String(currentId)] || [];
        var sliderBox = document.querySelector('.product__slider-content');
        if (sliderBox && myBadges.length) {
            var bl = document.createElement('div');
            bl.className = 'product__item-badges pui-badges';
            bl.innerHTML = myBadges.map(function (b) {
                var label = b === 'sale' ? 'Акція' : (b === 'new' ? 'Новинка' : 'Топ');
                return '<span class="product__item-badge product__item-badge--' + b + '">' + label + '</span>';
            }).join('');
            sliderBox.appendChild(bl);
        }

        /* рядок покупки: зліва лічильник кількості → кнопка «Додати в кошик» → справа «В вибране» */
        var cartBtn = document.getElementById('button-cart');
        var qty = document.querySelector('.product__count');
        if (cartBtn && !document.querySelector('.pui-buyrow')) {
            var row = document.createElement('div');
            row.className = 'pui-buyrow';
            cartBtn.parentNode.insertBefore(row, cartBtn);
            if (qty) row.appendChild(qty);
            row.appendChild(cartBtn);
            var wish = document.createElement('button');
            wish.type = 'button';
            wish.className = 'pui-wish';
            wish.title = 'Додати до обраного';
            wish.setAttribute('aria-label', 'Додати до обраного');
            wish.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0l-1.4 1.4-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4l8.8 8.8 8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg>';
            row.appendChild(wish);
            wish.addEventListener('click', function () { wish.classList.toggle('is-active'); });
        }

        /* теги продукту під зображеннями (ліва колонка) */
        var left = document.querySelector('.product__left');
        if (left && current) {
            var tags = ((current.translations['uk-ua'] || {}).tag || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean);
            if (tags.length) {
                var tagBox = document.createElement('div');
                tagBox.className = 'pui-tags';
                tagBox.innerHTML = '<p class="pui-tags__title">Теги:</p>' + tags.map(function (t) {
                    return '<a class="pui-tag" href="search.php?q=' + encodeURIComponent(t) + '">' + t + '</a>';
                }).join('');
                left.appendChild(tagBox);
            }
        }

        /* З цим товаром також купують: інші товари тієї ж категорії */
        if (current) {
            var related = products.filter(function (p) {
                return p.product_id !== currentId && p.categories.some(function (c) {
                    return current.categories.indexOf(c) !== -1;
                });
            }).slice(0, 8);
            html += sliderSection('related', 'З цим товаром також купують', related.map(card).join(''));
        }

        /* FAQ */
        if (home.faq && home.faq.length) {
            html += '<section class="hm-sec hm-faq-sec" id="faq"><div class="container">' +
                '<h2 class="hm-sec__title page-name">Питання та відповіді</h2>' +
                '<div class="hm-faq">' +
                home.faq.slice(0, 4).map(function (item, i) {
                    return '<div class="hm-faq__item">' +
                        '<button type="button" class="hm-faq__btn" aria-expanded="false">' +
                        '<span>' + item.q + '</span>' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>' +
                        '</button>' +
                        '<div class="hm-faq__answer"><p>' + item.a + '</p></div>' +
                        '</div>';
                }).join('') +
                '</div></div></section>';
        }

        /* Переглянуті (без поточного) */
        var viewed = [];
        try { viewed = JSON.parse(localStorage.getItem('hydro_viewed') || '[]'); } catch (e) {}
        var viewedCards = viewed.filter(function (id) { return id !== currentId; }).map(function (id) {
            var p = byId[id];
            return p ? card(p) : '';
        }).join('');
        html += sliderSection('viewed', 'Переглянуті нещодавно', viewedCards);

        mount.innerHTML = html;

        mount.querySelectorAll('.hm-slider-wrap').forEach(function (wrap) {
            var slider = wrap.querySelector('.hm-slider');
            var step = function () {
                var el = slider.querySelector('.hm-slide');
                return el ? el.getBoundingClientRect().width + 24 : 300;
            };
            wrap.querySelector('.hm-arrow--prev').addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); });
            wrap.querySelector('.hm-arrow--next').addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); });
        });
        mount.querySelectorAll('.hm-faq__btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.parentNode;
                var open = item.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    }).catch(function () {});
})();

/* === search-ui.js === */
/* Пошук знімка: оверлей із затемненням по центру + живий пошук по data/products.json.
   На search.html — повні результати з query-параметра q. */
(function () {
    var PROD = 'https://hydrophob.net.ua/';

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }
    function norm(s) {
        return (s || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }
    var productsPromise = null;
    function loadProducts() {
        if (!productsPromise) productsPromise = fetch('data/products.json').then(function (r) { return r.json(); });
        return productsPromise;
    }
    function search(products, q) {
        q = norm(q);
        if (!q) return [];
        var words = q.split(' ');
        return products.filter(function (p) {
            var hay = norm((p.translations['uk-ua'] || {}).name + ' ' + (p.model || ''));
            return words.every(function (w) { return hay.indexOf(w) !== -1; });
        });
    }

    /* --- оверлей --- */
    var openBtn = document.querySelector('.header__search-open');
    if (openBtn) {
        var ov = document.createElement('div');
        ov.className = 'su-overlay';
        ov.innerHTML = '<div class="su-box">' +
            '<button type="button" class="su-close" aria-label="Закрити">' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>' +
            '<form class="header__search-form su-native" action="search.php" method="get">' +
            '<input class="header__search-input su-input-native" type="text" name="q" placeholder="Пошук" autocomplete="off" required>' +
            '<button class="header__search-btn" type="submit" aria-label="Шукати"></button>' +
            '</form>' +
            '<div class="su-results"></div>' +
            '<a class="su-all" href="#" hidden>Всі результати</a>' +
            '</div>';
        document.body.appendChild(ov);

        var input = ov.querySelector('.su-input-native');
        var results = ov.querySelector('.su-results');
        var allLink = ov.querySelector('.su-all');

        function openOv(e) {
            if (e) e.preventDefault();
            ov.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { input.focus(); }, 50);
        }
        function closeOv() {
            ov.classList.remove('is-open');
            document.body.style.overflow = '';
        }
        openBtn.addEventListener('click', openOv);
        ov.querySelector('.su-close').addEventListener('click', closeOv);
        ov.addEventListener('click', function (e) { if (e.target === ov) closeOv(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeOv(); });

        var t = null;
        input.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(function () {
                var q = input.value;
                loadProducts().then(function (products) {
                    var found = search(products, q);
                    if (!norm(q)) { results.innerHTML = ''; allLink.hidden = true; return; }
                    results.innerHTML = found.slice(0, 6).map(function (p) {
                        var name = (p.translations['uk-ua'] || {}).name || '';
                        return '<a class="su-item" href="product.php?id=' + p.product_id + '">' +
                            '<img src="' + cacheImg(p.image, 200) + '" alt="" loading="lazy">' +
                            '<span class="su-item__name">' + name + '</span>' +
                            '<span class="su-item__price">' + fmt(p.price) + '</span></a>';
                    }).join('') || '<p class="su-empty">Нічого не знайдено</p>';
                    allLink.hidden = found.length <= 6;
                    allLink.textContent = 'Всі результати (' + found.length + ')';
                    allLink.href = 'search.php?q=' + encodeURIComponent(q);
                });
            }, 200);
        });
    }

    /* --- сторінка search.html --- */
    var page = document.getElementById('search-page-results');
    if (page) {
        var params = new URLSearchParams(location.search);
        var q = params.get('q') || '';
        var qInput = document.getElementById('search-page-input');
        if (qInput) qInput.value = q;
        var title = document.getElementById('search-page-count');

        loadProducts().then(function (products) {
            var found = q ? search(products, q) : [];
            if (title) title.textContent = q ? 'Знайдено: ' + found.length + ' за запитом «' + q + '»' : 'Введіть пошуковий запит';
            page.innerHTML = found.map(function (p) {
                var name = (p.translations['uk-ua'] || {}).name || '';
                var href = 'product.php?id=' + p.product_id;
                return '<div class="product__item">' +
                    '<div class="product__item-media"><a class="product__item-image" href="' + href + '">' +
                    '<img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy"></a></div>' +
                    '<div class="product__item-content">' +
                    '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
                    '<p class="product__item-price">' + fmt(p.price) + '</p>' +
                    '</div></div>';
            }).join('');
        });
    }
})();

/* === crosslinks.js === */
/* Кнопки-перелінковки між сторінками знімка — блок перед футером залежно від типу сторінки. */
(function () {
    var path = location.pathname.split('/').pop() || 'index.php';
    var links;

    if (/^catalog\.php/.test(path)) {
        links = [
            { href: 'delivery.php', text: 'Доставка та оплата' },
            { href: 'about.php', text: 'Про нас' },
            { href: 'contact.php', text: 'Контакти' }
        ];
    } else if (/^product\.php/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'До каталогу' },
            { href: 'delivery.php', text: 'Доставка та оплата' },
            { href: 'contact.php', text: 'Контакти' }
        ];
    } else if (/^(about|privacy|terms|returns|delivery|search)\.php/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'Перейти в каталог' },
            { href: 'index.php', text: 'На головну' },
            { href: 'contact.php', text: 'Контакти' }
        ];
    } else if (/^contact\.php/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'Перейти в каталог' },
            { href: 'delivery.php', text: 'Доставка та оплата' }
        ];
    } else if (/^(account|login|register|edit|address|order|wishlist)/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'До каталогу' },
            { href: 'index.php', text: 'На головну' }
        ];
    } else {
        return; /* головна — там своя навігація секціями */
    }

    var main = document.querySelector('main');
    if (!main) return;

    var html = '<section class="xl-nav"><div class="container"><div class="xl-nav__row">' +
        links.map(function (l) {
            return '<a class="xl-nav__btn" href="' + l.href + '">' + l.text +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></a>';
        }).join('') +
        '</div></div></section>';
    main.insertAdjacentHTML('beforeend', html);
})();
