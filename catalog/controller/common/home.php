<?php
class ControllerCommonHome extends Controller {
	public function index() {
		// Перший візит (кука ще не стоїть) — віддаємо вітальну сторінку прямо на «/»,
		// щоб прогріти кеш hero-відео; URL не змінюється, редіректу немає.
		// Пошуковим ботам кука не ставиться ніколи, тож для них головною
		// назавжди лишалась би вітальна заглушка — їм одразу віддаємо
		// справжню головну. Вимірювачі швидкості (PageSpeed/Lighthouse)
		// лишаються на вітальній: саме її бачить перший відвідувач.
		if (!isset($this->request->cookie['hydro_visited']) && !$this->isSearchBot()) {
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

		$bots = array(
			'googlebot', 'adsbot-google', 'mediapartners-google', 'google-inspectiontool',
			'bingbot', 'bingpreview', 'yandexbot', 'applebot', 'duckduckbot', 'baiduspider',
			'slurp', 'facebookexternalhit', 'facebookcatalog', 'twitterbot', 'telegrambot',
			'whatsapp', 'viberbot', 'linkedinbot', 'ahrefsbot', 'semrushbot', 'petalbot'
		);

		foreach ($bots as $bot) {
			if (strpos($agent, $bot) !== false) {
				return true;
			}
		}

		return false;
	}
}
