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
        var btn = slide.querySelector('[data-hero-sound]');
        if (btn) {
            /* SVG не має властивості .hidden (лише HTMLElement) — тільки атрибутом */
            var iconOff = btn.querySelector('.hero__sound-off');
            var iconOn = btn.querySelector('.hero__sound-on');
            if (soundOn) { iconOff.setAttribute('hidden', ''); } else { iconOff.removeAttribute('hidden'); }
            if (soundOn) { iconOn.removeAttribute('hidden'); } else { iconOn.setAttribute('hidden', ''); }
            btn.setAttribute('aria-label', soundOn ? 'Вимкнути звук' : 'Увімкнути звук');
            btn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
        }
        if (v) v.muted = !soundOn;
    }

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var typeTimers = new Map();

    function animateSlideText(slide) {
        if (reduceMotion) return;
        var title = slide.querySelector('.hero__title');
        var descr = slide.querySelector('.hero__descr');
        if (title) {
            title.classList.remove('is-rise');
            void title.offsetWidth;
            title.classList.add('is-rise');
        }
        if (descr) {
            if (!descr.dataset.full) descr.dataset.full = descr.textContent;
            var full = descr.dataset.full;
            var prev = typeTimers.get(descr);
            if (prev) clearTimeout(prev);
            descr.textContent = '';
            var i = 0;
            (function step() {
                i += 1;
                descr.textContent = full.slice(0, i);
                if (i < full.length) typeTimers.set(descr, setTimeout(step, 35));
            })();
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
        animateSlideText(slide);
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
    animateSlideText(slides[0]);

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
        return PROD + 'image/' + encodeURI(path);
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
    var PER_PAGE = document.querySelector('.catalog__banner') ? 14 : 12;
    var PROD = 'https://hydrophob.net.ua/';

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        var ext = path.substring(dot + 1);
        var base = path.substring(0, dot);
        return PROD + 'image/' + encodeURI(path);
    }

    function fmtPrice(p) {
        var n = Math.round(parseFloat(p));
        return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }

    var badgesMap = {};
    function cardHtml(p) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product.php?id=' + p.product_id;
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

    /* серверні рекламні банери: зберігаємо і вставляємо після кожного 8-го товару */
    var bannerHtml = [].slice.call(box.querySelectorAll('.catalog__banner')).map(function (b) { return b.outerHTML; });

    function render(products, page) {
        var pages = Math.max(1, Math.ceil(products.length / PER_PAGE));
        page = Math.min(Math.max(1, page), pages);
        var slice = products.slice((page - 1) * PER_PAGE, page * PER_PAGE);

        var html = '';
        var bi = 0;
        slice.forEach(function (p, i) {
            html += cardHtml(p);
            if ((i + 1) % 8 === 0 && bannerHtml[bi]) { html += bannerHtml[bi++]; }
        });
        box.innerHTML = html;

        var pag = document.getElementById('json-pagination');
        if (pag) {
            var from = products.length ? (page - 1) * PER_PAGE + 1 : 0;
            var to = Math.min(page * PER_PAGE, products.length);
            var results = '<p class="cui-results">Показано з ' + from + ' по ' + to + ' із ' + products.length + ' (сторінок: ' + pages + ')</p>';
            if (pages <= 1) { pag.innerHTML = ''; return; }
            var arrowPrev = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
            var arrowNext = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';
            var html = '<ul class="pagination">';
            html += page > 1
                ? '<li><a href="#" class="prev" data-page="' + (page - 1) + '" aria-label="Попередня">' + arrowPrev + '</a></li>'
                : '<li class="is-disabled"><a class="prev" aria-hidden="true">' + arrowPrev + '</a></li>';
            for (var i = 1; i <= pages; i++) {
                html += i === page
                    ? '<li class="active"><a>' + i + '</a></li>'
                    : '<li><a href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            html += page < pages
                ? '<li><a href="#" class="next" data-page="' + (page + 1) + '" aria-label="Наступна">' + arrowNext + '</a></li>'
                : '<li class="is-disabled"><a class="next" aria-hidden="true">' + arrowNext + '</a></li>';
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
        return PROD + 'image/' + encodeURI(path);
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

        /* --- фільтри в стилі hydrophob.net: групи-акордеони з чекбоксами --- */
        function nameOf(p) {
            return ((p.translations['uk-ua'] || {}).name || '') + ' ' + (p.model || '');
        }
        var TYPE_DEFS = [
            ['sprey', 'Спрей', /спрей/i],
            ['pokryttia', 'Керамічне покриття', /покриття|керамі/i],
            ['nabir', 'Набір', /набір|набор/i],
            ['aplikator', 'Аплікатор', /аплікатор/i],
            ['mikrofibra', 'Мікрофібра і серветки', /мікрофібра|серветк/i],
            ['aktyvator', 'Знежирювач і активатор', /знежирювач|активатор/i],
            ['ochysnyk', 'Очисник', /очисник|очист/i]
        ];
        var VOL_DEFS = [
            ['50', '50 мл', /50\s*мл|50\s*ml/i],
            ['100', '100 мл', /100\s*мл|100\s*ml/i],
            ['250', '250 мл', /250\s*мл|250\s*ml/i],
            ['500', '500 мл', /500\s*(мл|ml)/i],
            ['big', '1 л і більше', /\b(1|10)\s*л|litr|літр/i]
        ];
        function countBy(defs) {
            return defs.map(function (d) {
                var n = products.filter(function (p) { return d[2].test(nameOf(p)); }).length;
                return [d[0], d[1], d[2], n];
            }).filter(function (d) { return d[3] > 0; });
        }
        var typeDefs = countBy(TYPE_DEFS);
        var volDefs = countBy(VOL_DEFS);

        function checkGroup(title, defs, group, opened) {
            if (!defs.length) return '';
            return '<div class="hpf' + (opened ? ' is-open' : '') + '" data-hpf>' +
                '<button type="button" class="hpf__toggle" data-hpf-toggle aria-expanded="' + (opened ? 'true' : 'false') + '">' + title +
                '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>' +
                '<div class="hpf__body">' +
                defs.map(function (d) {
                    return '<label class="hpf__item">' +
                        '<input type="checkbox" class="hpf__check" data-hpf-filter="' + group + '" value="' + d[0] + '">' +
                        '<i class="hpf__box" aria-hidden="true"></i>' +
                        '<span class="hpf__label">' + d[1] + ' <span class="hpf__count">(' + d[3] + ')</span></span>' +
                        '</label>';
                }).join('') +
                '</div></div>';
        }

        var catLinks = '<div class="hpf is-open" data-hpf>' +
            '<button type="button" class="hpf__toggle" data-hpf-toggle aria-expanded="true">Категорії' +
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>' +
            '<div class="hpf__body">' +
            '<a class="catalog__filter-item' + (current === '33' || !current ? ' is-active' : '') + '" href="catalog.php">Всі товари</a>' + catList +
            '</div></div>';

        var catDescBlock = aside.querySelector('[data-cat-desc]');
        var catDescHtml = catDescBlock ? catDescBlock.outerHTML : '';
        aside.innerHTML =
            catLinks +
            (popular ? '<div class="catalog__filter"><p class="catalog__filter-title">Популярні товари</p>' +
                '<div class="cui-pop-list">' + popular + '</div></div>' : '') +
            catDescHtml;

        /* фільтри (Тип товару / Обʼєм) — у виїзний drawer */
        var drawerBody = document.querySelector('.cui-drawer__body');
        if (drawerBody) {
            drawerBody.insertAdjacentHTML('beforeend',
                checkGroup('Тип товару', typeDefs, 'type', true) +
                checkGroup('Обʼєм', volDefs, 'vol', true));
        }
        var filterScope = drawerBody || aside;

        /* акордеони (aside: Категорії; drawer: чекбокс-групи) */
        [aside, drawerBody].forEach(function (scope) {
            if (!scope) return;
            scope.addEventListener('click', function (e) {
                var t = e.target.closest('[data-hpf-toggle]');
                if (!t) return;
                var g = t.closest('[data-hpf]');
                var open = g.classList.toggle('is-open');
                t.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });

        /* фільтрація чекбоксами */
        var activeFilters = { type: [], vol: [] };
        filterScope.addEventListener('change', function (e) {
            var cb = e.target.closest('[data-hpf-filter]');
            if (!cb) return;
            var group = cb.getAttribute('data-hpf-filter');
            activeFilters[group] = [].slice.call(filterScope.querySelectorAll('[data-hpf-filter="' + group + '"]:checked')).map(function (c) { return c.value; });
            applyFilters();
        });

        var allDefs = { type: typeDefs, vol: volDefs };
        function applyFilters() {
            var filtered = products.filter(function (p) {
                return ['type', 'vol'].every(function (group) {
                    var act = activeFilters[group];
                    if (!act.length) return true;
                    return act.some(function (val) {
                        var def = allDefs[group].find(function (d) { return d[0] === val; });
                        return def && def[2].test(nameOf(p));
                    });
                });
            });
            render(filtered, 1);
        }
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
    var rvTeaser = main.querySelector('.rv-teaser');
    if (rvTeaser) { main.insertBefore(mount, rvTeaser); } else { main.appendChild(mount); }

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/' + encodeURI(path);
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
            /* toggle обраного — делегований обробник у wishlist-cosmetic.js */
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

        /* FAQ / related / viewed рендеряться серверно в product.php
           розміткою шаблону шопу (product__faq / product__related / product__viewed). */
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
        return PROD + 'image/' + encodeURI(path);
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

/* === otp-modal.js === */
/* Спільна модалка коду підтвердження — одна на весь сайт.
   Будь-яка кнопка [data-code-trigger] відкриває її: бере email зі свого
   scope (найближчі form/section/.account__panel), «надсилає» код (у макеті —
   миттєво) і показує 6-клітинкове поле вводу. Немає реального бекенду —
   підтвердження спрацьовує щойно всі 6 цифр заповнені. */
(function () {
    var modal = document.querySelector('[data-code-modal]');
    if (!modal) return;

    var mailEl = modal.querySelector('[data-code-mail]');
    var titleEl = modal.querySelector('[data-code-title]');
    var errEl = modal.querySelector('[data-code-error]');
    var cells = [].slice.call(modal.querySelectorAll('[data-code-cell]'));
    var confirmBtn = modal.querySelector('[data-code-confirm]');
    var timerEl = modal.querySelector('[data-code-timer]');
    var resendBtn = modal.querySelector('[data-code-resend]');
    var defaultTitle = titleEl ? titleEl.textContent : '';
    var timerId = null;
    var seconds = 59;
    var ctx = null;

    function showError(message) {
        if (!errEl) return;
        errEl.hidden = !message;
        errEl.textContent = message || '';
    }

    function syncConfirm() {
        var filled = cells.every(function (c) { return c.value !== ''; });
        confirmBtn.classList.toggle('is-muted', !filled);
        confirmBtn.setAttribute('aria-disabled', String(!filled));
    }

    function tick() {
        seconds -= 1;
        if (seconds <= 0) {
            window.clearInterval(timerId);
            timerEl.hidden = true;
            resendBtn.hidden = false;
            return;
        }
        timerEl.textContent = 'Надіслати код повторно через 00:' + String(seconds).padStart(2, '0');
    }

    function startTimer() {
        window.clearInterval(timerId);
        seconds = 59;
        timerEl.hidden = false;
        resendBtn.hidden = true;
        timerEl.textContent = 'Надіслати код повторно через 00:' + String(seconds).padStart(2, '0');
        timerId = window.setInterval(tick, 1000);
    }

    function open(context) {
        ctx = context;
        if (mailEl) mailEl.textContent = context.email || 'ваш email';
        if (titleEl) titleEl.textContent = context.title || defaultTitle;
        cells.forEach(function (c) { c.value = ''; });
        showError('');
        syncConfirm();
        modal.hidden = false;
        startTimer();
        if (cells[0]) cells[0].focus();
    }

    function close() {
        modal.hidden = true;
        window.clearInterval(timerId);
    }

    cells.forEach(function (cell, index) {
        cell.addEventListener('input', function () {
            cell.value = cell.value.replace(/\D/g, '').slice(-1);
            if (cell.value && index < cells.length - 1) cells[index + 1].focus();
            syncConfirm();
        });
        cell.addEventListener('keydown', function (event) {
            if (event.key === 'Backspace' && !cell.value && index > 0) cells[index - 1].focus();
        });
        cell.addEventListener('paste', function (event) {
            var text = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (!text) return;
            event.preventDefault();
            cells.forEach(function (c, i) { c.value = text[i] || ''; });
            syncConfirm();
            var last = cells[Math.min(text.length, cells.length) - 1];
            if (last) last.focus();
        });
    });

    confirmBtn.addEventListener('click', function () {
        if (confirmBtn.getAttribute('aria-disabled') === 'true' || !ctx) return;
        close();
        if (ctx.onConfirm) { ctx.onConfirm(); return; }
        window.location.href = ctx.redirect || 'account.php';
    });

    resendBtn.addEventListener('click', function () {
        showError('');
        startTimer();
    });

    var closeBtn = modal.querySelector('[data-modal-close]');
    if (closeBtn) closeBtn.addEventListener('click', close);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) close();
    });

    document.querySelectorAll('[data-code-trigger]').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            var scope = trigger.closest('form, section, .account__panel') || document;
            var emailField = scope.querySelector('[data-code-email]');
            var email = emailField ? emailField.value.trim() : '';

            if (!email) {
                if (emailField) { emailField.classList.add('is-invalid'); emailField.focus(); }
                return;
            }

            open({
                email: email,
                title: trigger.getAttribute('data-code-title'),
                redirect: trigger.getAttribute('data-code-redirect'),
                onConfirm: trigger.dataset.codeInline === '1' ? function () {
                    var target = document.querySelector(trigger.getAttribute('data-code-update'));
                    if (target) {
                        target.value = email;
                        target.setAttribute('data-email-original', email);
                    }
                    trigger.disabled = true;
                } : null
            });
        });
    });
})();

