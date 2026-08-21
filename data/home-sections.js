/* Секції головної знімка: Акційні / Рекомендовані / FAQ / Переглянуті — з data/*.json.
   + трекер переглянутих товарів (localStorage) на product-*.html
   + collapse довгого SEO-опису на головній */
(function () {
    var PROD = 'https://hydrophob.com.ua/';
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
