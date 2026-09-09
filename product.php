<?php
require_once __DIR__ . '/helper/general.php';

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $productId ? hp_product_by_id($productId) : null;

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Товар не знайдено';
    require __DIR__ . '/sections/document-start.php';
    require __DIR__ . '/sections/header.php';
    ?>
    <main class="main">
        <section class="catalog"><div class="container"><div class="catalog__inner">
            <h1 class="catalog__title page-title">Товар не знайдено</h1>
            <p><a class="btn-2" href="catalog.php">До каталогу</a></p>
        </div></div></section>

    </main>
    <?php
    require __DIR__ . '/sections/footer.php';
    require __DIR__ . '/sections/document-end.php';
    exit;
}

$name = hp_t($product, 'name');
$categoryTitle = '';
$firstCategoryId = (int) ($product['categories'][0] ?? 0);
if ($firstCategoryId) {
    $cat = hp_category_by_id($firstCategoryId);
    if ($cat) {
        $categoryTitle = hp_t($cat, 'name');
    }
}

$description = hp_html($product, 'description');
$metaTitle = hp_t($product, 'meta_title') ?: $name;
$metaDescription = hp_t($product, 'meta_description');
$tags = array_filter(array_map('trim', explode(',', hp_t($product, 'tag'))));

$sliderImages = array_values(array_unique(array_filter(array_merge(
    [$product['image'] ?? ''],
    $product['images'] ?? []
))));

$badges = hp_product_badges($productId);