/* === edit-email.js === */
/* Поле email на edit.php завжди редаговане; кнопка "Надіслати код" поряд з
   ним активна лише коли значення справді відрізняється від початкового. */
(function () {
    document.querySelectorAll('[data-email-send-btn]').forEach(function (sendBtn) {
        var field = sendBtn.closest('.account__field-with-btn').querySelector('input[type="email"]');
        if (!field) return;
        var original = field.getAttribute('data-email-original') || field.value;

        field.addEventListener('input', function () {
            sendBtn.disabled = field.value.trim() === original.trim() || field.value.trim() === '';
        });
    });
})();

/* === about-sections.js === */
/* Сторінка "Про нас": після основного блоку — FAQ і "Переглянуті нещодавно"
   (та сама розмітка hm-sec, що на головній і сторінці товару). */
(function () {
    var PROD = 'https://hydrophob.net.ua/';
    var aboutSec = document.querySelector('main .about');
    if (!aboutSec) return;
    var main = document.querySelector('main');

    function cacheImg(path) {
        if (!path) return PROD + 'image/placeholder.png';
        return PROD + 'image/' + encodeURI(path);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }
    function card(p) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product.php?id=' + p.product_id;
        return '<div class="product__item hm-slide">' +
            '<div class="product__item-media">' +
            '<a class="product__item-image" href="' + href + '"><img src="' + cacheImg(p.image) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'"></a>' +
            '</div>' +
            '<div class="product__item-content">' +
            '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
            '<p class="product__item-price">' + fmt(p.price) + '</p>' +
            '<button class="product__item-add btn-2" type="button">В кошик' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.56706 2.90039H6.90039V6.90039L2.90039 6.90039V9.56706H6.90039V13.5671H9.56706V9.56706H13.5671V6.90039L9.56706 6.90039V2.90039Z" fill="white"/></svg>' +
            '</button>' +
            '</div></div>';
    }

    Promise.all([
        fetch('data/home.json').then(function (r) { return r.json(); }),
        fetch('data/products.json').then(function (r) { return r.json(); })
    ]).then(function (res) {
        var home = res[0], products = res[1];
        var byId = {};
        products.forEach(function (p) { byId[p.product_id] = p; });
        var html = '';

        if (home.faq && home.faq.length) {
            html += '<section class="hm-sec hm-faq-sec" id="faq"><div class="container">' +
                '<h2 class="hm-sec__title page-name">Питання та відповіді</h2>' +
                '<div class="hm-faq">' +
                home.faq.slice(0, 5).map(function (item, i) {
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

        var viewed = [];
        try { viewed = JSON.parse(localStorage.getItem('hydro_viewed') || '[]'); } catch (e) {}
        var viewedCards = viewed.map(function (id) {
            var p = byId[id];
            return p ? card(p) : '';
        }).join('');
        if (viewedCards) {
            html += '<section class="hm-sec" id="viewed"><div class="container">' +
                '<h2 class="hm-sec__title page-name">Переглянуті нещодавно</h2>' +
                '<div class="hm-slider-wrap">' +
                '<button type="button" class="hm-arrow hm-arrow--prev" aria-label="Назад"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
                '<div class="hm-slider">' + viewedCards + '</div>' +
                '<button type="button" class="hm-arrow hm-arrow--next" aria-label="Вперед"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
                '</div></div></section>';
        }

        if (!html) return;
        var mount = document.createElement('div');
        mount.innerHTML = html;
        var cta = main.querySelector('.about-cta');
        if (cta) { main.insertBefore(mount, cta); } else { main.appendChild(mount); }

        mount.querySelectorAll('.hm-faq__btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.parentNode;
                var open = item.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
        mount.querySelectorAll('.hm-slider-wrap').forEach(function (wrap) {
            var slider = wrap.querySelector('.hm-slider');
            var step = function () {
                var el = slider.querySelector('.hm-slide');
                return el ? el.getBoundingClientRect().width + 24 : 300;
            };
            wrap.querySelector('.hm-arrow--prev').addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); });
            wrap.querySelector('.hm-arrow--next').addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); });
        });
    }).catch(function () {});
})();

