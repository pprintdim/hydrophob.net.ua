# hydrophob.com.ua

OpenCart 3.0.4.1, прод — Hetzner 46.224.100.254 (CloudPanel, site user `hydrophobcom`).

- Локалка: MAMP, http://localhost:8892/ (адмінка — /hp_panel/)
- БД одна — серверна, через SSH-тунель `127.0.0.1:3307` (LaunchAgent `com.pprintdim.hetzner-mysql-tunnel`)
- Картинки `image/catalog` та `image/cache` живуть лише на сервері; локально працює фолбек на прод (константа `DEV_IMAGE_FALLBACK` у config.php + патч `*/model/tool/image.php`)
