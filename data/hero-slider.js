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
