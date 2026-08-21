/* Рендер каталогу знімка з data/products.json.
   Використання: <div id="json-catalog" data-category="33"></div> — category "33" = всі товари (коренева). */
(function () {
    var box = document.getElementById('json-catalog');
    if (!box) return;

    var catId = parseInt(box.getAttribute('data-category'), 10);
    var PER_PAGE = 12;
    var PROD = 'https://hydrophob.com.ua/';

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
