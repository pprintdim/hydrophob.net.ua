<?php
// Text
$_['text_information']  = 'Информация';
$_['text_service']      = 'Служба поддержки';
$_['text_extra']        = 'Дополнительно';
$_['text_contact']      = 'Контакты';
$_['text_return']       = 'Возврат товара';
$_['text_sitemap']      = 'Карта сайта';
$_['text_manufacturer'] = 'Производители';
$_['text_voucher']      = 'Подарочные сертификаты';
$_['text_affiliate']    = 'Партнерская программа';
$_['text_special']      = 'Акции';
$_['text_account']      = 'Личный Кабинет';
$_['text_order']        = 'История заказов';
$_['text_wishlist']     = 'Закладки';
$_['text_newsletter']   = 'Рассылка';
if (isset($_SERVER['REQUEST_URI']) && in_array(trim($_SERVER['REQUEST_URI'], '/'), ['', 'ru'])) {
    $_['text_powered'] = '<a href="https://ocmod.net/moduli/v/3-0/" target="_blank">OpenCart Extensions</a><br> %s &copy; %s';
} else {
    $_['text_powered'] = 'OpenCart<br> %s &copy; %s';
}
$_['otp_need_email'] = 'Укажите e-mail, чтобы получить код';
$_['text_for_customers'] = 'Покупателям';
$_['text_on_map'] = 'Мы на карте';
$_['text_in_cart'] = 'В корзине';
$_['text_review_title'] = 'Оставить отзыв';
$_['text_review_product'] = 'Товар';
$_['text_review_product_ph'] = 'Начните вводить название товара';
$_['text_review_name'] = 'Ваше имя';
$_['text_review_text'] = 'Отзыв';
$_['text_review_rating'] = 'Оценка';
$_['text_review_send'] = 'Отправить отзыв';
$_['text_review_thanks'] = 'Спасибо! Отзыв отправлен на модерацию.';
$_['text_review_pick'] = 'Выберите товар из списка';
$_['text_review_phone'] = 'Номер телефона';
$_['text_review_email'] = 'E-mail';
$_['text_wish_added'] = 'Добавлено в избранное';
$_['text_wish_removed'] = 'Убрано из избранного';
$_['text_added_toast'] = 'Товар добавлен в корзину';

// единый попап успеха
$_['text_ok_title'] = 'Готово';
$_['text_ok_close'] = 'Понятно';

// контакты в попапах берутся из аккаунта — менять их в кабинете
$_['text_edit_in_account'] = 'Изменить в кабинете';