/* === cart-cosmetic.js === */
/* Косметичний кошик знімка: товари в localStorage, бейдж на іконці в шапці,
   наповнення мінікошика розміткою шопу (busket__item) і фідбек на кнопках.
   Реального бекенду немає. */
(function () {
    var KEY = 'hydro_cart_items';
    var busket = document.querySelector('.header__busket');
    var miniList = document.querySelector('#cart .busket__inner');
    var checkoutList = document.querySelector('#checkout-busket');

    function getItems() {
        try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; }
    }
    function setItems(items) {
        try { localStorage.setItem(KEY, JSON.stringify(items)); } catch (e) {}
        render();
    }
    function count(items) {
        return items.reduce(function (s, it) { return s + it.qty; }, 0);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }

    function renderBadge(items) {
        if (!busket) return;
        var n = count(items);
        var badge = busket.querySelector('.hc-badge');
        if (!n) { if (badge) badge.remove(); return; }
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'hc-badge';
            busket.appendChild(badge);
        }
        badge.textContent = n > 99 ? '99+' : String(n);
        badge.classList.remove('is-bump');
        void badge.offsetWidth;
        badge.classList.add('is-bump');
    }

    function renderMini(items) {
        renderInto(miniList, items, true);
        renderInto(checkoutList, items, false);
    }

    function renderInto(target, items, withCheckoutLink) {
        if (!target) return;
        if (!items.length) {
            target.innerHTML = '<li><p class="busket__empty">Ваш кошик порожній!</p></li>';
            return;
        }
        var total = items.reduce(function (s, it) { return s + it.price * it.qty; }, 0);
        var rows = items.map(function (it, i) {
            return '<div class="busket__item" data-cart-index="' + i + '">' +
                '<a class="busket__item-image" href="' + it.href + '">' +
                (it.img ? '<img src="' + it.img + '" alt="">' : '') +
                '</a>' +
                '<div class="busket__item-content">' +
                '<div class="busket__item-top">' +
                '<a class="busket__item-title" href="' + it.href + '"><h3>' + it.name + '</h3></a>' +
                '</div>' +
                '<div class="busket__item-bottom">' +
                '<div class="busket__item-count">' +
                '<button class="busket__item-minus" data-cart-minus type="button"></button>' +
                '<span>' + it.qty + '</span>' +
                '<button class="busket__item-plus" data-cart-plus type="button"></button>' +
                '</div>' +
                '<p class="busket__item-price">' + fmt(it.price * it.qty) + '</p>' +
                '</div>' +
                '</div>' +
                '<button class="busket__item-delete" data-cart-delete type="button" title="Видалити"></button>' +
                '</div>';
        }).join('');
        target.innerHTML =
            '<li><p class="busket__amount">Товарів: ' + count(items) + '</p></li>' +
            '<li><div class="busket__block"><div class="table table-striped">' + rows + '</div></div></li>' +
            '<li><div class="busket__bottom">' +
            '<div class="busket__bottom-content"><div class="busket__bottom">' +
            '<div class="busket__bottom-item"><p class="busket__bottom-descr busket__bottom-total-title">Разом</p>' +
            '<p class="busket__bottom-descr busket__bottom-total">' + fmt(total) + '</p></div>' +
            '</div></div>' +
            (withCheckoutLink ? '<div class="busket__bottom-confirm"><a class="busket__bottom-btn btn-2" href="checkout.php">Оформити замовлення</a></div>' : '') +
            '</div></li>';
    }

    function render() {
        var items = getItems();
        renderBadge(items);
        renderMini(items);
    }

    function feedback(btn) {
        if (btn.classList.contains('is-added')) return;
        var old = btn.innerHTML;
        btn.classList.add('is-added');
        btn.innerHTML = 'Додано <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
        setTimeout(function () {
            btn.classList.remove('is-added');
            btn.innerHTML = old;
        }, 1400);
    }

    function parsePrice(text) {
        return parseFloat(String(text || '').replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
    }

    function addItem(data, btn) {
        var items = getItems();
        var found = null;
        for (var i = 0; i < items.length; i++) {
            if (items[i].href === data.href) { found = items[i]; break; }
        }
        if (found) { found.qty += data.qty; } else { items.push(data); }
        setItems(items);
        if (btn) feedback(btn);
    }

    /* картки товарів (делеговано — картки рендеряться і динамічно) */
    document.addEventListener('click', function (event) {
        var minus = event.target.closest('[data-cart-minus]');
        var plus = event.target.closest('[data-cart-plus]');
        var del = event.target.closest('[data-cart-delete]');
        if (minus || plus || del) {
            var row = event.target.closest('[data-cart-index]');
            if (!row) return;
            var idx = parseInt(row.getAttribute('data-cart-index'), 10);
            var items = getItems();
            if (!items[idx]) return;
            if (del) { items.splice(idx, 1); }
            else if (plus) { items[idx].qty += 1; }
            else if (minus) {
                items[idx].qty -= 1;
                if (items[idx].qty < 1) items.splice(idx, 1);
            }
            setItems(items);
            return;
        }

        var btn = event.target.closest('.product__item-add');
        if (!btn) return;
        var card = btn.closest('.product__item');
        if (!card) { return; }
        var titleEl = card.querySelector('.product__item-title h3');
        var linkEl = card.querySelector('.product__item-image');
        var imgEl = card.querySelector('.product__item-image img');
        var priceEl = card.querySelector('.product__item-price');
        addItem({
            name: titleEl ? titleEl.textContent.trim() : 'Товар',
            href: linkEl ? linkEl.getAttribute('href') : '#',
            img: imgEl ? imgEl.getAttribute('src') : '',
            price: parsePrice(priceEl && priceEl.textContent),
            qty: 1
        }, btn);
    });

    /* сторінка товару: перекриваємо стоковий ajax-обробник */
    var pageCart = document.getElementById('button-cart');
    if (pageCart) {
        pageCart.removeAttribute('onclick');
        pageCart.addEventListener('click', function () {
            var qtyInput = document.querySelector('input[name="quantity"]');
            var qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
            var nameEl = document.querySelector('.product__name');
            var priceEl = document.getElementById('dynamic-price');
            var imgEl = document.querySelector('.product__slider .swiper-slide img');
            var unitPrice = parsePrice(priceEl && priceEl.textContent) / Math.max(qty, 1);
            addItem({
                name: nameEl ? nameEl.textContent.trim() : 'Товар',
                href: location.pathname + location.search,
                img: imgEl ? imgEl.getAttribute('src') : '',
                price: unitPrice || parsePrice(priceEl && priceEl.textContent),
                qty: qty
            }, pageCart);
        });
    }

    render();
})();

