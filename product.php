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
            <h1 class="catalog__title page-title"><p>Товар не знайдено</p></h1>
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

$description = hp_t($product, 'description');
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
$pageLangRedirect = 'https://hydrophob.com.ua/index.php?route=product/product&product_id=' . $productId;

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
    <main class="main">
       <section class="product" id="product-product">
            <div class="container" id="content">
                <div class="product__inner">
                    <div class="product__left">
						<div class="product__slider-content">
							<div class="product__slider swiper">
								<div class="swiper-wrapper">
									<?php foreach ($sliderImages as $img): ?>
									<div class="swiper-slide">
										<img src="<?= hp_cache_img($img, 500) ?>" alt="<?= hp_e($name) ?>">
									</div>
									<?php endforeach; ?>
								</div>
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
                            <div class="product__info">

                                <div class="product__volume"><?= hp_e($product['model'] ?? '') ?></div>


<div class="product__count">
    <button class="product__count-minus" type="button"></button>
    <input type="text" name="quantity" value="1" min="1" max="999">
    <button class="product__count-plus" type="button"></button>
</div>


                                <p class="product__price" id="dynamic-price"><?= hp_price($product['price'] ?? 0) ?></p>
								<script>var product_id = <?= (int) $productId ?>;</script>

                            </div>

                            <button id="button-cart" class="product__add btn-2" type="button" onclick="addToCart();">
                                Додати в кошик
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9.33268 2.66699H6.66602V6.66699L2.66602 6.66699V9.33366H6.66602V13.3337H9.33268V9.33366H13.3327V6.66699L9.33268 6.66699V2.66699Z" fill="white"/>
                                </svg>
                            </button>
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

        fetch('https://hydrophob.com.ua/index.php?route=product/product/updatePrice&product_id=' + product_id, {
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
        url: 'https://hydrophob.com.ua/index.php?route=checkout/cart/add',
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
                $('#cart > ul').load('https://hydrophob.com.ua/index.php?route=common/cart/info ul li');
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
                                <button class="product__selects-btn active">загальне</button>
								<button class="product__selects-btn">характеристики</button>
                            </div>
                            <div class="product__result">
                                <div class="product__result-content active">
									<?= $description ?>
                                </div>
                                <div class="product__result-content">
									<table class="table table-bordered">
										<thead>
											<tr>
												<td colspan="2"><strong>Основні</strong></td>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Модель</td>
												<td><?= hp_e($product['model'] ?? '') ?></td>
											</tr>
										</tbody>
									</table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       </section>

    </main>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://hydrophob.com.ua/catalog/view/theme/default/js/product.js" type="text/javascript"></script>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
