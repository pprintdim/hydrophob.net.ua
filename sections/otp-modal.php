<!-- Спільна модалка коду підтвердження: одна на весь сайт, відкривається будь-яким [data-code-trigger] -->
<div class="otp-modal" data-code-modal hidden role="dialog" aria-modal="true" aria-label="Введіть код підтвердження">
    <div class="otp-modal__dialog">
        <button type="button" class="otp-modal__close" data-modal-close aria-label="Закрити">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <h2 class="otp-modal__title" data-code-title>Введіть код підтвердження</h2>
        <p class="otp-modal__text">Ми надіслали 6-значний код на <b data-code-mail>ваш email</b></p>

        <div class="otp-code">
            <input class="otp-code__cell" inputmode="numeric" maxlength="1" aria-label="Цифра коду 1" data-code-cell>
            <input class="otp-code__cell" inputmode="numeric" maxlength="1" aria-label="Цифра коду 2" data-code-cell>
            <input class="otp-code__cell" inputmode="numeric" maxlength="1" aria-label="Цифра коду 3" data-code-cell>
            <input class="otp-code__cell" inputmode="numeric" maxlength="1" aria-label="Цифра коду 4" data-code-cell>
            <input class="otp-code__cell" inputmode="numeric" maxlength="1" aria-label="Цифра коду 5" data-code-cell>
            <input class="otp-code__cell" inputmode="numeric" maxlength="1" aria-label="Цифра коду 6" data-code-cell>
        </div>

        <p class="otp-modal__error" data-code-error hidden></p>

        <div class="otp-modal__actions">
            <button type="button" class="btn-2" data-code-confirm aria-disabled="true">Підтвердити
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            </button>
            <p class="otp-modal__timer" data-code-timer></p>
            <button type="button" class="otp-modal__resend" data-code-resend hidden>Надіслати код повторно</button>
        </div>
    </div>
</div>