/* === gallery-lightbox.js === */
/* Лайтбокс галереї на сторінках поза головною (напр. сторінка товару):
   елементи збираються з DOM-атрибутів data-lightbox-*. На головній свій. */
(function () {
    if (document.querySelector('.hero')) return; /* головна — там власний лайтбокс */
    var triggers = [].slice.call(document.querySelectorAll('[data-lightbox-src]'));
    if (!triggers.length) return;

    var items = triggers.map(function (el) {
        return {
            type: el.getAttribute('data-lightbox-type') || 'image',
            src: el.getAttribute('data-lightbox-src'),
            poster: el.getAttribute('data-lightbox-poster') || ''
        };
    });
    var index = 0;

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
    var body = lb.querySelector('.hm-lightbox__body');
    var counter = lb.querySelector('.hm-lightbox__counter');

    function show(i) {
        index = (i + items.length) % items.length;
        var item = items[index];
        if (item.type === 'video') {
            body.innerHTML = '<video src="' + item.src + '"' + (item.poster ? ' poster="' + item.poster + '"' : '') + ' controls autoplay playsinline></video>';
        } else {
            body.innerHTML = '<img src="' + item.src + '" alt="Hydrophob">';
        }
        counter.textContent = (index + 1) + ' / ' + items.length;
    }
    function open(i) {
        show(i);
        lb.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        lb.classList.remove('is-open');
        body.innerHTML = '';
        document.body.style.overflow = '';
    }
    lb.addEventListener('click', function (e) {
        if (e.target === lb || e.target.closest('.hm-lightbox__close')) close();
    });
    lb.querySelector('.hm-lightbox__nav--prev').addEventListener('click', function () { show(index - 1); });
    lb.querySelector('.hm-lightbox__nav--next').addEventListener('click', function () { show(index + 1); });
    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });
    triggers.forEach(function (el, i) {
        el.addEventListener('click', function () { open(i); });
    });
})();



