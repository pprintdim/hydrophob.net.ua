<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		// GA4: ідентифікатор і тестовий режим налаштовуються в панелі (СЕО-мета).
		// Порожній id — тег не підключається взагалі.
		$data['ga4_id'] = trim((string)$this->config->get('config_ga4_id'));
		$data['ga4_debug'] = (bool)$this->config->get('config_ga4_debug');
		// ціни в подіях беруться з карток на сторінці — тобто в поточній валюті
		$data['ga4_currency'] = isset($this->session->data['currency'])
			? $this->session->data['currency']
			: $this->config->get('config_currency');

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		// фавікон більше не йде через addLink: повний набір (ico/png/svg)
		// прописаний у шаблоні, інакше в <head> було два різні rel="icon"


		// noindex для службових сторінок рахує модуль seo_meta (подія header/before)
		$data['meta_robots'] = $this->registry->has('seo_meta_robots')
			? (string)$this->registry->get('seo_meta_robots')
			: '';

		// Індексаційний гейт: поки сайт у розробці, `config_noindex` перекриває
		// будь-які правила seo_meta і закриває від індексації ВЕСЬ магазин.
		// Знімається одним перемикачем у Налаштування → Сервер (+ robots.txt).
		if ($this->config->get('config_noindex')) {
			$data['meta_robots'] = 'noindex, nofollow';

			if (!headers_sent()) {
				header('X-Robots-Tag: noindex, nofollow', true);
			}
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

		// hreflang: та сама сторінка іншими мовами
		$this->load->model('tool/image');
		// favicon із налаштувань магазину
		$data['icon'] = '';
		$icon_path = $this->config->get('config_icon');
		if ($icon_path && is_file(DIR_IMAGE . $icon_path)) {
			// SVG не ресайзиться — віддаємо напряму
			if (strtolower(pathinfo($icon_path, PATHINFO_EXTENSION)) == 'svg') {
				$data['icon'] = $this->config->get('config_url') . 'image/' . $icon_path;
			} else {
				$data['icon'] = $this->model_tool_image->resize($icon_path, 64, 64);
			}
		}

		// мовний перемикач у мобільному бургер-меню (десктопний живе в common/language)
		$data['mob_languages'] = array();
		$data['mob_lang_code'] = (string)$this->config->get('config_language');

		$this->load->model('localisation/language');

		// адреса тієї самої сторінки іншою мовою — мова тепер сегмент URL
		if (isset($this->request->get['route'])) {
			$mob_route = (string)$this->request->get['route'];
			$mob_args = $this->request->get;
			unset($mob_args['_route_'], $mob_args['route'], $mob_args['language']);
		} else {
			$mob_route = 'common/home';
			$mob_args = array();
		}

		foreach ($this->model_localisation_language->getLanguages() as $mob_language) {
			if ($mob_language['status']) {
				$mob_link_args = $mob_args;

				if ($mob_language['code'] !== $data['mob_lang_code']) {
					$mob_link_args['language'] = $mob_language['code'];
				}

				$data['mob_languages'][] = array(
					'code' => $mob_language['code'],
					'href' => $this->url->link($mob_route, http_build_query($mob_link_args), true)
				);
			}
		}

		$data['hreflangs'] = array();
		$this->load->model('localisation/language');
		$current_route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';
		$allowed_args = array('product_id', 'path', 'information_id', 'page', 'search', 'manufacturer_id', 'filter');
		$query_args = array();
		foreach ($allowed_args as $arg_key) {
			if (isset($this->request->get[$arg_key])) {
				$query_args[$arg_key] = $this->request->get[$arg_key];
			}
		}
		// саме мова магазину, а не поточна: на /ru/ config_language вже ru,
		// через що x-default показував на російську версію
		$default_language = $this->registry->has('config_language_default')
			? (string)$this->registry->get('config_language_default')
			: (string)$this->config->get('config_language');

		foreach ($this->model_localisation_language->getLanguages() as $hl_language) {
			if (!$hl_language['status']) {
				continue;
			}

			// мову задаємо явно навіть для дефолтної: без цього посилання
			// будувалось поточною мовою і на /ru/ усі три hreflang вказували
			// на російську версію. Префікс у шлях додає rewrite, параметр
			// ?language= у підсумковий URL не потрапляє
			$hl_args = $query_args;
			$hl_args['language'] = $hl_language['code'];
			$href = $this->url->link($current_route, http_build_query($hl_args), true);

			$data['hreflangs'][] = array(
				// регіональний код: сайт орієнтований на Україну
				'code' => substr($hl_language['code'], 0, 2) . '-UA',
				'href' => $href
			);

			if ($hl_language['code'] == $default_language) {
				$data['hreflangs'][] = array('code' => 'x-default', 'href' => $href);
			}
		}

		$data['reviews_href'] = $this->url->link('information/reviews');

		// ── OG / Twitter та canonical-фолбек ──────────────────────────
		// canonical, який виставив контролер, головніший; решті сторінок
		// ставимо поточну адресу без tracking-хвостів
		$canonical = '';

		foreach ($this->document->getLinks() as $link) {
			if ($link['rel'] == 'canonical') {
				$canonical = $link['href'];

				break;
			}
		}

		if (!$canonical) {
			$path = isset($this->request->server['REQUEST_URI']) ? $this->request->server['REQUEST_URI'] : '/';
			$canonical = rtrim($server, '/') . strtok($path, '?');

			$data['canonical'] = $canonical;
		} else {
			$data['canonical'] = '';
		}

		$data['og_site_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
		$data['og_locale'] = substr($this->language->get('code'), 0, 2) . '_UA';
		$data['og_title'] = html_entity_decode($this->document->getTitle(), ENT_QUOTES, 'UTF-8');
		$data['og_url'] = $canonical;
		$data['og_type'] = (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product') ? 'product' : 'website';

		$data['og_image'] = '';

		// картинку товару кладе його контролер; далі — банер 1200x630, і лише
		// в останню чергу лого. SVG соцмережі не показують, тому як og:image
		// він не годиться взагалі.
		if (!empty($this->session->data['og_image'])) {
			$data['og_image'] = $this->session->data['og_image'];

			unset($this->session->data['og_image']);
		} elseif (is_file(DIR_IMAGE . 'hydrophob/og-image.jpg')) {
			$data['og_image'] = $server . 'image/hydrophob/og-image.jpg';
		} elseif ($this->config->get('config_logo')
			&& is_file(DIR_IMAGE . $this->config->get('config_logo'))
			&& strtolower(pathinfo($this->config->get('config_logo'), PATHINFO_EXTENSION)) != 'svg') {
			$data['og_image'] = $server . 'image/' . $this->config->get('config_logo');
		}

		// розміри прев'ю: без них соцмережі часом показують дрібну картинку,
		// бо не знають пропорцій до завантаження
		$data['og_image_width'] = '';
		$data['og_image_height'] = '';

		if ($data['og_image']) {
			$og_path = str_replace($server, '', $data['og_image']);
			$og_file = DIR_IMAGE . preg_replace('~^image/~', '', $og_path);

			if (is_file($og_file)) {
				$size = @getimagesize($og_file);

				if ($size) {
					$data['og_image_width'] = (string)$size[0];
					$data['og_image_height'] = (string)$size[1];
				}
			}
		}

		return $this->load->view('common/header', $data);
	}
}
