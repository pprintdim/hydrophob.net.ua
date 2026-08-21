/* Редизайн каталогу знімка:
   - тулбар: кнопка «Фільтри» + сортування + перемикач сітки 4/2;
   - drawer (виїжджає справа): фільтри акордеонами, початково згорнуті;
   - сайдбар праворуч: список категорій + популярні товари з data/*.json. */
(function () {
    var PROD = 'https://hydrophob.com.ua/';
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

        var current = (location.pathname.match(/category-(\d+)\.html/) || [])[1];

        var catList = cats
            .filter(function (c) { return String(c.category_id) !== '33'; })
            .map(function (c) {
                var name = (c.translations['uk-ua'] || {}).name || '';
                var active = String(c.category_id) === current ? ' is-active' : '';
                return '<a class="catalog__filter-item' + active + '" href="category-' + c.category_id + '.html">' + name + '</a>';
            }).join('');

        var popular = (home.popular || []).map(function (id) {
            var p = byId[id];
            if (!p) return '';
            var name = (p.translations['uk-ua'] || {}).name || '';
            return '<a class="cui-pop" href="product-' + p.product_id + '.html">' +
                '<img class="cui-pop__img" src="' + cacheImg(p.image, 200) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'">' +
                '<span class="cui-pop__meta"><span class="cui-pop__name">' + name + '</span>' +
                '<span class="cui-pop__price">' + fmt(p.price) + '</span></span>' +
                '</a>';
        }).join('');

        aside.innerHTML =
            '<div class="catalog__filter"><p class="catalog__filter-title">Категорії</p>' +
            '<div class="catalog__filter-list"><a class="catalog__filter-item' + (current === '33' ? ' is-active' : '') + '" href="category-33.html">Всі товари</a>' + catList + '</div></div>' +
            (popular ? '<div class="catalog__filter"><p class="catalog__filter-title">Популярні товари</p>' +
                '<div class="cui-pop-list">' + popular + '</div></div>' : '');
    }).catch(function () {});
})();
