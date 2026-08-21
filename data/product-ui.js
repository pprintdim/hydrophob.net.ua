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
        var href = 'product-' + p.product_id + '.html';
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
                    return '<a class="pui-tag" href="search.html?q=' + encodeURIComponent(t) + '">' + t + '</a>';
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
