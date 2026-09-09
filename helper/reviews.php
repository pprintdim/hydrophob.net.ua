<?php
/* Відгуки покупців (дані знімка — при натяжці підуть з БД/prom.ua) */

function hp_reviews(): array
{
    return [
        ['name' => 'Віка К.', 'date' => '22/07/2026', 'stars' => 5, 'source' => 'prom.ua', 'product_id' => 75, 'product' => 'Керамічний детейл спрей 500 мл', 'text' => 'Наносила на кузов після мийки — вода реально збирається в краплі та скочується. Блиск як після салону, ефект тримається вже другий місяць.'],
        ['name' => 'Олексій Т.', 'date' => '15/07/2026', 'stars' => 5, 'source' => 'prom.ua', 'product_id' => 58, 'product' => 'Керамічне покриття 10Н', 'text' => 'Робив покриття сам у гаражі, за інструкцією. Нічого складного, результат чудовий — фара і кузов блищать, бруд не липне.'],
        ['name' => 'Марина С.', 'date' => '03/07/2026', 'stars' => 5, 'source' => 'сайт', 'product_id' => 67, 'product' => 'Знежирювач-активатор', 'text' => 'Брала в комплект до кераміки. Проста в застосуванні, запах не різкий. Поверхня після обробки ідеально чиста.'],
        ['name' => 'Дмитро Л.', 'date' => '28/06/2026', 'stars' => 4, 'source' => 'prom.ua', 'product_id' => 77, 'product' => 'Hydrophob АВТОМИЙКА 10 л', 'text' => 'Використовуємо на мийці самообслуговування. Клієнти задоволені, витрата економна. Чотири зірки лише за довгу доставку.'],
        ['name' => 'Ірина Б.', 'date' => '19/06/2026', 'stars' => 5, 'source' => 'сайт', 'product_id' => 62, 'product' => 'Покриття для шкіри', 'text' => 'Обробила шкіряний салон — тепер розлита кава просто витирається серветкою. Дуже задоволена, замовлю ще для взуття.'],
        ['name' => 'Сергій М.', 'date' => '11/06/2026', 'stars' => 5, 'source' => 'prom.ua', 'product_id' => 60, 'product' => 'Покриття для скла', 'text' => 'Лобове скло після обробки — дощ злітає вже на 60 км/год, двірники майже не потрібні. Працює як заявлено.'],
        ['name' => 'Наталя Ф.', 'date' => '02/06/2026', 'stars' => 5, 'source' => 'сайт', 'product_id' => 68, 'product' => 'Мікрофібра для поліровки', 'text' => 'Якісна щільна мікрофібра, не лишає ворсу. Після трьох прань як нова.'],
        ['name' => 'Андрій П.', 'date' => '25/05/2026', 'stars' => 5, 'source' => 'prom.ua', 'product_id' => 70, 'product' => 'Набір для нанесення', 'text' => 'Брав повний набір — вигідніше, ніж окремо. Все запаковано акуратно, інструкція зрозуміла. Менеджер допоміг з вибором.'],
        ['name' => 'Юлія Г.', 'date' => '18/05/2026', 'stars' => 4, 'source' => 'сайт', 'product_id' => 79, 'product' => 'Мірний стакан', 'text' => 'Зручний для дозування концентрату. Дрібниця, а працювати приємніше.'],
        ['name' => 'Володимир К.', 'date' => '08/05/2026', 'stars' => 5, 'source' => 'prom.ua', 'product_id' => 63, 'product' => 'Покриття для гуми', 'text' => 'Гумові ущільнювачі дверей більше не примерзають взимку. Шини виглядають як нові. Рекомендую.'],
    ];
}

function hp_reviews_avg(): float
{
    $r = hp_reviews();
    return round(array_sum(array_column($r, 'stars')) / max(count($r), 1), 1);
}

function hp_stars_html(int $stars, int $size = 13): string
{
    $icon = '<svg width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="rv-star%s"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>';
    $out = '<span class="rv-stars" aria-label="Оцінка ' . $stars . ' з 5">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= sprintf($icon, $size, $size, $i <= $stars ? ' rv-star--on' : '');
    }
    return $out . '</span>';
}

function hp_review_card(array $r): string
{
    $initial = mb_substr($r['name'], 0, 1);
    return '<article class="rv-card">' .
        '<div class="rv-card__head">' .
        '<span class="rv-card__avatar" aria-hidden="true">' . hp_e($initial) . '</span>' .
        '<div class="rv-card__ident">' .
        '<h3 class="rv-card__name">' . hp_e($r['name']) . '</h3>' .
        '<p class="rv-card__meta">' . hp_e($r['date']) . ' · ' . hp_e($r['source']) . '</p>' .
        '</div>' .
        hp_stars_html($r['stars']) .
        '</div>' .
        '<p class="rv-card__product">Товар: <a href="product.php?id=' . (int) $r['product_id'] . '">' . hp_e($r['product']) . '</a></p>' .
        '<p class="rv-card__text">' . hp_e($r['text']) . '</p>' .
        '</article>';
}
