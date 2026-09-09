<?php
class ControllerStartupSeoUrl extends Controller {
	// мовні префікси адреси: uk живе на чистих URL, ru — під /ru/
	private static $language_prefixes = array('ru' => 'ru-ru');

	private static $languages_cache = null;

	// короткий ключ у URL => стокова пара sort/order OpenCart
	private static $sorts = array(
		'name_asc'   => array('pd.name', 'ASC'),
		'name_desc'  => array('pd.name', 'DESC'),
		'price_asc'  => array('p.price', 'ASC'),
		'price_desc' => array('p.price', 'DESC'),
		'rating'     => array('rating', 'DESC'),
		'model_asc'  => array('p.model', 'ASC'),
		'model_desc' => array('p.model', 'DESC')
	);

	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Старі адреси виду ?language=ru-ru віддаємо 301-м на /ru/... —
		// інакше кожна сторінка мала б два робочі URL
		$this->redirectLegacyLanguage();

		// Мову диктує адреса: /ru/... — російська, решта — українська.
		// Раніше вона трималась у сесії, тож той самий URL віддавав різний
		// контент різним відвідувачам, а Google бачив лише один варіант.
		$this->applyUrlLanguage();

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			// /sitemap/<section>.xml віддає фід; в nginx правила під це немає,
			// тож парсер розбирає адресу тут і тримає url чистими
			if (isset($parts[0]) && $parts[0] === 'sitemap' && isset($parts[1])) {
				$section = preg_replace('/[^a-z]/', '', str_replace('.xml', '', $parts[1]));

				if ($section) {
					$this->request->get['route'] = 'extension/feed/google_sitemap';
					$this->request->get['sitemap_section'] = $section;

					unset($this->request->get['_route_']);

					return;
				}
			}

			$filters = array();
			$is_search = false;

			foreach ($parts as $part) {
				// Сегменти каталогу: /katalog/<фільтр>/sort-<ключ>/page-N
				if (preg_match('/^page-([0-9]+)$/', $part, $match)) {
					$this->request->get['page'] = $match[1];

					continue;
				}

				if (preg_match('/^sort-([a-z_]+)$/', $part, $match) && isset(self::$sorts[$match[1]])) {
					$this->request->get['sort'] = self::$sorts[$match[1]][0];
					$this->request->get['order'] = self::$sorts[$match[1]][1];

					continue;
				}

				// /poshuk/<запит>/... — сам запит іде сегментом, а не ?search=
				if ($is_search && !isset($this->request->get['search'])) {
					$filter_slug = $this->db->query("SELECT `query` FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND `query` LIKE 'filter_id=%' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

					if (!$filter_slug->num_rows) {
						$this->request->get['search'] = urldecode($part);

						continue;
					}
				}

				$filter_query = $this->db->query("SELECT `query` FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND `query` LIKE 'filter_id=%' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($filter_query->num_rows) {
					$filters[] = (int)substr($filter_query->row['query'], strlen('filter_id='));

					continue;
				}

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($url[0] == 'route') {
						// SEO-URL для звичайних роутів (kontakty -> information/contact)
						$this->request->get['route'] = substr($query->row['query'], strlen('route='));

						if ($this->request->get['route'] == 'product/search') {
							$is_search = true;
						}
					} elseif ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					$this->request->get['route'] = 'error/not_found';

					break;
				}
			}