/* === wishlist-cosmetic.js === */
/* Косметичне "обране": список у localStorage, бейдж на іконці в шапці,
   заповнення сердечка червоним. Реального бекенду немає. */
(function () {
    var KEY = 'hydro_wish';
    var headerWish = document.querySelector('.header__wishlist');

    function getList() {
        try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; }
    }
    function setList(list) {
        try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) {}
        renderBadge(list);
    }
    function renderBadge(list) {
        if (!headerWish) return;
        var n = list.length;
        var badge = headerWish.querySelector('.hc-badge');
        if (!n) { if (badge) badge.remove(); return; }
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'hc-badge';
            headerWish.appendChild(badge);
        }
        badge.textContent = n > 99 ? '99+' : String(n);
        badge.classList.remove('is-bump');
        void badge.offsetWidth;
        badge.classList.add('is-bump');
    }

    function keyFor(btn) {
        var card = btn.closest('.product__item');
        if (card) {
            var link = card.querySelector('.product__item-image');
            if (link) return link.getAttribute('href');
        }
        /* сторінка товару */
        return location.pathname + location.search;
    }

    function syncButtons() {
        var list = getList();
        document.querySelectorAll('.product__item-wish, .pui-wish').forEach(function (btn) {
            btn.classList.toggle('is-active', list.indexOf(keyFor(btn)) !== -1);
        });
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('.product__item-wish, .pui-wish');
        if (!btn) return;
        var key = keyFor(btn);
        var list = getList();
        var i = list.indexOf(key);
        if (i === -1) { list.push(key); } else { list.splice(i, 1); }
        setList(list);
        btn.classList.toggle('is-active', i === -1);
    });

    renderBadge(getList());
    syncButtons();
    /* картки рендеряться динамічно — синхронізуємо стан після завантаження секцій */
    setTimeout(syncButtons, 800);
    setTimeout(syncButtons, 2000);
})();

