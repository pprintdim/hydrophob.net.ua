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