document.addEventListener('DOMContentLoaded', function () {
    const thumbsSlider = new Swiper('.product__thumbs', {
        spaceBetween: 24,
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
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
    });
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