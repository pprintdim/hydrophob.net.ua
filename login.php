<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Вхід';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-login">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <span>Вхід в особистий кабінет</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <div class="account__auth">
                            <div class="account__auth-card">
                                <h2>Новий клієнт</h2>
                                <p>Створіть акаунт, щоб бачити історію замовлень, зберігати адреси та список обраного.</p>
                                <div class="account__form">
                                    <a href="register.php" class="btn-2">Продовжити
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                            <div class="account__auth-card">
                                <h2>Постійний клієнт</h2>
                                <p>Введіть email і пароль від вашого акаунту.</p>
                                <form action="#" method="post" class="account__form" onsubmit="return false;">
                                    <div class="account__field">
                                        <label for="input-email">E-Mail</label>
                                        <input type="text" name="email" value="" placeholder="E-Mail" id="input-email" />
                                    </div>
                                    <div class="account__field" style="margin-top: 16px;">
                                        <label for="input-password">Пароль</label>
                                        <input type="password" name="password" value="" placeholder="Пароль" id="input-password" />
                                        <a href="#" class="account__link">Забули пароль?</a>
                                    </div>
                                    <button type="submit" class="btn-2">Увійти
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="account__otp-divider">або</div>

                        <div class="account__panel account__otp" id="otp-login">
                            <h2 class="account__panel-title">Вхід за одноразовим кодом</h2>
                            <p class="account__otp-desc">Без пароля: вкажіть email, отримайте код листом і підтвердьте вхід.</p>

                            <div class="account__otp-steps">
                                <span class="account__otp-step-badge is-active" data-otp-badge="email">
                                    <span class="account__otp-step-num">1</span> Email
                                </span>
                                <span class="account__otp-step-badge" data-otp-badge="code">
                                    <span class="account__otp-step-num">2</span> Код з листа
                                </span>
                                <span class="account__otp-step-badge" data-otp-badge="done">
                                    <span class="account__otp-step-num">3</span> Готово
                                </span>
                            </div>

                            <div class="account__otp-panel is-active" data-otp-panel="email">
                                <div class="account__form-grid">
                                    <div class="account__field">
                                        <label for="otp-email">E-Mail</label>
                                        <input type="email" id="otp-email" placeholder="E-Mail" autocomplete="email" />
                                    </div>
                                </div>
                                <div class="account__actions" style="justify-content: flex-end;">
                                    <button type="button" class="btn-2" id="otp-send-btn">Надіслати код
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="account__otp-panel" data-otp-panel="code">
                                <p class="account__otp-hint">Код надіслано на <b id="otp-email-echo">email</b>. Термін дії — 5 хвилин.</p>
                                <div class="account__form-grid">
                                    <div class="account__field">
                                        <label for="otp-code">Код підтвердження</label>
                                        <input type="text" id="otp-code" class="account__otp-code-input" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="0000" />
                                    </div>
                                </div>
                                <div class="account__actions">
                                    <button type="button" class="account__otp-resend" id="otp-resend-btn" disabled>Надіслати ще раз (<span id="otp-timer">60</span> с)</button>
                                    <button type="button" class="btn-2" id="otp-confirm-btn">Підтвердити
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="account__otp-panel" data-otp-panel="done">
                                <div class="account__otp-done">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span>Вхід підтверджено. Перенаправляємо до особистого кабінету…</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
	</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
