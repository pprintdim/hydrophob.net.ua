/* Секції головної знімка: Акційні / Рекомендовані / FAQ / Переглянуті — з data/*.json.
   + трекер переглянутих товарів (localStorage) на product-*.html
   + collapse довгого SEO-опису на головній */
(function () {
    var PROD = 'https://hydrophob.com.ua/';
    var VIEWED_KEY = 'hydro_viewed';

    /* --- трекер переглянутих: на сторінці товару пишемо id --- */
    var pm = location.pathname.match(/product-(\d+)\.html/);
    if (pm) {
        try {
            var seen = JSON.parse(localStorage.getItem(VIEWED_KEY) || '[]');
            var id = parseInt(pm[1], 10);
            seen = [id].concat(seen.filter(function (x) { return x !== id; })).slice(0, 12);
            localStorage.setItem(VIEWED_KEY, JSON.stringify(seen));
        } catch (e) {}
    }

    var mount = document.getElementById('home-sections');

    /* --- collapse опису (.desc) на головній --- */
    var desc = document.querySelector('.desc');
    if (desc && !desc.classList.contains('is-collapsible')) {
        desc.classList.add('is-collapsible');
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'desc-toggle btn-2';
        btn.textContent = 'Показати більше';
        desc.parentNode.insertBefore(btn, desc.nextSibling);
        btn.addEventListener('click', function () {
            var open = desc.classList.toggle('is-open');
            btn.textContent = open ? 'Згорнути' : 'Показати більше';
            if (!open) desc.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    if (!mount) return;

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }
    function card(p, oldPrice) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product-' + p.product_id + '.html';
        var price = oldPrice
            ? '<span class="product__item-price-new">' + fmt(p.price) + '</span> <span class="product__item-price-old">' + fmt(oldPrice) + '</span>'
            : fmt(p.price);
        return '<div class="product__item hm-slide">' +
            '<div class="product__item-media">' +
            '<a class="product__item-image" href="' + href + '"><img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'"></a>' +
            (oldPrice ? '<div class="product__item-badges"><span class="product__item-badge product__item-badge--sale">Акція</span></div>' : '') +
            '</div>' +
            '<div class="product__item-content">' +
            '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
            '<p class="product__item-price">' + price + '</p>' +
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

        mount.innerHTML = html;

        /* стрілки слайдерів */
        mount.querySelectorAll('.hm-slider-wrap').forEach(function (wrap) {
            var slider = wrap.querySelector('.hm-slider');
            var step = function () {
                var el = slider.querySelector('.hm-slide');
                return el ? el.getBoundingClientRect().width + 24 : 300;
            };
            wrap.querySelector('.hm-arrow--prev').addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); });
            wrap.querySelector('.hm-arrow--next').addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); });
        });

        /* FAQ акордеон */
        mount.querySelectorAll('.hm-faq__btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = btn.parentNode;
                var open = item.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    }).catch(function (e) {
        mount.innerHTML = '';
    });
})();
