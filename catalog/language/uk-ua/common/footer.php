<?php
// Text
$_['text_information'] = 'Інформація';
$_['text_service'] = 'Служба підтримки';
$_['text_extra'] = 'Додатково';
$_['text_contact'] = 'Контакти';
$_['text_return'] = 'Повернення товару';
$_['text_sitemap'] = 'Карта сайту';
$_['text_manufacturer'] = 'Виробники';
$_['text_voucher'] = 'Подарункові сертифікати';
$_['text_affiliate'] = 'Партнерська програма';
$_['text_special'] = 'Акції';
$_['text_account'] = 'Особистий Кабінет';
$_['text_order'] = 'Історія замовлень';
$_['text_wishlist'] = 'Закладки';
$_['text_newsletter'] = 'Розсилка';
if (isset($_SERVER['REQUEST_URI']) && in_array(trim($_SERVER['REQUEST_URI'], '/'), ['', 'ua', 'uk'])) {
    $_['text_powered'] = '<a href="https://ocmod.net/ua/moduli/v/3-0/" target="_blank">OpenCart Extensions</a><br> %s &copy; %s';
} else {
    $_['text_powered'] = 'OpenCart<br> %s &copy; %s';
}
$_['otp_need_email'] = 'Вкажіть e-mail, щоб отримати код';
$_['text_for_customers'] = 'Покупцям';
$_['text_on_map'] = 'Ми на карті';
$_['text_in_cart'] = 'В кошику';
$_['text_review_title'] = 'Залишити відгук';
$_['text_review_product'] = 'Товар';
$_['text_review_product_ph'] = 'Почніть вводити назву товару';
$_['text_review_name'] = 'Ваше ім\'я';
$_['text_review_text'] = 'Відгук';
$_['text_review_rating'] = 'Оцінка';
$_['text_review_send'] = 'Надіслати відгук';
$_['text_review_thanks'] = 'Дякуємо! Відгук надіслано на модерацію.';
$_['text_review_pick'] = 'Оберіть товар зі списку';
$_['text_review_phone'] = 'Номер телефону';
$_['text_review_email'] = 'E-mail';
$_['text_wish_added'] = 'Додано в обране';
$_['text_wish_removed'] = 'Прибрано з обраного';
$_['text_added_toast'] = 'Товар додано в кошик';

// єдиний попап успіху
$_['text_ok_title'] = 'Готово';
$_['text_ok_close'] = 'Зрозуміло';

// контакти в попапах беруться з акаунта — правити їх у кабінеті
$_['text_edit_in_account'] = 'Змінити в кабінеті';
