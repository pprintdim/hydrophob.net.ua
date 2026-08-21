<?php
/* Hero-відеослайдер: кожен слайд — свій заголовок, опис, note-рядок і кнопка.
   Ліниве відео: тег <video> лише в першому слайді, решта — постер + data-video. */
$heroSlides = [
    [
        'video' => 'media/porsche.mp4', 'poster' => 'media/porsche-poster.jpg', 'h' => 'h1',
        'title' => 'Hydrophob',
        'descr' => 'Захисне Nano покриття для вашого авто',
        'note' => 'Понад 70 засобів для авто, одягу та дому',
        'btn_text' => 'Каталог', 'btn_href' => 'catalog.php',
    ],
    [
        'video' => 'media/moto.mp4', 'poster' => 'media/moto-poster.jpg', 'h' => 'h2',
        'title' => 'Захист техніки',
        'descr' => 'Гідрофобний захист для мото і будь-яких поверхонь',
        'note' => 'Кузов, диски, скло, пластик — один захист для всього',
        'btn_text' => 'Засоби для авто', 'btn_href' => 'catalog.php?id=59',
    ],
    [
        'video' => 'media/instruction.mp4', 'poster' => 'media/instruction-poster.jpg', 'h' => 'h2',
        'title' => 'Просте нанесення',
        'descr' => 'Наносиш — розтираєш мікрофіброю — готово',
        'note' => 'Без професійного обладнання — результат з першого разу',
        'btn_text' => 'Обрати засіб', 'btn_href' => 'catalog.php',
    ],
    [
        'video' => 'media/twerk.mp4', 'poster' => 'media/twerk-poster.jpg', 'h' => 'h2',
        'title' => 'Ефект, який видно',
        'descr' => 'Вода збирається в краплі та скочується з поверхні',
        'note' => 'Тримається місяцями навіть після регулярного миття',
        'btn_text' => 'Спробувати', 'btn_href' => 'catalog.php',
    ],
];
?>
    <main class="main">
        <section class="hero hero--slider" data-hero-slider>
<?php foreach ($heroSlides as $i => $s): ?>
<div class="hero__slide<?= $i === 0 ? ' is-active' : '' ?>">
    <?php if ($i === 0): ?>
    <div class="hero__video"><video src="<?= $s['video'] ?>" poster="<?= $s['poster'] ?>" autoplay muted playsinline preload="auto" data-hero-video></video></div>
    <?php else: ?>
    <div class="hero__video" data-video="<?= $s['video'] ?>"><img src="<?= $s['poster'] ?>" alt="" loading="lazy"></div>
    <?php endif; ?>
    <div class="container"><div class="hero__inner">
        <<?= $s['h'] ?> class="hero__title"><?= htmlspecialchars($s['title']) ?></<?= $s['h'] ?>>
        <p class="hero__descr"><?= htmlspecialchars($s['descr']) ?></p>
        <span class="hero__note"><?= htmlspecialchars($s['note']) ?></span>
        <a class="hero__btn btn" href="<?= $s['btn_href'] ?>"><p><?= htmlspecialchars($s['btn_text']) ?></p>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M7 18.5L13 12.5L7 6.5L9 4.5L17 12.5L9 20.5L7 18.5Z" fill="white"></path>
            </svg></a>
    </div></div>
    <button type="button" class="hero__sound" data-hero-sound aria-label="Увімкнути звук">
        <svg class="hero__sound-off" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6 9H2v6h4l5 4zM22 9l-6 6M16 9l6 6"/></svg>
        <svg class="hero__sound-on" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" hidden><path d="M11 5 6 9H2v6h4l5 4zM15.54 8.46a5 5 0 0 1 0 7.07M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
    </button>
</div>
<?php endforeach; ?>
<div class="hero__dots"><?php foreach ($heroSlides as $i => $s): ?><button type="button" class="hero__dot<?= $i === 0 ? ' is-active' : '' ?>" data-hero-dot="<?= $i ?>" aria-label="Слайд <?= $i + 1 ?>"></button><?php endforeach; ?></div>
<div class="hero__loader" data-hero-loader hidden><span></span></div>
</section>
