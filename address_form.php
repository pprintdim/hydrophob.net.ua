<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Адреса';
$pageExtraHead = '<link rel="stylesheet" href="data/search-ui.css">';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-address">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <a href="account.php">Особистий кабінет</a><span>/</span>
                    <a href="address_list.php">Адресна книга</a><span>/</span>
                    <span>Редагування адреси</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Редагування адреси</h1>

                        <form action="#" method="post" class="account__form" onsubmit="return false;">
                            <div class="account__panel account__fieldset">
                                <div class="account__form-grid">
                                    <div class="account__field">
                                        <label for="input-firstname">Ім'я</label>
                                        <input type="text" name="firstname" value="Іван" placeholder="Ім'я" id="input-firstname" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-lastname">Прізвище</label>
                                        <input type="text" name="lastname" value="Петренко" placeholder="Прізвище" id="input-lastname" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-company">Компанія</label>
                                        <input type="text" name="company" value="" placeholder="Компанія" id="input-company" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-address-1">Адреса 1</label>
                                        <input type="text" name="address_1" value="вул. Хрещатик, 22, кв. 15" placeholder="Адреса 1" id="input-address-1" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-address-2">Адреса 2</label>
                                        <input type="text" name="address_2" value="" placeholder="Адреса 2" id="input-address-2" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-city">Місто</label>
                                        <input type="text" name="city" value="Київ" placeholder="Місто" id="input-city" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-postcode">Поштовий індекс</label>
                                        <input type="text" name="postcode" value="01001" placeholder="Поштовий індекс" id="input-postcode" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-country">Країна</label>
                                        <select name="country_id" id="input-country">
                                            <option value="1" selected="selected">Україна</option>
                                        </select>
                                    </div>
                                    <div class="account__field">
                                        <label for="input-zone">Область</label>
                                        <select name="zone_id" id="input-zone">
                                            <option value="1" selected="selected">м. Київ</option>
                                            <option value="2">Київська область</option>
                                            <option value="3">Львівська область</option>
                                        </select>
                                    </div>
                                    <div class="account__field">
                                        <label>Адреса за замовчуванням</label>
                                        <div class="account__radio-group">
                                            <label class="account__radio"><input type="radio" name="default" value="1" checked="checked" />Так</label>
                                            <label class="account__radio"><input type="radio" name="default" value="0" />Ні</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="account__actions">
                                <a href="address_list.php" class="account__link">Назад до адресної книги</a>
                                <input type="submit" value="Зберегти" class="btn-2" />
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </section>
	</main>
<?php
require __DIR__ . '/sections/footer.php';
$pageExtraFoot = '<script src="data/search-ui.js"></script>\n<script src="data/crosslinks.js"></script>';
require __DIR__ . '/sections/document-end.php';
