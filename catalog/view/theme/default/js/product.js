document.addEventListener('DOMContentLoaded', function () {
    if (!window.Swiper) return;   // без слайдера сторінка має жити далі
    const thumbsSlider = new Swiper('.product__thumbs', {
        spaceBetween: 12,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
        mousewheel: {
            forceToAxis: true,
        },
        scrollbar: {
            el: '.product__thumbs .swiper-scrollbar',
            draggable: true,
        },
    });
    const mainSlider = new Swiper('.product__slider', {
        spaceBetween: 24,
        loop: true,
        navigation: {
            nextEl: '.product__slider-next',
            prevEl: '.product__slider-prev',
        },
        thumbs: {
            swiper: thumbsSlider,
        },
        on: {
            afterInit: function () {
                document.querySelector('.product__slider-content')?.classList.remove('is-loading');
            },
        },
    });
    /* страховка: якщо Swiper не стартував (помилка/повільний CDN) — показати слайди */
    setTimeout(function () {
        document.querySelector('.product__slider-content')?.classList.remove('is-loading');
    }, 3000);
});

const buttons = document.querySelectorAll('.product__selects-btn');
const contents = document.querySelectorAll('.product__result-content');
buttons.forEach((button, index) => {
    button.addEventListener('click', () => {
        buttons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        contents.forEach(content => content.classList.remove('active'));
        contents[index].classList.add('active');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    /* стрілки секцій — по боках слайдера, як на головній */
    document.querySelectorAll('.product__viewed, .product__related, .product__gallery').forEach(function (sec) {
        const slider = sec.querySelector('.swiper');
        const btns = sec.querySelector('.product__viewed-btns, .product__related-btns');
        if (slider && btns) {
            btns.classList.add('side-arrows');
            slider.parentNode.insertBefore(btns, slider);
            const wrap = document.createElement('div');
            wrap.className = 'side-arrows-wrap';
            slider.parentNode.insertBefore(wrap, slider);
            wrap.appendChild(btns);
            wrap.appendChild(slider);
        }
    });

    const viewedSlider = document.querySelector('.product__viewed-slider');
    if (viewedSlider) {
        new Swiper(viewedSlider, {
            spaceBetween: 24,
            slidesPerView: 1.15,   /* один крок з .hm-slider: картка 86% + край наступної */
            navigation: {
                nextEl: '.product__viewed-next',
                prevEl: '.product__viewed-prev',
            },
            breakpoints: {
                768: { slidesPerView: 3 },
                1000: { slidesPerView: 4 },
            },
        });
    }

    const relatedSlider = document.querySelector('.product__related-slider');
    if (relatedSlider) {
        new Swiper(relatedSlider, {
            spaceBetween: 24,
            slidesPerView: 1.15,   /* один крок з .hm-slider: картка 86% + край наступної */
            navigation: {
                nextEl: '.product__related-next',
                prevEl: '.product__related-prev',
            },
            breakpoints: {
                768: { slidesPerView: 3 },
                1000: { slidesPerView: 4 },
            },
        });
    }

    const gallerySlider = document.querySelector('.product__gallery-slider');
    if (gallerySlider) {
        new Swiper(gallerySlider, {
            spaceBetween: 16,
            slidesPerView: 1.4,
            mousewheel: {
                forceToAxis: true,
            },
            navigation: {
                nextEl: '.product__gallery-next',
                prevEl: '.product__gallery-prev',
            },
            breakpoints: {
                600: { slidesPerView: 2.4 },
                1000: { slidesPerView: 4 },
            },
        });
    }
});

// FAQ accordion: one answer open at a time
document.querySelectorAll('[data-faq]').forEach((list) => {
    list.addEventListener('click', function (event) {
        const button = event.target.closest('[data-faq-toggle]');
        if (!button) return;

        const item = button.closest('.product__faq-item');
        const open = item.classList.contains('active');

        list.querySelectorAll('.product__faq-item').forEach((other) => {
            other.classList.remove('active');
            other.querySelector('[data-faq-toggle]').setAttribute('aria-expanded', 'false');
            other.querySelector('.product__faq-answer').hidden = true;
        });

        if (!open) {
            item.classList.add('active');
            button.setAttribute('aria-expanded', 'true');
            item.querySelector('.product__faq-answer').hidden = false;
        }
    });
});

/* галерея з одного фото — стрілки не потрібні */
document.addEventListener('DOMContentLoaded', function () {
    var box = document.querySelector('.product__slider-content');
    if (!box) return;
    var slides = box.querySelectorAll('.product__slider .swiper-slide');
    if (slides.length <= 1) box.classList.add('is-single');
});
