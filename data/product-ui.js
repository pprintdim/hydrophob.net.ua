/* Сторінка товару знімка: collapse опису + секції «З цим купують» / FAQ / «Переглянуті» (стилі hm-*). */
(function () {
    var PROD = 'https://hydrophob.com.ua/';
    var pm = location.pathname.match(/product-(\d+)\.html/);
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
    function card(p) {
        var name = (p.translations['uk-ua'] || {}).name || '';
        var href = 'product-' + p.product_id + '.html';
        return '<div class="product__item hm-slide">' +
            '<div class="product__item-media">' +
            '<a class="product__item-image" href="' + href + '"><img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'"></a>' +
            '</div>' +
            '<div class="product__item-content">' +
            '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
            '<p class="product__item-price">' + fmt(p.price) + '</p>' +
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
