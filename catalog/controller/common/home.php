<?php
class ControllerCommonHome extends Controller {
	public function index() {
		// вітальна сторінка з відео — за спільною для групи логікою показів
		if ($this->shouldShowWelcome()) {
			$this->response->setOutput($this->load->controller('common/welcome/render'));

			return;
		}

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
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}

	/**
	 * Пошуковий чи соцмережевий бот? Вимірювачі швидкості (Lighthouse,
	 * PageSpeed, GTmetrix) сюди НЕ входять — їм показуємо вітальну.
	 */
	/**
	 * Вітальна сторінка: перший візит — завжди; далі протягом місяця (стільки
	 * живе кука) ще до трьох показів у випадкові моменти при відкритті головної,
	 * не частіше ніж раз на 6 годин. Пошуковим ботам і вимірювачам швидкості
	 * вітальну не показуємо ніколи. ?welcome=1 показує примусово (для перевірки).
	 */
	private function shouldShowWelcome() {
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

		if ($shows === 0) {
			$show = true;
		} elseif ($shows < 4 && $now - $last > 6 * 3600 && mt_rand(1, 100) <= 20) {
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

		foreach (array('lighthouse', 'pagespeed', 'gtmetrix', 'pingdom', 'webpagetest') as $speed) {
			if (strpos($agent, $speed) !== false) {
				return false;
			}
		}

		return (bool)preg_match('/bot|crawl|spider|slurp|yandex|bingpreview|facebookexternalhit|telegrambot|whatsapp|twitterbot|linkedinbot|pinterest|embedly|quora|applebot|duckduck|semrush|ahrefs|mj12|petalbot|headlesschrome/', $agent);
	}
}