/* === scroll-reveal.js === */
/* Fade-up секцій і карток при появі у в'юпорті (stagger по картках).
   IntersectionObserver, поважає prefers-reduced-motion (гейт у CSS). */
(function () {
    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var SECTIONS = 'section, .footer__inner';
    var CARDS = '.product__item, .dlv__card, .dx-step, .account__menu-item, .hm-faq__item, .product__faq-item';

    function markCards(scope) {
        var cards = scope.querySelectorAll(CARDS);
        cards.forEach(function (card, i) {
            if (card.classList.contains('rv')) return;
            card.classList.add('rv');
            card.style.setProperty('--rv-delay', (Math.min(i, 8) * 60) + 'ms');
        });
    }

    function typeH2(el) {
        if (el.dataset.twDone) return;
        el.dataset.twDone = '1';
        var full = el.dataset.twFull || '';
        var i = 0;
        (function step() {
            i += 1;
            el.textContent = full.slice(0, i);
            if (i < full.length) setTimeout(step, 40);
            else el.style.minHeight = '';
        })();
    }

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-in');
            entry.target.querySelectorAll('.rv').forEach(function (el) {
                el.classList.add('is-in');
            });
            entry.target.querySelectorAll('[data-tw-full]').forEach(typeH2);
            io.unobserve(entry.target);
        });
    }, { threshold: 0.12 });

    /* ці секції вспливають цілком, разом із вмістом */
    var RV_SECTIONS = '.product__related, .product__viewed, .product__gallery, .product__faq, .hm-sec, .dx-steps, .about-cta';

    function processSection(sec) {
        if (sec.dataset.secAnim) return;
        if (sec.closest('.header') || sec.id === 'cart') return;
        sec.dataset.secAnim = '1';
        if (sec.matches(RV_SECTIONS)) sec.classList.add('rv');
        markCards(sec);
        io.observe(sec);
    }
    document.querySelectorAll(SECTIONS).forEach(processSection);

    /* --- УСІ заголовки: h1 — вспливання, h2 — друк. Універсально, включно з
       динамічно відрендереними секціями (MutationObserver) --- */
    var H_SKIP = '.fb-modal, .otp-modal, .header, .busket, .cui-drawer, .hm-lightbox, .wl-intro, .wl-choose';
    var H_TYPED_ELSEWHERE = '.contacts__help-name, .contacts__title, .dx-cta__text';

    var hio = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            hio.unobserve(el);
            if ('twFull' in el.dataset) typeH2(el);
            else el.classList.add('is-in');
        });
    }, { threshold: 0.4 });

    function processHeading(el) {
        if (el.dataset.hAnim) return;
        if (el.closest(H_SKIP)) return;
        if (el.matches(H_TYPED_ELSEWHERE)) return;
        el.dataset.hAnim = '1';
        if (el.tagName === 'H2' && el.children.length === 0 && el.textContent.trim()) {
            el.dataset.twFull = el.textContent.replace(/\s+/g, ' ').trim();
            el.style.minHeight = el.offsetHeight ? el.offsetHeight + 'px' : '1.2em';
            el.textContent = '';
        } else {
            el.classList.add('rv');
        }
        hio.observe(el);
    }
    function scanHeadings(root) {
        if (root.querySelectorAll) root.querySelectorAll('h1, h2').forEach(processHeading);
    }
    scanHeadings(document);
    new MutationObserver(function (muts) {
        muts.forEach(function (m) {
            [].forEach.call(m.addedNodes, function (n) {
                if (n.nodeType !== 1) return;
                /* динамічно відрендерені секції (fetch-рендер головної/каталогу) */
                if (n.matches && n.matches(SECTIONS)) processSection(n);
                if (n.querySelectorAll) n.querySelectorAll(SECTIONS).forEach(processSection);
                if (n.matches && n.matches('h1, h2')) processHeading(n);
                scanHeadings(n);
            });
        });
    }).observe(document.body, { childList: true, subtree: true });
})();