$pageTitle = $metaTitle;
$pageDescription = $metaDescription;
$pageKeywords = hp_t($product, 'tag');
$pageCanonical = 'product.php?id=' . $productId;
$pageExtraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">';
$pageLangRedirect = 'https://hydrophob.net.ua/index.php?route=product/product&product_id=' . $productId;

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
    <main class="main main--light">
       <section class="product" id="product-product">
            <div class="container" id="content">
                <nav class="catalog__crumbs" aria-label="Хлібні крихти">
                    <a href="index.php" class="catalog__crumbs-link">Головна</a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a href="catalog.php<?= $firstCategoryId ? '?id=' . $firstCategoryId : '' ?>" class="catalog__crumbs-link"><?= hp_e($categoryTitle !== '' ? $categoryTitle : 'Каталог') ?></a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a class="catalog__crumbs-link is-current"><?= hp_e($name) ?></a>
                </nav>
                <script type="application/ld+json"><?= hp_breadcrumb_ld([
                    ['name' => 'Головна', 'url' => 'index.php'],
                    ['name' => $categoryTitle !== '' ? $categoryTitle : 'Каталог', 'url' => 'catalog.php' . ($firstCategoryId ? '?id=' . $firstCategoryId : '')],
                    ['name' => $name, 'url' => 'product.php?id=' . $productId],
                ]) ?></script>
                <div class="product__inner">
                    <div class="product__left">
						<div class="product__slider-content<?= count($sliderImages) > 1 ? ' is-loading' : '' ?>">
							<?php if (count($sliderImages) > 1): ?>
							<div class="product__slider-loader" aria-hidden="true"><span></span></div>
							<?php endif; ?>
							<div class="product__slider swiper">
								<div class="swiper-wrapper">
									<?php foreach ($sliderImages as $img): ?>
									<div class="swiper-slide">
										<img src="<?= hp_cache_img($img, 500) ?>" alt="<?= hp_e($name) ?>">
									</div>
									<?php endforeach; ?>
								</div>
								<?php if (count($sliderImages) > 1): ?>
								<div class="product__slider-btns">
                                    <button class="product__slider-prev">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="42" viewBox="0 0 14 42" fill="none">
                                            <path d="M13.6238 3.70761C13.744 3.50915 13.8379 3.27569 13.9003 3.02057C13.9626 2.76544 13.9922 2.49364 13.9872 2.2207C13.9823 1.94775 13.9429 1.67899 13.8714 1.42978C13.7999 1.18056 13.6977 0.955764 13.5705 0.76822C13.4433 0.580676 13.2937 0.43406 13.1302 0.33674C12.9667 0.239421 12.7926 0.193306 12.6177 0.201027C12.4428 0.208748 12.2705 0.270155 12.1108 0.381741C11.9511 0.493327 11.8071 0.652908 11.6869 0.851371L0.364118 19.5604C0.130287 19.9464 0 20.4574 0 20.9885C0 21.5197 0.130287 22.0307 0.364118 22.4166L11.6869 41.1277C11.8063 41.3305 11.9503 41.4943 12.1106 41.6095C12.2709 41.7247 12.4442 41.7891 12.6206 41.7989C12.797 41.8087 12.9728 41.7637 13.1379 41.6665C13.3031 41.5693 13.4542 41.4219 13.5825 41.2328C13.7108 41.0438 13.8137 40.8168 13.8854 40.5651C13.957 40.3134 13.9958 40.042 13.9997 39.7667C14.0035 39.4914 13.9723 39.2176 13.9077 38.9613C13.8432 38.7049 13.7467 38.4712 13.6238 38.2736L3.16418 20.9885L13.6238 3.70761Z" fill="#161616"/>
                                        </svg>
                                    </button>
                                    <button class="product__slider-next">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="42" viewBox="0 0 14 42" fill="none">
                                            <path d="M0.376226 3.70761C0.256047 3.50915 0.162095 3.27569 0.0997324 3.02057C0.0373697 2.76544 0.00781822 2.49364 0.0127659 2.2207C0.0177135 1.94775 0.0570641 1.67899 0.128569 1.42978C0.200074 1.18056 0.302334 0.955764 0.42951 0.76822C0.556686 0.580676 0.706289 0.43406 0.869775 0.33674C1.03326 0.239421 1.20743 0.193306 1.38233 0.201027C1.55724 0.208748 1.72946 0.270155 1.88916 0.381741C2.04886 0.493327 2.19291 0.652908 2.31309 0.851371L13.6359 19.5604C13.8697 19.9464 14 20.4574 14 20.9885C14 21.5197 13.8697 22.0307 13.6359 22.4166L2.31309 41.1277C2.1937 41.3305 2.04968 41.4943 1.8894 41.6095C1.72911 41.7247 1.55575 41.7891 1.37939 41.7989C1.20303 41.8087 1.02718 41.7637 0.862059 41.6665C0.696937 41.5693 0.545833 41.4219 0.417525 41.2328C0.289217 41.0438 0.186262 40.8168 0.11464 40.5651C0.0430183 40.3134 0.00415802 40.042 0.000315666 39.7667C-0.00352669 39.4914 0.0277252 39.2176 0.0922575 38.9613C0.156789 38.7049 0.253315 38.4712 0.376226 38.2736L10.8358 20.9885L0.376226 3.70761Z" fill="#161616"/>
                                        </svg>
                                    </button>
								</div>
								<?php endif; ?>
							</div>
							<?php if (count($sliderImages) > 1): ?>
							<div class="product__thumbs swiper">
								<div class="swiper-wrapper">
									<?php foreach ($sliderImages as $img): ?>
									<div class="swiper-slide">
										<img src="<?= hp_cache_img($img, 74) ?>" alt="<?= hp_e($name) ?>">
									</div>
									<?php endforeach; ?>
								</div>
								<div class="swiper-scrollbar"></div>
							</div>
							<?php endif; ?>
						</div>
					</div>
                    <div class="product__content">
                        <form class="product__top" id="product" action="" method="post" enctype="multipart/form-data">

                            <?php if ($categoryTitle !== ''): ?>
                            <p class="product__title page-title"><?= hp_e($categoryTitle) ?></p>
                            <?php endif; ?>
                            <h1 class="product__name"><?= hp_e($name) ?></h1>

                            <?php
                            $modelValue = trim($product['model'] ?? '');
                            $inStock    = (int) ($product['quantity'] ?? 1) > 0;
                            ?>
                            <div class="product__meta">
                                <span class="product__stock<?= $inStock ? ' is-in' : '' ?>"><?= $inStock ? 'В наявності' : 'Під замовлення' ?></span>
                                <?php if ($modelValue !== ''): ?><span class="product__sku">Артикул: <?= hp_e($modelValue) ?></span><?php endif; ?>
                            </div>

                            <?php
                            // головні характеристики: обʼєм тягнемо з назви, решта — статично для макета
                            preg_match('/(\d+(?:[.,]\d+)?)\s*(мл|ml|л|шт)/ui', $name, $vm);
                            $keySpecs = [];
                            if ($vm) { $keySpecs[] = ['Обʼєм', $vm[1] . ' ' . mb_strtolower($vm[2])]; }
                            $keySpecs[] = ['Тип засобу', preg_match('/спрей/ui', $name) ? 'Спрей' : (preg_match('/набір/ui', $name) ? 'Набір' : 'Засіб')];
                            $keySpecs[] = ['Область застосування', 'Авто'];
                            ?>
                            <ul class="product__keyspecs">
                                <?php foreach ($keySpecs as [$specName, $specValue]): ?>
                                <li class="product__keyspec"><span class="product__keyspec-name"><?= hp_e($specName) ?></span><span class="product__keyspec-value"><?= hp_e($specValue) ?></span></li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="product__info">
                                <div class="product__count">
                                    <button class="product__count-minus" type="button" aria-label="&minus;"></button>
                                    <input type="text" name="quantity" value="1" min="1" max="999" inputmode="numeric" aria-label="Кількість">
                                    <button class="product__count-plus" type="button" aria-label="+"></button>
                                </div>

                                <div class="product__pricebox">
                                    <p class="product__price" id="dynamic-price"><?= hp_price($product['price'] ?? 0) ?></p>
                                </div>
                                <script>var product_id = <?= (int) $productId ?>;</script>
                            </div>

                            <div class="product__actions">
                                <button id="button-cart" class="product__add btn-2" type="button" onclick="addToCart();">
                                    Додати в кошик
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.33268 2.66699H6.66602V6.66699L2.66602 6.66699V9.33366H6.66602V13.3337H9.33268V9.33366H13.3327V6.66699L9.33268 6.66699V2.66699Z" fill="white"/>
                                    </svg>
                                </button>
                                <button class="product__quick" type="button" data-quick-open>
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12z"/></svg>
                                    Купити в 1 клік
                                </button>
                            </div>

                            <ul class="product__perks">
                                <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17h4V5H2v12h3M20 17h2v-6l-3-5h-5v11h3"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>Доставка Новою поштою по всій Україні</li>
                                <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>Оплата карткою або при отриманні</li>
                                <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.4-3 8.3-7 9-4-.7-7-4.6-7-9V6z"/><path d="m9 12 2 2 4-4"/></svg>Оригінальна продукція TM Hydrophob</li>
                            </ul>
                        </form>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const priceBox = document.getElementById('dynamic-price');
    const productForm = document.querySelector('#product');
    const quantityInput = productForm.querySelector('input[name="quantity"]');
    const optionInputs = productForm.querySelectorAll('select[name^="option"]');

    const minusBtn = document.querySelector('.product__count-minus');
    const plusBtn = document.querySelector('.product__count-plus');

    function updatePrice() {
        const fd = new FormData();
        fd.append('quantity', quantityInput.value);

        optionInputs.forEach(el => fd.append(el.name, el.value));

        fetch('https://hydrophob.net.ua/index.php?route=product/product/updatePrice&product_id=' + product_id, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(json => {
            if (json.price) priceBox.innerHTML = json.price;
        })
        .catch(err => console.error(err));
    }

    if (minusBtn) minusBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value) || 1;
        if (val > 1) {
            quantityInput.value = val - 1;
            updatePrice();
        }
    });

    if (plusBtn) plusBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value) || 1;
        if (val < 999) {
            quantityInput.value = val + 1;
            updatePrice();
        }
    });

    quantityInput.addEventListener('input', () => {
        let val = parseInt(quantityInput.value) || 1;
        if (val < 1) val = 1;
        if (val > 999) val = 999;
        quantityInput.value = val;
        updatePrice();
    });

    optionInputs.forEach(el => el.addEventListener('change', updatePrice));
});

