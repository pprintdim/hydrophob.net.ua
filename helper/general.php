<?php
declare(strict_types=1);

/**
 * Спільні хелпери для sectional-PHP збірки hydrophob.com.ua.
 * Дані беруться з data/*.json (не редагувати), картинки і статичні
 * ассети теми лишаються на бойовому catalog/view/theme/default (CDN),
 * бо ця тека — лише верстка на сабдомені html.*.
 */

const HP_CDN = 'https://hydrophob.com.ua/';
const HP_THEME = 'https://hydrophob.com.ua/catalog/view/theme/default/';
const HP_PHONE_DISPLAY = '+380 (73) 108-12-12';
const HP_PHONE_HREF = 'tel:+380 (73) 108-12-12';
const HP_PHONE_VIBER = 'viber://chat?number=+%2B380731081212';
const HP_EMAIL = 'hydrophob@ukr.net';
const HP_ADDRESS = 'вул. Якова Гніздовського, 15, Київ';
const HP_TELEGRAM_URL = 'https://t.me/Hydrophob1';
const HP_WORKTIME = 'Працюємо з 08:00 до 22:00, cб. - нд : вихідний';

function hp_data_dir(): string
{
    return dirname(__DIR__) . '/data';
}

function hp_load_json(string $file): array
{
    static $cache = [];
    if (isset($cache[$file])) {
        return $cache[$file];
    }
    $path = hp_data_dir() . '/' . $file;
    $json = is_file($path) ? file_get_contents($path) : false;
    $data = $json !== false ? (json_decode($json, true) ?? []) : [];
    $cache[$file] = $data;
    return $data;
}

/** @return array<int,array> */
function hp_products(): array
{
    return hp_load_json('products.json');
}

/** @return array<int,array> */
function hp_categories(): array
{
    return hp_load_json('categories.json');
}

function hp_home(): array
{
    return hp_load_json('home.json');
}

/** @return array<int,array> */
function hp_informations(): array
{
    return hp_load_json('informations.json');
}

function hp_product_by_id(int $id): ?array
{
    static $byId = null;
    if ($byId === null) {
        $byId = [];
        foreach (hp_products() as $p) {
            $byId[(int) $p['product_id']] = $p;
        }
    }
    return $byId[$id] ?? null;
}

function hp_category_by_id(int $id): ?array
{
    static $byId = null;
    if ($byId === null) {
        $byId = [];
        foreach (hp_categories() as $c) {
            $byId[(int) $c['category_id']] = $c;
        }
    }
    return $byId[$id] ?? null;
}

function hp_information_by_id(int $id): ?array
{
    foreach (hp_informations() as $i) {
        if ((int) ($i['information_id'] ?? 0) === $id) {
            return $i;
        }
    }
    return null;
}

function hp_t(array $entity, string $field, string $lang = 'uk-ua'): string
{
    $value = $entity['translations'][$lang][$field] ?? $entity['translations']['uk-ua'][$field] ?? '';
    return is_string($value) ? $value : '';
}

function hp_e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Ціна вигляду "1 234 грн" (як у data/*.js). */
function hp_price($value): string
{
    $n = (int) round((float) $value);
    return number_format($n, 0, ',', ' ') . ' грн';
}

/** encodeURI-подібне кодування шляху: кожен сегмент окремо, "/" не чіпаємо. */
function hp_encode_uri_path(string $path): string
{
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}

/** Оригінальне зображення товару/категорії на бойовому CDN. */
function hp_full_img(?string $path): string
{
    if (!$path) {
        return HP_CDN . 'image/placeholder.png';
    }
    return HP_CDN . 'image/' . hp_encode_uri_path($path);
}

/** Кешоване зображення (image/cache/…-{size}x{size}.ext), як у data/catalog.js. */
function hp_cache_img(?string $path, int $size): string
{
    if (!$path) {
        return HP_CDN . 'image/placeholder.png';
    }
    $dot = strrpos($path, '.');
    if ($dot === false) {
        return HP_CDN . 'image/placeholder.png';
    }
    $base = substr($path, 0, $dot);
    $ext = substr($path, $dot + 1);
    return HP_CDN . 'image/cache/' . hp_encode_uri_path($base) . '-' . $size . 'x' . $size . '.' . $ext;
}

function hp_badges_map(): array
{
    $home = hp_home();
    return $home['badges'] ?? [];
}

/** @return string[] */
function hp_product_badges(int $productId): array
{
    $map = hp_badges_map();
    return $map[(string) $productId] ?? [];
}

function hp_badge_label(string $badge): string
{
    switch ($badge) {
        case 'sale':
            return 'Акція';
        case 'new':
            return 'Новинка';
        default:
            return 'Топ';
    }
}

function hp_badges_html(array $badges): string
{
    if (!$badges) {
        return '';
    }
    $out = '<div class="product__item-badges">';
    foreach ($badges as $b) {
        $out .= '<span class="product__item-badge product__item-badge--' . hp_e($b) . '">' . hp_e(hp_badge_label($b)) . '</span>';
    }
    return $out . '</div>';
}

const HP_WISH_ICON = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0l-1.4 1.4-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4l8.8 8.8 8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg>';
const HP_CART_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.56706 2.90039H6.90039V6.90039L2.90039 6.90039V9.56706H6.90039V13.5671H9.56706V9.56706H13.5671V6.90039L9.56706 6.90039V2.90039Z" fill="white"/></svg>';

/** Картка товару, як генерують data/catalog.js та data/home-sections.js. */
function hp_product_card(array $p, string $modifier = ''): string
{
    $name = hp_t($p, 'name');
    $id = (int) $p['product_id'];
    $href = 'product.php?id=' . $id;
    $badges = hp_product_badges($id);
    $class = trim('product__item ' . $modifier);
    return '<div class="' . hp_e($class) . '">' .
        '<div class="product__item-media">' .
        '<a class="product__item-image" href="' . hp_e($href) . '">' .
        '<img src="' . hp_cache_img($p['image'] ?? '', 450) . '" alt="' . hp_e($name) . '" loading="lazy" onerror="this.src=\'' . HP_CDN . 'image/placeholder.png\'">' .
        '</a>' . hp_badges_html($badges) .
        '<button type="button" class="product__item-wish" title="Додати до обраного" aria-label="Додати до обраного">' . HP_WISH_ICON . '</button>' .
        '</div>' .
        '<div class="product__item-content">' .
        '<a class="product__item-title" href="' . hp_e($href) . '"><h3>' . hp_e($name) . '</h3></a>' .
        '<p class="product__item-price">' . hp_price($p['price'] ?? 0) . '</p>' .
        '<button class="product__item-add btn-2" type="button">В кошик' . HP_CART_ICON . '</button>' .
        '</div>' .
        '</div>';
}

/** BreadcrumbList JSON-LD, як у сатичних category-*.html. */
function hp_breadcrumb_ld(array $items): string
{
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    }
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
