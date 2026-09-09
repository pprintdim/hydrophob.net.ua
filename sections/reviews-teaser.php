<?php
/* Тизер популярних відгуків. Використання: $reviewsTeaserLight = true/false; потім require. */
require_once __DIR__ . '/../helper/reviews.php';
$rtLight = !empty($reviewsTeaserLight);
$rtItems = array_slice(hp_reviews(), 0, 8);
?>
<section class="rv-teaser <?= $rtLight ? 'rv-teaser--light' : 'rv-teaser--dark' ?>">
    <div class="container">
        <div class="rv-teaser__head">
            <h2 class="hm-sec__title page-name" style="margin-bottom:0;<?= $rtLight ? 'color:#161616;' : '' ?>">Відгуки покупців</h2>
            <a class="rv-teaser__all" href="reviews.php">Всі відгуки</a>
        </div>
        <div class="hm-slider-wrap rv-teaser__wrap">
            <button type="button" class="hm-arrow hm-arrow--prev" data-rv-prev aria-label="Назад">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="hm-slider rv-teaser__slider" data-rv-slider>
                <?php foreach ($rtItems as $r): ?>
                <div class="hm-slide rv-teaser__slide"><?= hp_review_card($r) ?></div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="hm-arrow hm-arrow--next" data-rv-next aria-label="Вперед">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
<script>
/* слайдер відгуків: горизонтальний скрол зі стрілками (як інші секції) */
(function () {
    var wrap = document.currentScript.previousElementSibling;
    if (!wrap) return;
    var slider = wrap.querySelector('[data-rv-slider]');
    var prev = wrap.querySelector('[data-rv-prev]');
    var next = wrap.querySelector('[data-rv-next]');
    if (!slider || !prev || !next) return;

    function step() {
        var card = slider.querySelector('.rv-teaser__slide');
        return card ? card.getBoundingClientRect().width + 24 : 320;
    }
    prev.addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); });
    next.addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); });

    function sync() {
        var max = slider.scrollWidth - slider.clientWidth - 2;
        prev.classList.toggle('is-hidden', slider.scrollLeft <= 2);
        next.classList.toggle('is-hidden', slider.scrollLeft >= max);
    }
    slider.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync);
    sync();
})();
</script>
    </div>
</section>