function addToCart() {
    const quantity = parseInt(document.querySelector('input[name="quantity"]').value) || 1;
    const optionInputs = document.querySelectorAll('select[name^="option"]');
    const options = {};
    optionInputs.forEach(el => {
        const option_id = el.name.match(/\d+/)[0];
        options[option_id] = el.value;
    });

    $.ajax({
        url: 'https://hydrophob.net.ua/index.php?route=checkout/cart/add',
        type: 'post',
        data: { product_id: product_id, quantity: quantity, option: options },
        dataType: 'json',
        beforeSend: function() { $('#cart > button').button('loading'); },
        complete: function() { $('#cart > button').button('reset'); },
        success: function(json) {
            $('.alert-dismissible, .text-danger').remove();
            if (json['redirect']) { location = json['redirect']; }
            if (json['success']) {
                $('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                setTimeout(function () {
                    $('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
                }, 100);
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                $('#cart > ul').load('https://hydrophob.net.ua/index.php?route=common/cart/info ul li');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}
</script>
                        <div class="product__bottom">
                            <div class="product__selects">
                                <button class="product__selects-btn active">Опис</button>
								<button class="product__selects-btn">Характеристики</button>
								<button class="product__selects-btn">Інструкція</button>
								<button class="product__selects-btn">Відгуки (2)</button>
                            </div>
                            <div class="product__result">
                                <div class="product__result-content active">
									<?= $description ?>
                                </div>
                                <div class="product__result-content">
									<ul class="product__specs">
										<li class="product__specs-group">Основні</li>
										<li class="product__specs-row"><span class="product__specs-name">Модель</span><span class="product__specs-value"><?= hp_e($product['model'] ?? '') ?></span></li>
										<li class="product__specs-row"><span class="product__specs-name">Категорія</span><span class="product__specs-value"><?= hp_e($categoryTitle) ?></span></li>
										<li class="product__specs-row"><span class="product__specs-name">Бренд</span><span class="product__specs-value">HYDROPHOB</span></li>
										<li class="product__specs-row"><span class="product__specs-name">Країна виробництва</span><span class="product__specs-value">Україна</span></li>
									</ul>
                                </div>
                                <div class="product__result-content">
									<p><b>Як застосовувати засіб:</b></p>
									<ol class="product__instruction">
										<li>Очистіть і знежирте поверхню — вона має бути сухою та без пилу.</li>
										<li>Нанесіть тонкий рівномірний шар засобу мікрофіброю або аплікатором.</li>
										<li>Дайте полімеризуватись 10–15 хвилин, не торкаючись поверхні.</li>
										<li>Зніміть залишки чистою сухою серветкою й розполіруйте до блиску.</li>
										<li>Не мийте поверхню наступні 12 годин для повної фіксації покриття.</li>
									</ol>
									<p>Зберігати при температурі від +5 до +30 °C, берегти від дітей та прямих сонячних променів.</p>
                                </div>
                                <div class="product__result-content">
									<div class="product__review">
										<div class="product__review-head">
											<b>Олексій</b>
											<span class="product__review-stars" aria-label="5 з 5">★★★★★</span>
											<span class="product__review-date">12.08.2026</span>
										</div>
										<p>Наніс на кузов після мийки — вода реально збирається в краплі та скочується. Ефект тримається вже другий місяць.</p>
									</div>
									<div class="product__review">
										<div class="product__review-head">
											<b>Марина</b>
											<span class="product__review-stars" aria-label="5 з 5">★★★★★</span>
											<span class="product__review-date">03.07.2026</span>
										</div>
										<p>Проста в застосуванні, запах не різкий. Скло після обробки майже не брудниться, дуже задоволена.</p>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       </section>

<?php
$faqItems = [
    ['question' => 'Скільки тримається гідрофобне покриття?', 'answer' => 'Залежно від поверхні та умов експлуатації ефект зберігається від 6 до 18 місяців. На склі та лакофарбовому покритті авто — довше, на текстилі — менше.'],
    ['question' => 'Як наносити засіб правильно?', 'answer' => 'Поверхню очистіть і знежирте, нанесіть тонкий шар засобу мікрофіброю, дайте полімеризуватись 10-15 хвилин і зніміть залишки сухою серветкою.'],
    ['question' => 'Чи можна використовувати на кількох поверхнях?', 'answer' => 'Так, більшість засобів лінійки підходять для скла, металу, пластику та текстилю — уточнюйте сумісність у характеристиках конкретного товару.'],
    ['question' => 'Що робити, якщо ефект гідрофобності зник раніше строку?', 'answer' => 'Найчастіше причина — абразивне миття або хімічно агресивні мийні засоби. Повторно нанесіть засіб на очищену поверхню.'],
];

$relatedProducts = [];
foreach (hp_products() as $rp) {
    $rid = (int) $rp['product_id'];
    if ($rid === $productId) continue;
    if (!array_intersect($product['categories'] ?? [], $rp['categories'] ?? [])) continue;
    $relatedProducts[] = $rp;
    if (count($relatedProducts) >= 8) break;
}

$arrowPrevSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="42" viewBox="0 0 14 42" fill="none"><path d="M13.6238 3.70761C13.744 3.50915 13.8379 3.27569 13.9003 3.02057C13.9626 2.76544 13.9922 2.49364 13.9872 2.2207C13.9823 1.94775 13.9429 1.67899 13.8714 1.42978C13.7999 1.18056 13.6977 0.955764 13.5705 0.76822C13.4433 0.580676 13.2937 0.43406 13.1302 0.33674C12.9667 0.239421 12.7926 0.193306 12.6177 0.201027C12.4428 0.208748 12.2705 0.270155 12.1108 0.381741C11.9511 0.493327 11.8071 0.652908 11.6869 0.851371L0.364118 19.5604C0.130287 19.9464 0 20.4574 0 20.9885C0 21.5197 0.130287 22.0307 0.364118 22.4166L11.6869 41.1277C11.8063 41.3305 11.9503 41.4943 12.1106 41.6095C12.2709 41.7247 12.4442 41.7891 12.6206 41.7989C12.797 41.8087 12.9728 41.7637 13.1379 41.6665C13.3031 41.5693 13.4542 41.4219 13.5825 41.2328C13.7108 41.0438 13.8137 40.8168 13.8854 40.5651C13.957 40.3134 13.9958 40.042 13.9997 39.7667C14.0035 39.4914 13.9723 39.2176 13.9077 38.9613C13.8432 38.7049 13.7467 38.4712 13.6238 38.2736L3.16418 20.9885L13.6238 3.70761Z" fill="#161616"/></svg>';
$arrowNextSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="42" viewBox="0 0 14 42" fill="none"><path d="M0.376226 3.70761C0.256047 3.50915 0.162095 3.27569 0.0997324 3.02057C0.0373697 2.76544 0.00781822 2.49364 0.0127659 2.2207C0.0177135 1.94775 0.0570641 1.67899 0.128569 1.42978C0.200074 1.18056 0.302334 0.955764 0.42951 0.76822C0.556686 0.580676 0.706289 0.43406 0.869775 0.33674C1.03326 0.239421 1.20743 0.193306 1.38233 0.201027C1.55724 0.208748 1.72946 0.270155 1.88916 0.381741C2.04886 0.493327 2.19291 0.652908 2.31309 0.851371L13.6359 19.5604C13.8697 19.9464 14 20.4574 14 20.9885C14 21.5197 13.8697 22.0307 13.6359 22.4166L2.31309 41.1277C2.1937 41.3305 2.04968 41.4943 1.8894 41.6095C1.72911 41.7247 1.55575 41.7891 1.37939 41.7989C1.20303 41.8087 1.02718 41.7637 0.862059 41.6665C0.696937 41.5693 0.545833 41.4219 0.417525 41.2328C0.289217 41.0438 0.186262 40.8168 0.11464 40.5651C0.0430183 40.3134 0.00415802 40.042 0.000315666 39.7667C-0.00352669 39.4914 0.0277252 39.2176 0.0922575 38.9613C0.156789 38.7049 0.253315 38.4712 0.376226 38.2736L10.8358 20.9885L0.376226 3.70761Z" fill="#161616"/></svg>';

function hp_product_slide(array $p): string
{
    $name = hp_t($p, 'name');
    $href = 'product.php?id=' . (int) $p['product_id'];
    return '<div class="swiper-slide"><div class="product__item">' .
        '<a class="product__item-image" href="' . hp_e($href) . '">' .
        '<img src="' . hp_cache_img($p['image'] ?? '', 450) . '" alt="' . hp_e($name) . '" title="' . hp_e($name) . '" loading="lazy" onerror="this.src=\'' . HP_CDN . 'image/placeholder.png\'">' .
        '</a>' .
        '<div class="product__item-content">' .
        '<a class="product__item-title" href="' . hp_e($href) . '"><h3>' . hp_e($name) . '</h3></a>' .
        '<p class="product__item-price">' . hp_price($p['price'] ?? 0) . '</p>' .
        '<button class="product__item-add btn-2" type="button">Додати в кошик' .
        '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.56706 2.90039H6.90039V6.90039L2.90039 6.90039V9.56706H6.90039V13.5671H9.56706V9.56706H13.5671V6.90039L9.56706 6.90039V2.90039Z" fill="white"/></svg>' .
        '</button>' .
        '</div></div></div>';
}
?>

       <section class="product__faq">
           <div class="container">
               <h2 class="product__faq-title">Часті запитання</h2>
               <div class="product__faq-list" data-faq>
                   <?php foreach ($faqItems as $i => $item): ?>
                   <div class="product__faq-item<?= $i === 0 ? ' active' : '' ?>">
                       <button type="button" class="product__faq-btn" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" data-faq-toggle>
                           <span class="product__faq-question"><?= hp_e($item['question']) ?></span>
                           <span class="product__faq-icon" aria-hidden="true">
                               <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                           </span>
                       </button>
                       <div class="product__faq-answer"<?= $i !== 0 ? ' hidden' : '' ?>><?= hp_e($item['answer']) ?></div>
                   </div>
                   <?php endforeach; ?>
               </div>
           </div>
       </section>




       <?php if ($relatedProducts): ?>
       <section class="product__related">
           <div class="container">
               <div class="product__related-head">
                   <h2 class="product__related-title">З цим товаром також купують</h2>
                   <div class="product__related-btns">
                       <button class="product__related-prev" aria-label="Попередній слайд"><?= $arrowPrevSvg ?></button>
                       <button class="product__related-next" aria-label="Наступний слайд"><?= $arrowNextSvg ?></button>
                   </div>
               </div>
               <div class="product__related-slider swiper">
                   <div class="swiper-wrapper">
                       <?php foreach ($relatedProducts as $rp): ?>
                       <?= hp_product_slide($rp) ?>
                       <?php endforeach; ?>
                   </div>
               </div>
           </div>
       </section>
       <?php endif; ?>


       <section class="product__viewed" id="product-viewed" hidden>
           <div class="container">
               <div class="product__viewed-head">
                   <h2 class="product__viewed-title">Переглянуті нещодавно</h2>
                   <div class="product__viewed-btns">
                       <button class="product__viewed-prev" aria-label="Попередній слайд"><?= $arrowPrevSvg ?></button>
                       <button class="product__viewed-next" aria-label="Наступний слайд"><?= $arrowNextSvg ?></button>
                   </div>
               </div>
               <div class="product__viewed-slider swiper">
                   <div class="swiper-wrapper" id="product-viewed-wrapper"></div>
               </div>
           </div>
       </section>
       <script>
       /* «Переглянуті» — синхронно з localStorage, слайди мають бути в DOM до ініту Swiper у product.js */
       (function () {
           var slides = <?= json_encode(array_combine(
               array_map(function ($p) { return (string) $p['product_id']; }, hp_products()),
               array_map('hp_product_slide', hp_products())
           ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
           var viewed = [];
           try { viewed = JSON.parse(localStorage.getItem('hydro_viewed') || '[]'); } catch (e) {}
           var html = viewed.filter(function (id) { return id !== <?= (int) $productId ?>; })
               .map(function (id) { return slides[String(id)] || ''; }).join('');
           if (!html) return;
           document.getElementById('product-viewed-wrapper').innerHTML = html;
           document.getElementById('product-viewed').hidden = false;
       })();
       </script>

    </main>

<!-- Швидке замовлення: у верстці форма демонстраційна, на бойовому сайті шлеться на checkout/quick/confirm -->
<div class="qo" id="quick-order" aria-hidden="true">
    <div class="qo__overlay" data-quick-close></div>
    <div class="qo__box" role="dialog" aria-modal="true" aria-labelledby="qo-title">
        <button type="button" class="qo__close" data-quick-close aria-label="Закрити">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>

        <p class="qo__title" id="qo-title">Швидке замовлення</p>
        <p class="qo__sub">Лишіть імʼя та номер — передзвонимо, уточнимо доставку й оформимо за вас.</p>

        <div class="qo__product">
            <?php if (!empty($sliderImages[0])): ?><img class="qo__product-img" src="<?= hp_cache_img($sliderImages[0], 150) ?>" alt="<?= hp_e($name) ?>" width="72" height="72" loading="lazy"><?php endif; ?>
            <div class="qo__product-info">
                <p class="qo__product-name"><?= hp_e($name) ?></p>
                <p class="qo__product-price"><span data-qo-qty>1</span> &times; <span data-qo-price><?= hp_price($product['price'] ?? 0) ?></span></p>
            </div>
        </div>

        <form class="qo__form" data-quick-form>
            <label class="qo__field">
                <span class="qo__label">Ваше імʼя</span>
                <input type="text" name="firstname" autocomplete="name" required>
                <em class="qo__error" data-error="firstname"></em>
            </label>
            <label class="qo__field">
                <span class="qo__label">Номер телефону</span>
                <input type="tel" name="telephone" autocomplete="tel" placeholder="+38 (0__) ___-__-__" required>
                <em class="qo__error" data-error="telephone"></em>
            </label>
            <label class="qo__field">
                <span class="qo__label">E-mail (необовʼязково)</span>
                <input type="email" name="email" autocomplete="email">
                <em class="qo__error" data-error="email"></em>
            </label>
            <label class="qo__field">
                <span class="qo__label">Коментар до замовлення</span>
                <textarea name="comment" rows="2"></textarea>
            </label>

            <input type="text" name="company_website" class="qo__trap" tabindex="-1" autocomplete="off" aria-hidden="true">

            <em class="qo__error qo__error--common" data-error="warning"></em>

            <button type="submit" class="qo__submit btn-2">Замовити дзвінок</button>
            <p class="qo__note">Дані потрібні лише для звʼязку щодо цього замовлення.</p>
        </form>

        <div class="qo__done" hidden>
            <span class="qo__done-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
            <p class="qo__done-text"></p>
            <button type="button" class="qo__done-btn btn-2" data-quick-close>Закрити</button>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('quick-order');
    if (!modal) return;

    var productForm = document.getElementById('product');
    var form   = modal.querySelector('[data-quick-form]');
    var done   = modal.querySelector('.qo__done');
    var qtyOut = modal.querySelector('[data-qo-qty]');
    var priceOut = modal.querySelector('[data-qo-price]');
    var lastFocus = null;

    function open() {
        lastFocus = document.activeElement;
        modal.querySelectorAll('.qo__error').forEach(function (el) { el.textContent = ''; });
        modal.querySelectorAll('.qo__field').forEach(function (el) { el.classList.remove('has-error'); });
        form.hidden = false;
        done.hidden = true;

        var qty = productForm ? productForm.querySelector('input[name="quantity"]') : null;
        if (qty && qtyOut) qtyOut.textContent = qty.value || '1';

        var priceBox = document.getElementById('dynamic-price');
        if (priceBox && priceOut) priceOut.textContent = priceBox.textContent.trim();

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('qo-open');
        setTimeout(function () { var f = form.querySelector('input[name="firstname"]'); if (f) f.focus(); }, 60);
    }

    function close() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('qo-open');
        if (lastFocus) lastFocus.focus();
    }

    document.querySelectorAll('[data-quick-open]').forEach(function (b) { b.addEventListener('click', open); });
    modal.querySelectorAll('[data-quick-close]').forEach(function (b) { b.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        form.hidden = true;
        done.hidden = false;
        done.querySelector('.qo__done-text').textContent = 'Дякуємо! Замовлення прийнято — менеджер зателефонує найближчим часом.';
        form.reset();
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://hydrophob.net.ua/catalog/view/theme/default/js/product.js?v=20260825b" type="text/javascript"></script>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
