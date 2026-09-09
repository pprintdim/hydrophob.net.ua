<?php
// Sitemap за зразком hydrophob.net: /sitemap.xml — індекс, кожна секція живе
// на /sitemap/<name>.xml. Кожен url має мовні альтернативи (uk/ru) — мови тут
// українська живе на чистому шляху, російська — під /ru/, тож альтернатива
// для неї = той самий шлях із мовним префіксом.
class ControllerExtensionFeedGoogleSitemap extends Controller {
	private $languages = array();
	private $default = '';

	public function index() {
		if (!$this->config->get('feed_google_sitemap_status')) {
			return;
		}

		// випадковий notice перед <?xml ламає документ
		ob_start();

		$this->prepareLanguages();

		$uri = (string)parse_url($this->request->server['REQUEST_URI'], PHP_URL_PATH);
		$type = '';

		// /sitemap/products.xml -> products; index.php?sitemap_section= теж працює
		if (preg_match('~/sitemap/([a-z]+)(?:\.xml)?$~', $uri, $match)) {
			$type = $match[1];
		} elseif (isset($this->request->get['sitemap_section'])) {
			$type = preg_replace('/[^a-z]/', '', (string)$this->request->get['sitemap_section']);
		}

		$this->response->addHeader('Content-Type: application/xml; charset=UTF-8');

		if ($type === 'style') {
			$this->response->addHeader('Content-Type: text/xsl; charset=UTF-8');

			ob_end_clean();

			$this->response->setOutput(file_get_contents(DIR_APPLICATION . '../sitemap.xsl'));

			return;
		}

		switch ($type) {
			case 'products':
				$output = $this->sectionProducts();
				break;
			case 'categories':
				$output = $this->sectionCategories();
				break;
			case 'pages':
				$output = $this->sectionPages();
				break;
			case 'gallery':
				$output = $this->sectionGallery();
				break;
			default:
				$output = $this->sitemapIndex();
				break;
		}

		ob_end_clean();

		$this->response->setOutput($output);
	}

	private function prepareLanguages() {
		$this->load->model('localisation/language');

		$this->default = (string)$this->config->get('config_language');

		foreach ($this->model_localisation_language->getLanguages() as $language) {
			if ($language['status']) {
				$this->languages[] = array(
					'code'     => $language['code'],
					'hreflang' => substr($language['code'], 0, 2) . '-UA'
				);
			}
		}
	}

	private function styleUrl() {
		return $this->base() . '/sitemap/style.xml';
	}

	private function base() {
		return rtrim($this->config->get('config_ssl') ?: $this->config->get('config_url'), '/');
	}