			if ($filters) {
				$this->request->get['filter'] = implode(',', $filters);
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				}
			}
		}
	}

	/**
	 * ?language=<код> у вхідному запиті — спадок старої схеми. Ведемо 301
	 * на адресу з мовним префіксом, зберігаючи решту параметрів.
	 */
	private function redirectLegacyLanguage() {
		if (empty($this->request->get['language']) || $this->request->server['REQUEST_METHOD'] !== 'GET') {
			return;
		}

		// прямі виклики index.php?route=... (ajax, фіди) мовного префікса не
		// мають — там ?language= лишається робочим способом задати мову
		if (isset($this->request->get['route'])) {
			return;
		}

		$code = (string)$this->request->get['language'];
		$languages = $this->getActiveLanguages();

		if (!isset($languages[$code])) {
			return;
		}

		$prefix = array_search($code, self::$language_prefixes, true);
		$path = '/';

		if (!empty($this->request->get['_route_'])) {
			$path .= trim((string)$this->request->get['_route_'], '/');
		}

		if ($prefix !== false) {
			$path = '/' . $prefix . rtrim($path, '/');

			if ($path === '/' . $prefix) {
				$path .= '/';
			}
		}

		$params = $this->request->get;
		unset($params['language'], $params['_route_'], $params['route']);

		$query = $params ? '?' . http_build_query($params) : '';

		$this->response->redirect($this->config->get('config_url') . ltrim($path, '/') . $query, 301);
	}

	/**
	 * Мова з адреси. Префікс однієї мови (ru) — решта живе на чистих URL.
	 * Виконується після startup/startup, тож перекриває його вибір.
	 */
	private function applyUrlLanguage() {
		$default = (string)$this->config->get('config_language');

		// далі config_language підміняється мовою з адреси, тож дефолтну мову
		// магазину запам'ятовуємо — вона потрібна для hreflang x-default
		$this->registry->set('config_language_default', $default);
		$prefix_language = '';

		if (isset($this->request->get['_route_'])) {
			$route = ltrim((string)$this->request->get['_route_'], '/');

			foreach (self::$language_prefixes as $prefix => $code) {
				if ($route === $prefix || strpos($route, $prefix . '/') === 0) {
					// /ru і /ru/ — та сама сторінка, лишаємо одну адресу
					if ($route === $prefix && $this->request->server['REQUEST_METHOD'] === 'GET') {
						$this->response->redirect($this->config->get('config_url') . $prefix . '/', 301);
					}

					$prefix_language = $code;
					// далі по коду адреса розбирається вже без мовного сегмента
					$this->request->get['_route_'] = (string)substr($route, strlen($prefix) + 1);

					break;
				}
			}

			// сторінка без префікса — завжди мова за замовчуванням, інакше
			// кука від попереднього візиту віддавала б ru-контент на uk-адресі
			if ($prefix_language === '') {
				$prefix_language = $default;
			}
		}

		// прямі виклики index.php?route=... (ajax) префікса не мають — беремо
		// мову сесії. Без цього config_language лишався дефолтним, і посилання
		// в ajax-відповідях (кошик, пошук) поверталися без /ru/
		if ($prefix_language === '' && !empty($this->session->data['language'])) {
			$prefix_language = (string)$this->session->data['language'];
		}

		if ($prefix_language === '') {
			return;
		}

		$languages = $this->getActiveLanguages();

		if (!isset($languages[$prefix_language])) {
			return;
		}

		$this->session->data['language'] = $prefix_language;

		if ((string)$this->config->get('config_language') !== $prefix_language
			|| (int)$this->config->get('config_language_id') !== (int)$languages[$prefix_language]['language_id']) {
			$language = new Language($prefix_language);
			$language->load($prefix_language);

			$this->registry->set('language', $language);
			$this->config->set('config_language', $prefix_language);
			$this->config->set('config_language_id', $languages[$prefix_language]['language_id']);
		}
	}

	private function getActiveLanguages() {
		if (self::$languages_cache === null) {
			self::$languages_cache = array();

			$query = $this->db->query("SELECT code, language_id FROM " . DB_PREFIX . "language WHERE status = '1'");

			foreach ($query->rows as $row) {
				self::$languages_cache[$row['code']] = $row;
			}
		}

		return self::$languages_cache;
	}

	private function sortKey($sort, $order) {
		$order = strtoupper($order) == 'DESC' ? 'DESC' : 'ASC';

		foreach (self::$sorts as $key => $pair) {
			if ($pair[0] == $sort && $pair[1] == $order) {
				return $key;
			}
		}

		return '';
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}
				} elseif ($key == 'path') {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$category . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';

							break;
						}
					}

					unset($data[$key]);
				}
			}
		}

		// головна — корінь сайту (з рештою параметрів, якщо вони є: ?language=…)
		$is_home = !$url && isset($data['route']) && $data['route'] == 'common/home';

		// Каталог: фільтри, сортування й сторінка стають сегментами шляху —
		// /katalog/<фільтр>/sort-price_asc/page-2 замість хвоста ?filter[]=…&sort=…
		if ($url && isset($data['route']) && $data['route'] == 'product/category') {
			$ok = true;

			if (!empty($data['filter'])) {
				$filters = is_array($data['filter']) ? $data['filter'] : explode(',', (string)$data['filter']);

				foreach ($filters as $filter_id) {
					if (!(int)$filter_id) {
						continue;
					}

					$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = 'filter_id=" . (int)$filter_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
					} else {
						$ok = false;

						break;
					}
				}
			}

			if ($ok) {
				unset($data['filter']);

				if (!empty($data['sort'])) {
					// дефолтне сортування в URL не показуємо
					if ($data['sort'] == 'p.sort_order') {
						unset($data['sort'], $data['order']);
					} else {
						$key = $this->sortKey($data['sort'], isset($data['order']) ? $data['order'] : 'ASC');

						if ($key) {
							$url .= '/sort-' . $key;
							unset($data['sort'], $data['order']);
						}
					}
				}

				if (!empty($data['page'])) {
					if ($data['page'] == '{page}') {
						$url .= '/page-{page}';       // шаблон пагінації, підставляється пізніше
						unset($data['page']);
					} elseif ((int)$data['page'] > 1) {
						$url .= '/page-' . (int)$data['page'];
						unset($data['page']);
					} else {
						unset($data['page']);
					}
				}
			}
		}

		// сторінки без id (контакти, кабінет, кошик, пошук...) мають слаг у вигляді route=...
		if (!$url && isset($data['route'])) {
			$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape('route=' . $data['route']) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

			if ($query->num_rows && $query->row['keyword']) {
				$url .= '/' . $query->row['keyword'];
			}
		}

		// пошук: запит, фільтри, sort і page — сегментами (/poshuk/кера/sort-price_asc/page-2)
		if ($url && isset($data['route']) && $data['route'] == 'product/search') {
			if (!empty($data['search'])) {
				$url .= '/' . rawurlencode(trim((string)$data['search']));
				unset($data['search']);
			}

			if (!empty($data['filter'])) {
				$filters = is_array($data['filter']) ? $data['filter'] : explode(',', (string)$data['filter']);
				$ok = true;

				foreach ($filters as $filter_id) {
					if (!(int)$filter_id) {
						continue;
					}

					$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = 'filter_id=" . (int)$filter_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
					} else {
						$ok = false;

						break;
					}
				}

				if ($ok) {
					unset($data['filter']);
				}
			}

			if (!empty($data['sort'])) {
				if ($data['sort'] == 'p.sort_order') {
					unset($data['sort'], $data['order']);
				} else {
					$key = $this->sortKey($data['sort'], isset($data['order']) ? $data['order'] : 'ASC');

					if ($key) {
						$url .= '/sort-' . $key;
						unset($data['sort'], $data['order']);
					}
				}
			}

			if (!empty($data['page'])) {
				if ($data['page'] == '{page}') {
					$url .= '/page-{page}';
					unset($data['page']);
				} elseif ((int)$data['page'] > 1) {
					$url .= '/page-' . (int)$data['page'];
					unset($data['page']);
				} else {
					unset($data['page']);
				}
			}
		}

		// мова посилання: явна (hreflang, перемикач) або поточна. У самому
		// URL вона стає префіксом, а не параметром ?language=, який Google
		// рахував окремою сторінкою-дублем
		$link_language = isset($data['language']) ? (string)$data['language'] : (string)$this->config->get('config_language');
		$language_prefix = array_search($link_language, self::$language_prefixes, true);
		$language_prefix = $language_prefix === false ? '' : '/' . $language_prefix;

		unset($data['language']);

		if ($url || $is_home) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			$base = str_replace('/index.php', '', $url_info['path']);

			if ($is_home && $base === '') {
				// головна іншою мовою — /ru/, без префікса — просто /
				$base = $language_prefix === '' ? '/' : $language_prefix . '/';
				$language_prefix = '';
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . $base . $language_prefix . $url . $query;
		} else {
			// посилання без ЧПУ (index.php?route=...): мову повертаємо параметром
			if ($language_prefix !== '') {
				return $link . (strpos($link, '?') === false ? '?' : '&') . 'language=' . rawurlencode($link_language);
			}

			return $link;
		}
	}
}
