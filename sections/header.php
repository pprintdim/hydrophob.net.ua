<?php
$pageLangRedirect = $pageLangRedirect ?? 'https://hydrophob.net.ua/index.php?route=common/home';
?>
    <header class="header">
        <div class="container">
            <div class="header__inner">
                <a class="header__logo" href="index.php">
                    <img src="https://hydrophob.net.ua/catalog/view/theme/default/img/logo.svg" alt="">
                </a>
                <div class="header__content">
                    <nav class="header__menu">
                        <button class="header__menu-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M0.515398 0.514573C0.905923 0.124049 1.53909 0.124049 1.92961 0.514573L17.486 16.0709C17.8765 16.4614 17.8765 17.0946 17.486 17.4851C17.0954 17.8757 16.4623 17.8757 16.0717 17.4851L0.515399 1.92879C0.124874 1.53826 0.124874 0.905097 0.515398 0.514573Z" fill="white"/>
                                <path d="M0.515398 0.514573C0.905923 0.124049 1.53909 0.124049 1.92961 0.514573L17.486 16.0709C17.8765 16.4614 17.8765 17.0946 17.486 17.4851C17.0954 17.8757 16.4623 17.8757 16.0717 17.4851L0.515399 1.92879C0.124874 1.53826 0.124874 0.905097 0.515398 0.514573Z" fill="white"/>
                                <path d="M0.515398 0.514573C0.905923 0.124049 1.53909 0.124049 1.92961 0.514573L17.486 16.0709C17.8765 16.4614 17.8765 17.0946 17.486 17.4851C17.0954 17.8757 16.4623 17.8757 16.0717 17.4851L0.515399 1.92879C0.124874 1.53826 0.124874 0.905097 0.515398 0.514573Z" fill="white"/>
                                <path d="M0.513596 17.4853C0.123072 17.0948 0.123072 16.4616 0.513596 16.0711L16.0699 0.514714C16.4605 0.12419 17.0936 0.124189 17.4842 0.514714C17.8747 0.905238 17.8747 1.5384 17.4842 1.92893L1.92781 17.4853C1.53729 17.8758 0.904121 17.8758 0.513596 17.4853Z" fill="white"/>
                                <path d="M0.513596 17.4853C0.123072 17.0948 0.123072 16.4616 0.513596 16.0711L16.0699 0.514714C16.4605 0.12419 17.0936 0.124189 17.4842 0.514714C17.8747 0.905238 17.8747 1.5384 17.4842 1.92893L1.92781 17.4853C1.53729 17.8758 0.904121 17.8758 0.513596 17.4853Z" fill="white"/>
                                <path d="M0.513596 17.4853C0.123072 17.0948 0.123072 16.4616 0.513596 16.0711L16.0699 0.514714C16.4605 0.12419 17.0936 0.124189 17.4842 0.514714C17.8747 0.905238 17.8747 1.5384 17.4842 1.92893L1.92781 17.4853C1.53729 17.8758 0.904121 17.8758 0.513596 17.4853Z" fill="white"/>
                            </svg>
                        </button>
                        <ul>
                            <li>
                                <a href="catalog.php" title="Каталог">Каталог</a>
                            </li>
                            <li>
                                <a href="about.php" title="Про нас">Про нас</a>
                            </li>
                            <li>
                                <a href="delivery.php" title="Доставка та оплата">Доставка та оплата</a>
                            </li>
                            <li>
                                <a href="contact.php" title="Контакти">Контакти</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="header__block">
<div class="header__icons">
<a href="account.php" class="header__account" title="Особистий кабінет"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
<a href="wishlist.php" class="header__account header__wishlist" title="Вибране"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0l-1.4 1.4-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4l8.8 8.8 8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg></a>

                        <div class="header__search" id="search">
                            <button class="header__search-open" aria-label="Пошук"></button>
                        </div>
						<button class="header__busket" data-loading-text=""></button>
<section id="cart"  class="busket">
	<div class="busket__top">
		<p class="busket__title">Кошик</p>
	</div>
  <ul class="busket__inner">
     				<li>
					<p class="busket__empty">Ваш кошик порожній!</p>
				</li>
			</ul>
</section>
</div>


						<div class="header__lang">
    <div class="header__lang-selected">
                                    UA
                                                                </div>
    <div class="header__lang-content">
                                                        <a href="#" class="language-select" data-code="en-gb">
                    EN
                </a>
                                                <a href="#" class="language-select" data-code="ru-ru">
                    RU
                </a>
                        </div>
    <form action="https://hydrophob.net.ua/index.php?route=common/language/language" method="post" id="form-language">
        <input type="hidden" name="code" value="" />
        <input type="hidden" name="redirect" value="<?= hp_e($pageLangRedirect) ?>" />
    </form>
</div>

<script>
document.querySelectorAll('.header__lang-content .language-select').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var code = this.getAttribute('data-code');
        var form = document.getElementById('form-language');
        form.querySelector('input[name="code"]').value = code;
        form.submit();
    });
});
</script>

                        <button class="header__burger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="16" viewBox="0 0 24 16" fill="none">
                                <path d="M0 1C0 0.447715 0.447715 0 1 0H23C23.5523 0 24 0.447715 24 1C24 1.55228 23.5523 2 23 2H1C0.447716 2 0 1.55228 0 1Z" fill="white"/>
                                <path d="M0 1C0 0.447715 0.447715 0 1 0H23C23.5523 0 24 0.447715 24 1C24 1.55228 23.5523 2 23 2H1C0.447716 2 0 1.55228 0 1Z" fill="white"/>
                                <path d="M0 1C0 0.447715 0.447715 0 1 0H23C23.5523 0 24 0.447715 24 1C24 1.55228 23.5523 2 23 2H1C0.447716 2 0 1.55228 0 1Z" fill="white"/>
                                <path d="M0 8C0 7.44772 0.447715 7 1 7H23C23.5523 7 24 7.44772 24 8C24 8.55228 23.5523 9 23 9H1C0.447716 9 0 8.55228 0 8Z" fill="white"/>
                                <path d="M0 8C0 7.44772 0.447715 7 1 7H23C23.5523 7 24 7.44772 24 8C24 8.55228 23.5523 9 23 9H1C0.447716 9 0 8.55228 0 8Z" fill="white"/>
                                <path d="M0 8C0 7.44772 0.447715 7 1 7H23C23.5523 7 24 7.44772 24 8C24 8.55228 23.5523 9 23 9H1C0.447716 9 0 8.55228 0 8Z" fill="white"/>
                                <path d="M0 15C0 14.4477 0.447715 14 1 14H23C23.5523 14 24 14.4477 24 15C24 15.5523 23.5523 16 23 16H1C0.447716 16 0 15.5523 0 15Z" fill="white"/>
                                <path d="M0 15C0 14.4477 0.447715 14 1 14H23C23.5523 14 24 14.4477 24 15C24 15.5523 23.5523 16 23 16H1C0.447716 16 0 15.5523 0 15Z" fill="white"/>
                                <path d="M0 15C0 14.4477 0.447715 14 1 14H23C23.5523 14 24 14.4477 24 15C24 15.5523 23.5523 16 23 16H1C0.447716 16 0 15.5523 0 15Z" fill="white"/>
                              </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