/* === media-sound.js === */
/* Кнопка звуку на автоплей-відео (контакти тощо) — той самий вигляд, що в hero. */
(function () {
    document.querySelectorAll('[data-media-sound]').forEach(function (btn) {
        var video = btn.parentElement.querySelector('video');
        if (!video) return;
        btn.addEventListener('click', function () {
            var soundOn = video.muted; /* вмикаємо, якщо був вимкнений */
            video.muted = !soundOn;
            var off = btn.querySelector('.hero__sound-off');
            var on = btn.querySelector('.hero__sound-on');
            if (soundOn) { off.setAttribute('hidden', ''); on.removeAttribute('hidden'); }
            else { on.setAttribute('hidden', ''); off.removeAttribute('hidden'); }
            btn.setAttribute('aria-label', soundOn ? 'Вимкнути звук' : 'Увімкнути звук');
            btn.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
            var p = video.play();
            if (p && p.catch) p.catch(function () {});
        });
    });
})();

/* === help-typing.js === */
/* Друкування тексту при появі у в'юпорті: плашки допомоги, заголовок контактів, CTA-текст. */
(function () {
    var items = [].slice.call(document.querySelectorAll('.contacts__help-name, .contacts__title, .dx-cta__text'));
    if (!items.length || !('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var states = new Map();
    items.forEach(function (el) {
        states.set(el, { full: el.textContent.replace(/\s+/g, ' ').trim(), timer: null, done: false });
        el.textContent = '';
        el.style.minHeight = '1.4em'; /* висота не стрибає під час друку */
    });

    function type(el) {
        var s = states.get(el);
        if (!s || s.done) return;
        var i = 0;
        function step() {
            i += 1;
            el.textContent = s.full.slice(0, i);
            if (i >= s.full.length) { s.done = true; return; }
            s.timer = setTimeout(step, 45);
        }
        step();
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

/* === feedback-modals.js === */
/* Попапи "Залишити відгук" і "Поставити питання" (косметика знімка). */
(function () {
    function openModal(name) {
        var m = document.querySelector('[data-fb-modal="' + name + '"]');
        if (!m) return;
        var form = m.querySelector('[data-fb-form]');
        var done = m.querySelector('[data-fb-done]');
        if (form) form.hidden = false;
        if (done) done.hidden = true;
        m.hidden = false;
    }
    function closeModal(m) { m.hidden = true; }

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-review-open]')) { openModal('review'); return; }
        if (e.target.closest('[data-question-open]')) { openModal('question'); return; }
        if (e.target.closest('[data-cat-desc-open]')) { openModal('catdesc'); return; }

        var closeBtn = e.target.closest('[data-fb-close]');
        if (closeBtn) { closeModal(closeBtn.closest('.fb-modal')); return; }

        var modal = e.target.classList && e.target.classList.contains('fb-modal') ? e.target : null;
        if (modal) { closeModal(modal); return; }

        var star = e.target.closest('.fb-star');
        if (star) {
            var val = parseInt(star.getAttribute('data-star'), 10);
            star.closest('[data-fb-stars]').querySelectorAll('.fb-star').forEach(function (s) {
                s.classList.toggle('is-on', parseInt(s.getAttribute('data-star'), 10) <= val);
            });
            return;
        }

        var submit = e.target.closest('[data-fb-submit]');
        if (submit) {
            var mm = submit.closest('.fb-modal');
            mm.querySelector('[data-fb-form]').hidden = true;
            mm.querySelector('[data-fb-done]').hidden = false;
            setTimeout(function () { closeModal(mm); }, 2200);
        }
    });
})();

/* === parallax.js === */
/* Легкий паралакс для позначених зображень ([data-plx]): зсув пропорційний
   позиції елемента відносно центру екрана. rAF-throttle, поважає reduced-motion. */
(function () {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var els = [].slice.call(document.querySelectorAll('[data-plx]'));
    if (!els.length) return;

    var ticking = false;
    function update() {
        ticking = false;
        var vh = window.innerHeight;
        els.forEach(function (el) {
            var r = el.getBoundingClientRect();
            if (r.bottom < 0 || r.top > vh) return; /* поза екраном — не чіпаємо */
            var center = r.top + r.height / 2 - vh / 2;   /* -vh/2..vh/2 */
            var k = parseFloat(el.getAttribute('data-plx')) || 0.06;
            el.style.transform = 'translateY(' + (-center * k).toFixed(1) + 'px) scale(1.12)';
        });
    }
    window.addEventListener('scroll', function () {
        if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
})();
