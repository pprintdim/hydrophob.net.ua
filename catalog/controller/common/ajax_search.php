<?php
// Живий пошук у шапці (патерн shokeru.in.ua): один JSON-ендпоінт віддає збіги
// серед категорій, брендів і товарів, а дропдаун малює їх без перезавантаження.
class ControllerCommonAjaxSearch extends Controller {
	const LIMIT_PRODUCTS   = 6;
	const LIMIT_CATEGORIES = 5;
	const LIMIT_BRANDS     = 5;

	public function search() {
		$this->load->language('product/search');

		$json = array(
			'products'       => array(),
			'categories'     => array(),
			'brands'         => array(),
			'total_products' => 0,
			'search_url'     => ''
		);

		$q = isset($this->request->get['q']) ? trim(html_entity_decode($this->request->get['q'], ENT_QUOTES, 'UTF-8')) : '';

		$q_lower = utf8_strtolower($q);

		if (utf8_strlen($q) < 2) {
			$this->respond($json);
			return;
		}

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('tool/image');

		$filter_data = array(
			'filter_name'         => $q,
			'filter_description'  => false,
			'sort'                => 'p.sort_order',
			'order'               => 'ASC',
			'start'               => 0,
			'limit'               => self::LIMIT_PRODUCTS
		);

		$results = $this->model_catalog_product->getProducts($filter_data);

		foreach ($results as $result) {
			$image = $this->model_tool_image->resize($result['image'] ? $result['image'] : 'placeholder.png', 80, 80);

			$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

			$special = '';

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			}

			$json['products'][] = array(
				'product_id'   => (int)$result['product_id'],
				'name'         => $result['name'],
				'manufacturer' => $result['manufacturer'],
				'price'        => $price,
				'special'      => $special,
				'image'        => $image,
				'href'         => html_entity_decode($this->url->link('product/product', 'product_id=' . $result['product_id']), ENT_QUOTES, 'UTF-8')
			);
		}

		$json['total_products'] = $this->model_catalog_product->getTotalProducts($filter_data);
		$json['search_url']     = html_entity_decode($this->url->link('product/search', 'search=' . urlencode($q)), ENT_QUOTES, 'UTF-8');

		// Категорії: обходимо два рівні дерева й лишаємо ті, чия назва містить запит
		foreach ($this->matchCategories($q_lower) as $category) {
			$json['categories'][] = $category;

			if (count($json['categories']) >= self::LIMIT_CATEGORIES) {
				break;
			}
		}

		foreach ($this->model_catalog_manufacturer->getManufacturers() as $manufacturer) {
			if (utf8_strpos(utf8_strtolower($manufacturer['name']), $q_lower) === false) {
				continue;
			}

			$json['brands'][] = array(
				'name' => $manufacturer['name'],
				'href' => html_entity_decode($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']), ENT_QUOTES, 'UTF-8')
			);

			if (count($json['brands']) >= self::LIMIT_BRANDS) {
				break;
			}
		}

		$this->respond($json);
	}

	// $q приходить уже в нижньому регістрі
	private function matchCategories($q, $parent_id = 0, $path = '', $depth = 0) {
		$matched = array();

		if ($depth > 2) {
			return $matched;
		}

		foreach ($this->model_catalog_category->getCategories($parent_id) as $category) {
			$current = $path ? $path . '_' . $category['category_id'] : (string)$category['category_id'];

			if (utf8_strpos(utf8_strtolower($category['name']), $q) !== false) {
				$matched[] = array(
					'name' => $category['name'],
					'href' => html_entity_decode($this->url->link('product/category', 'path=' . $current), ENT_QUOTES, 'UTF-8')
				);
			}

			$matched = array_merge($matched, $this->matchCategories($q, $category['category_id'], $current, $depth + 1));
		}

		return $matched;
	}

	private function respond($json) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