	private function esc($url) {
		return htmlspecialchars(html_entity_decode($url, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
	}

	// Один <url> з альтернативами під кожну активну мову.
	private function entry($link, $changefreq = 'weekly', $priority = '0.7', $lastmod = '', $images = array()) {
		$base = $this->base();
		$path = str_replace($base, '', html_entity_decode($link, ENT_QUOTES, 'UTF-8'));

		if ($path === '') {
			$path = '/';
		}

		$output = "\n\t<url>";
		$output .= "\n\t\t" . '<loc>' . $this->esc($base . $path) . '</loc>';

		foreach ($this->languages as $language) {
			if ($language['code'] == $this->default) {
				$href = $base . $path;
			} else {
				$prefix = '/' . substr($language['code'], 0, 2);
				$href = $base . $prefix . ($path === '/' ? '/' : $path);
			}

			$output .= "\n\t\t" . '<xhtml:link rel="alternate" hreflang="' . $language['hreflang'] . '" href="' . $this->esc($href) . '"/>';
		}

		if ($lastmod) {
			$output .= "\n\t\t" . '<lastmod>' . date('Y-m-d\TH:i:sP', strtotime($lastmod)) . '</lastmod>';
		}

		$output .= "\n\t\t" . '<changefreq>' . $changefreq . '</changefreq>';
		$output .= "\n\t\t" . '<priority>' . $priority . '</priority>';

		foreach ($images as $image) {
			$output .= "\n\t\t" . '<image:image>' . "\n\t\t\t" . '<image:loc>' . $this->esc($image['loc']) . '</image:loc>';

			if (!empty($image['caption'])) {
				$output .= "\n\t\t\t" . '<image:caption>' . $this->esc($image['caption']) . '</image:caption>';
			}

			$output .= "\n\t\t" . '</image:image>';
		}

		return $output . "\n\t</url>";
	}

	private function open() {
		return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<?xml-stylesheet type="text/xsl" href="' . $this->styleUrl() . '"?>' . "\n"
			. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
			. ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'
			. ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
			. ' xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
	}

	// Медіа-карта сайту: відео й ілюстрації сторінок (не товарів і не категорій —
	// ті вже є у власних секціях). Кожен файл прив'язаний до сторінки, де показаний.
	private function sectionGallery() {
		$this->load->model('tool/video');

		$base = $this->base();
		$pages = array();   // url сторінки => array('images' => [loc => caption], 'videos' => [file => poster])

		$attach = function ($route, $images = array(), $videos = array()) use (&$pages) {
			$link = $this->url->link($route);

			if (!isset($pages[$link])) {
				$pages[$link] = array('images' => array(), 'videos' => array());
			}

			foreach ($images as $loc => $caption) {
				$pages[$link]['images'][$loc] = $caption;
			}

			foreach ($videos as $file => $poster) {
				$pages[$link]['videos'][$file] = $poster;
			}
		};

		// Медіа живуть у модулях теми; сторінку дає лейаут, до якого модуль підключений
		$module_query = $this->db->query("SELECT module_id, `code`, setting FROM " . DB_PREFIX . "module WHERE `code` IN ('hp_hero', 'hp_gallery', 'hp_about', 'hp_categories')");

		foreach ($module_query->rows as $module) {
			$setting = json_decode($module['setting'], true);

			if (!$setting || empty($setting['status'])) {
				continue;
			}

			$route = $this->moduleRoute($module['code'] . '.' . $module['module_id']);

			if (!$route) {
				continue;
			}

			$images = array();
			$videos = array();

			foreach (array_merge(
				isset($setting['slides']) && is_array($setting['slides']) ? $setting['slides'] : array(),
				isset($setting['items']) && is_array($setting['items']) ? $setting['items'] : array()
			) as $slide) {
				$file = isset($slide['video']) ? trim((string)$slide['video']) : '';
				$poster = isset($slide['poster']) ? trim((string)$slide['poster']) : (isset($slide['image']) ? trim((string)$slide['image']) : '');

				if ($file && substr($file, -4) === '.mp4' && $this->model_tool_video->exists($file)) {
					$videos[$file] = $poster;
				} elseif ($poster && is_file(DIR_IMAGE . $poster)) {
					// слайд без відео — просто ілюстрація сторінки
					$images[$base . '/image/' . $this->encodePath($poster)] = $this->config->get('config_name');
				}
			}

			if ($images || $videos) {
				$attach($route, $images, $videos);
			}
		}

		// Ілюстрації сторінок поза модулями: банер «Про нас» і відео контактів
		if (is_file(DIR_IMAGE . 'hydrophob/about-bg.webp')) {
			$attach('information/contact', array($base . '/image/hydrophob/about-bg.webp' => $this->config->get('config_name')));
		}

		if ($this->model_tool_video->exists('talk.mp4')) {
			$attach('information/contact', array(), array('talk.mp4' => 'catalog/video-posters/hero-talk-02168e.webp'));
		}

		$output = $this->open();

		foreach ($pages as $link => $media) {
			if (!$media['images'] && !$media['videos']) {
				continue;
			}

			$path = str_replace($base, '', html_entity_decode($link, ENT_QUOTES, 'UTF-8')) ?: '/';

			$output .= "\n\t<url>";
			$output .= "\n\t\t" . '<loc>' . $this->esc($base . $path) . '</loc>';

			foreach ($media['images'] as $loc => $caption) {
				$output .= "\n\t\t" . '<image:image>';
				$output .= "\n\t\t\t" . '<image:loc>' . $this->esc($loc) . '</image:loc>';

				if ($caption) {
					$output .= "\n\t\t\t" . '<image:caption>' . $this->esc(html_entity_decode($caption, ENT_QUOTES, 'UTF-8')) . '</image:caption>';
				}

				$output .= "\n\t\t" . '</image:image>';
			}

			foreach ($media['videos'] as $file => $poster) {
				$thumb = $this->model_tool_video->poster($file, $poster);

				// без прев'ю відео-запис у sitemap недійсний
				if (!$thumb) {
					continue;
				}

				$output .= "\n\t\t" . '<video:video>';
				$output .= "\n\t\t\t" . '<video:thumbnail_loc>' . $this->esc($thumb) . '</video:thumbnail_loc>';
				$output .= "\n\t\t\t" . '<video:title>' . $this->esc($this->model_tool_video->title($file)) . '</video:title>';
				$output .= "\n\t\t\t" . '<video:description>' . $this->esc($this->model_tool_video->description($file)) . '</video:description>';
				$output .= "\n\t\t\t" . '<video:content_loc>' . $this->esc($this->model_tool_video->url($file)) . '</video:content_loc>';

				$seconds = $this->model_tool_video->seconds($file);

				if ($seconds) {
					$output .= "\n\t\t\t" . '<video:duration>' . $seconds . '</video:duration>';
				}

				$output .= "\n\t\t\t" . '<video:family_friendly>yes</video:family_friendly>';
				$output .= "\n\t\t\t" . '<video:live>no</video:live>';
				$output .= "\n\t\t" . '</video:video>';
			}

			$output .= "\n\t</url>";
		}

		return $output . "\n" . '</urlset>';
	}

	// Сторінка, на якій показаний модуль: беремо роут його лейаута.
	private function moduleRoute($code) {
		$query = $this->db->query("
			SELECT lr.route
			FROM " . DB_PREFIX . "layout_module lm
			JOIN " . DB_PREFIX . "layout_route lr ON lr.layout_id = lm.layout_id
			WHERE lm.code = '" . $this->db->escape($code) . "'
			ORDER BY lr.route ASC
		");

		if (!$query->num_rows) {
			return '';
		}

		// головна важливіша за решту; роути з масками (%) адреси не мають
		foreach (array('common/home', 'product/category', 'information/contact') as $preferred) {
			foreach ($query->rows as $row) {
				if ($row['route'] === $preferred) {
					return $preferred;
				}
			}
		}

		foreach ($query->rows as $row) {
			if ($row['route'] !== '' && strpos($row['route'], '%') === false && strpos($row['route'], 'product/product') !== 0) {
				return $row['route'];
			}
		}

		return '';
	}

	private function sitemapIndex() {
		$base = $this->base();

		$output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<?xml-stylesheet type="text/xsl" href="' . $this->styleUrl() . '"?>' . "\n"
			. '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		foreach (array('pages', 'categories', 'products', 'gallery') as $section) {
			$loc = $base . '/sitemap/' . $section . '.xml';

			$output .= "\n\t<sitemap>";
			$output .= "\n\t\t" . '<loc>' . $this->esc($loc) . '</loc>';
			$output .= "\n\t\t" . '<lastmod>' . date('Y-m-d') . '</lastmod>';
			$output .= "\n\t</sitemap>";
		}

		return $output . "\n" . '</sitemapindex>';
	}

	private function sectionProducts() {
		$this->load->model('catalog/product');

		$base = $this->base();
		$output = $this->open();

		foreach ($this->model_catalog_product->getProducts(array('limit' => 1000)) as $product) {
			$images = array();

			// оригінали, не ресайзнуті копії: кеш зображень може зникнути будь-коли
			if ($product['image'] && is_file(DIR_IMAGE . $product['image'])) {
				$images[] = array(
					'loc'     => $base . '/image/' . $this->encodePath($product['image']),
					'caption' => $product['name']
				);
			}

			// фото галереї того ж товару належать тому ж url
			foreach ($this->model_catalog_product->getProductImages($product['product_id']) as $extra) {
				if (empty($extra['image']) || !is_file(DIR_IMAGE . $extra['image'])) {
					continue;
				}

				$images[] = array(
					'loc'     => $base . '/image/' . $this->encodePath($extra['image']),
					'caption' => $product['name']
				);
			}

			$output .= $this->entry(
				$this->url->link('product/product', 'product_id=' . $product['product_id']),
				'weekly',
				'1.0',
				$product['date_modified'],
				$images
			);
		}

		return $output . "\n" . '</urlset>';
	}

	private function sectionCategories() {
		$this->load->model('catalog/category');

		$output = $this->open() . $this->categoryTree(0);

		return $output . "\n" . '</urlset>';
	}

	private function categoryTree($parent_id) {
		$output = '';

		foreach ($this->model_catalog_category->getCategories($parent_id) as $category) {
			$images = array();

			if (!empty($category['image']) && is_file(DIR_IMAGE . $category['image'])) {
				$images[] = array(
					'loc'     => $this->base() . '/image/' . $this->encodePath($category['image']),
					'caption' => $category['name']
				);
			}

			$output .= $this->entry($this->url->link('product/category', 'path=' . $category['category_id']), 'weekly', '0.8', '', $images);
			$output .= $this->categoryTree($category['category_id']);
		}

		return $output;
	}

	// Лишає слеші, але екранує те, що url не понесе сирим.
	private function encodePath($path) {
		return implode('/', array_map('rawurlencode', explode('/', $path)));
	}

	private function sectionPages() {
		$this->load->model('catalog/information');

		$output = $this->open();

		// головна та роутові сторінки з власним контентом
		$output .= $this->entry($this->url->link('common/home'), 'daily', '1.0');

		foreach (array('information/contact', 'information/faq', 'information/reviews', 'product/special') as $route) {
			$output .= $this->entry($this->url->link($route), 'weekly', '0.8');
		}

		foreach ($this->model_catalog_information->getInformations() as $information) {
			$output .= $this->entry($this->url->link('information/information', 'information_id=' . $information['information_id']), 'yearly', '0.3');
		}

		return $output . "\n" . '</urlset>';
	}
}
