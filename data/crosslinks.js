/* Кнопки-перелінковки між сторінками знімка — блок перед футером залежно від типу сторінки. */
(function () {
    var path = location.pathname.split('/').pop() || 'index.html';
    var links;

    if (/^category-/.test(path)) {
        links = [
            { href: 'info-6.html', text: 'Доставка та оплата' },
            { href: 'info-4.html', text: 'Про нас' },
            { href: 'contact.html', text: 'Контакти' }
        ];
    } else if (/^product-/.test(path)) {
        links = [
            { href: 'category-33.html', text: 'До каталогу' },
            { href: 'info-6.html', text: 'Доставка та оплата' },
            { href: 'contact.html', text: 'Контакти' }
        ];
    } else if (/^info-|^search/.test(path)) {
        links = [
            { href: 'category-33.html', text: 'Перейти в каталог' },
            { href: 'index.html', text: 'На головну' },
            { href: 'contact.html', text: 'Контакти' }
        ];
    } else if (/^contact/.test(path)) {
        links = [
            { href: 'category-33.html', text: 'Перейти в каталог' },
            { href: 'info-6.html', text: 'Доставка та оплата' }
        ];
    } else if (/^(account|login|register|edit|address|order|wishlist)/.test(path)) {
        links = [
            { href: 'category-33.html', text: 'До каталогу' },
            { href: 'index.html', text: 'На головну' }
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
