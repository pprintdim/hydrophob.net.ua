<?php
class ControllerCommonHome extends Controller {
	public function index() {
		// Вітальний тизер більше не окрема сторінка: за тією ж логікою показів
		// він лягає поверх головної й після перегляду їде вниз, відкриваючи її.
		$welcome = $this->shouldShowWelcome();

		// мета магазину одне на всі мови, тому для не-дефолтних мов беремо
		// переклад із мовного файла (catalog/language/<code>/common/home.php)
		$this->load->language('common/home');

		$meta_title = $this->language->get('heading_meta_title');
		$meta_description = $this->language->get('heading_meta_description');

		$this->document->setTitle($meta_title !== 'heading_meta_title' && $meta_title !== ''
			? $meta_title
			: $this->config->get('config_meta_title'));
		$this->document->setDescription($meta_description !== 'heading_meta_description' && $meta_description !== ''
			? $meta_description
			: $this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		// canonical ставимо завжди: /index.php і /?utm_... — теж головна.
		// Саме url->link, а не config_url: інакше /ru/ канонікалилась на
		// українську версію і російська головна не потрапляла в індекс
		$this->document->addLink($this->url->link('common/home'), 'canonical');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['welcome_overlay'] = '';

		if ($welcome) {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/welcome.css');

			// прогрів роликів під прелоадером накладки — у <head>, до її inline-скрипта
			$warmup = 'catalog/view/theme/default/js/video-warmup.js';
			$this->document->addScript($warmup . '?v=' . (int)@filemtime(DIR_APPLICATION . '../' . $warmup));

			$data['welcome_overlay'] = $this->welcomeOverlay();
		}

		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}

	/**
	 * Пошуковий чи соцмережевий бот? Вимірювачі швидкості (Lighthouse,
	 * PageSpeed, GTmetrix) сюди НЕ входять — їм показуємо вітальну.
	 */
	/**
	 * Вітальний тизер поверх головної. Живе тут, а не окремим маршрутом:
	 * сторінки common/welcome більше немає, лишилась сама накладка.
	 */
	private function welcomeOverlay() {
		$this->load->language('common/welcome');

		$data['logo'] = $this->welcomeAsset('welcome_logo', 'catalog/view/theme/default/img/logo-animated.svg');
		$data['video'] = $this->welcomeAsset('welcome_video', 'catalog/view/theme/default/vid/welcome.mp4');
		// мобільний варіант: вибраний в адмінці, інакше — легка копія, яку
		// готує tool/video_mobile з того самого ролика
		$data['video_mobile'] = $this->welcomeAsset('welcome_video_mobile', '');

		if ($data['video_mobile'] === '' && $data['video'] !== '') {
			$this->load->model('tool/video_mobile');

			$data['video_mobile'] = $this->model_tool_video_mobile->get($data['video']);
		}
		$data['poster'] = $this->welcomeAsset('welcome_poster', 'image/catalog/video-posters/welcome.webp');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_eyebrow'] = $this->language->get('text_eyebrow');
		$data['text_lead'] = $this->language->get('text_lead');
		$data['text_play'] = $this->language->get('text_play');
		$data['prefetch'] = $this->welcomeHeroVideos('catalog/view/theme/default/image/');

		// той самий прогрів, але для телефона — легкими копіями, інакше накладка
		// тягне десктопні ролики першого екрана на мобільному інтернеті
		$this->load->model('tool/video_mobile');

		$data['prefetch_mobile'] = array_values(array_unique(array_filter(array_map(function ($video) {
			return $this->model_tool_video_mobile->get($video);
		}, $data['prefetch']))));

		return $this->load->view('common/welcome_overlay', $data);
	}

	/**
	 * Файл тизера: спершу те, що вибрали в адмінці, потім типовий для теми.
	 * Якщо файлу немає на диску — повертаємо порожнє, щоб не ловити 404.
	 */
	/**
	 * Відео перших екранів головної — прогріваємо їх, поки людина дивиться
	 * тизер. Коли накладка поїде, головна вже має ролики в кеші й показує
	 * їх без паузи.
	 */
	private function welcomeHeroVideos($theme_image) {
		$videos = array();

		$query = $this->db->query("SELECT setting FROM " . DB_PREFIX . "module WHERE code = 'hero' ORDER BY module_id ASC");

		foreach ($query->rows as $row) {
			$setting = json_decode($row['setting'], true);

			if (!is_array($setting) || empty($setting['status'])) {
				continue;
			}

			$videos[] = !empty($setting['video_webm']) ? $setting['video_webm'] : $theme_image . 'hero-video.webm';
			$videos[] = !empty($setting['video_mp4']) ? $setting['video_mp4'] : $theme_image . 'hero-video.mp4';
		}

		if (!$videos) {
			$videos = array($theme_image . 'hero-video.webm', $theme_image . 'hero-video.mp4');
		}

		$videos = array_values(array_unique(array_map(function ($video) {
			return html_entity_decode((string)$video, ENT_QUOTES, 'UTF-8');
		}, $videos)));

		return array_values(array_filter($videos, function ($video) {
			return is_file(DIR_APPLICATION . '../' . $video);
		}));
	}

	private function welcomeAsset($key, $fallback) {
		$path = trim((string)$this->config->get($key));

		if ($path === '' || !is_file(DIR_APPLICATION . '../' . $path)) {
			$path = $fallback;
		}

		return is_file(DIR_APPLICATION . '../' . $path) ? $path : '';
	}


	/**
	 * Вітальна сторінка: перший візит — завжди; далі протягом місяця (стільки
	 * живе кука) ще до трьох показів у випадкові моменти при відкритті головної,
	 * не частіше ніж раз на 6 годин. Пошуковим ботам і вимірювачам швидкості
	 * вітальну не показуємо ніколи. ?welcome=1 показує примусово (для перевірки).
	 */
	private function shouldShowWelcome() {
		// вимкнули в адмінці — тизера немає взагалі
		if ($this->config->has('welcome_status') && !$this->config->get('welcome_status')) {
			return false;
		}

		if ($this->isSearchBot()) {
			return false;
		}

		if (isset($this->request->get['welcome'])) {
			return (bool)$this->request->get['welcome'];
		}

		$now = time();
		$month = 60 * 60 * 24 * 30;
		$raw = isset($this->request->cookie['hydro_welcome']) ? (string)$this->request->cookie['hydro_welcome'] : '';
		$parts = $raw !== '' ? array_map('intval', explode('|', $raw)) : array();

		$shows = isset($parts[0]) ? $parts[0] : 0;
		$last = isset($parts[1]) ? $parts[1] : 0;
		$first = isset($parts[2]) ? $parts[2] : 0;

		// стара мітка першого візиту (hydro_visited) — перший показ уже був
		if ($shows === 0 && $first === 0 && isset($this->request->cookie['hydro_visited'])) {
			$shows = 1;
			$last = $now;
			$first = $now;
		}

		if ($first === 0 || $now - $first > $month) {
			$first = $now;
			$shows = 0;
		}

		$show = false;

		// частоту задає адміністратор; типові значення — ті, з якими тизер
		// працював досі
		$limit = (int)$this->config->get('welcome_shows') ?: 4;
		$chance = (int)$this->config->get('welcome_chance') ?: 20;
		$pause = $this->config->get('welcome_interval');
		$pause = $pause === null || $pause === '' ? 6 : (int)$pause;

		if ($shows === 0) {
			$show = true;
		} elseif ($shows < $limit && $now - $last > $pause * 3600 && mt_rand(1, 100) <= $chance) {
			$show = true;
		}

		if ($show) {
			$shows++;
			$last = $now;
		}

		$secure = isset($this->request->server['HTTPS']) && $this->request->server['HTTPS'] && $this->request->server['HTTPS'] != 'off';
		setcookie('hydro_welcome', $shows . '|' . $last . '|' . $first, $first + $month, '/', '', $secure, true);

		return $show;
	}

	private function isSearchBot() {
		$agent = isset($this->request->server['HTTP_USER_AGENT']) ? strtolower($this->request->server['HTTP_USER_AGENT']) : '';

		if (!$agent) {
			return false;
		}

		// вимірювачам швидкості тизер теж ні до чого: важке відео поверх
		// сторінки псує їм метрики, а міряють вони саме головну
		foreach (array('lighthouse', 'pagespeed', 'gtmetrix', 'pingdom', 'webpagetest') as $speed) {
			if (strpos($agent, $speed) !== false) {
				return true;
			}
		}

		return (bool)preg_match('/bot|crawl|spider|slurp|yandex|bingpreview|facebookexternalhit|telegrambot|whatsapp|twitterbot|linkedinbot|pinterest|embedly|quora|applebot|duckduck|semrush|ahrefs|mj12|petalbot|headlesschrome/', $agent);
	}
}
