<?php
// «З цими товарами також купують»: рекомендації на основі вмісту кошика.
// Спершу беремо привʼязані товари (oc_product_related) для позицій у кошику,
// якщо їх немає — сусідів по категорії. Те, що вже в кошику, не показуємо.
class ControllerExtensionModuleHpCartRelated extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_cart_related');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$cart_products = $this->cart->getProducts();

		if (!$cart_products) {
			return '';
		}

		$limit = !empty($setting['limit']) ? (int)$setting['limit'] : 8;

		$in_cart = array();

		foreach ($cart_products as $cart_product) {
			$in_cart[] = (int)$cart_product['product_id'];
		}

		$candidates = array();

		// 1) привʼязані до товарів у кошику
		foreach ($in_cart as $product_id) {
			foreach ($this->model_catalog_product->getProductRelated($product_id) as $related) {
				$candidates[(int)$related['product_id']] = $related;
			}
		}

		// 2) що реально купували разом із цими товарами (по історії замовлень)
		if (count($candidates) < $limit) {
			foreach ($this->boughtTogether($in_cart, $limit) as $bought) {
				$candidates[(int)$bought['product_id']] = $bought;
			}
		}

		// 3) добираємо сусідами по категоріях кошика, щоб сітка не була напівпорожня
		if (count($candidates) < $limit) {
			$this->load->model('catalog/category');

			foreach ($in_cart as $product_id) {
				$categories = $this->model_catalog_product->getCategories($product_id);

				if (!$categories) {
					continue;
				}

				$neighbours = $this->model_catalog_product->getProducts(array(
					'filter_category_id'  => $categories[0]['category_id'],
					'filter_sub_category' => true,
					'sort'                => 'p.sort_order',
					'order'               => 'ASC',
					'start'               => 0,
					'limit'               => $limit + count($in_cart)
				));

				foreach ($neighbours as $neighbour) {
					$candidates[(int)$neighbour['product_id']] = $neighbour;
				}
			}
		}

		$data['products'] = array();

		foreach ($candidates as $product_id => $product_info) {
			if (in_array($product_id, $in_cart)) {
				continue;
			}

			$image = $this->model_tool_image->resize($product_info['image'] ? $product_info['image'] : 'placeholder.png', 450, 450);

			$special = false;

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			}

			$data['products'][] = array(
				'product_id' => $product_id,
				'name'       => $product_info['name'],
				'thumb'      => $image,
				'price'      => $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
				'special'    => $special,
				'minimum'    => $product_info['minimum'] > 0 ? (int)$product_info['minimum'] : 1,
				'href'       => $this->url->link('product/product', 'product_id=' . $product_id)
			);

			if (count($data['products']) >= $limit) {
				break;
			}
		}

		if (!$data['products']) {
			return '';
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$language_id = (int)$this->config->get('config_language_id');

		if (!empty($setting['cart_related_description'][$language_id]['heading_title'])) {
			$data['heading_title'] = $setting['cart_related_description'][$language_id]['heading_title'];
		}

		$data['text_prev'] = $this->language->get('text_prev');
		$data['text_next'] = $this->language->get('text_next');
		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_more'] = $this->language->get('button_more');
		$data['button_add_short'] = $this->language->get('button_add_short');

		if (!empty($setting['view']) && $setting['view'] == 'aside') {
			$chunk = !empty($setting['chunk']) ? (int)$setting['chunk'] : 6;
			$offset = isset($setting['offset']) ? (int)$setting['offset'] : 0;

			$data['total'] = count($data['products']);
			$data['partial'] = ($offset > 0);
			$data['next_offset'] = $offset + $chunk;
			$data['has_more'] = ($offset + $chunk) < $data['total'];
			$data['products'] = array_slice($data['products'], $offset, $chunk);
			$data['more_url'] = html_entity_decode($this->url->link('extension/module/hp_cart_related/aside'), ENT_QUOTES, 'UTF-8');

			if (!$data['products']) {
				return '';
			}

			return $this->load->view('extension/module/hp_cart_related_aside', $data);
		}

		return $this->load->view('extension/module/hp_cart_related', $data);
	}

	// Товари з реальних замовлень, де вже зустрічались позиції з кошика:
	// спершу «купували разом», далі — просто найходовіше.
	private function boughtTogether($in_cart, $limit) {
		$in_cart = array_filter(array_map('intval', $in_cart));

		if (!$in_cart) {
			return array();
		}

		$ids = implode(',', $in_cart);

		$query = $this->db->query("
			SELECT op2.product_id, SUM(op2.quantity) AS bought
			FROM " . DB_PREFIX . "order_product op1
			JOIN " . DB_PREFIX . "order_product op2 ON op2.order_id = op1.order_id AND op2.product_id NOT IN (" . $ids . ")
			JOIN " . DB_PREFIX . "order o ON o.order_id = op1.order_id AND o.order_status_id > 0
			JOIN " . DB_PREFIX . "product p ON p.product_id = op2.product_id AND p.status = '1'
			WHERE op1.product_id IN (" . $ids . ")
			GROUP BY op2.product_id
			ORDER BY bought DESC
			LIMIT " . (int)$limit
		);

		$results = array();

		foreach ($query->rows as $row) {
			$product_info = $this->model_catalog_product->getProduct($row['product_id']);

			if ($product_info) {
				$results[] = $product_info;
			}
		}

		return $results;
	}

	// повна секція зі слайдером — окремий блок на сторінці кошика
	public function section() {
		return $this->index(array('limit' => 8));
	}

	// компактний вивід для бічної панелі чекауту: перші 6 карток одразу,
	// наступні порції довантажуються ajax-ом (?offset=N віддає лише картки)
	public function aside() {
		$offset = isset($this->request->get['offset']) ? max(0, (int)$this->request->get['offset']) : 0;

		$html = $this->index(array(
			'limit'  => 18,
			'view'   => 'aside',
			'offset' => $offset,
			'chunk'  => 6
		));

		// ajax-довантаження: віддаємо готовий шматок HTML
		if (isset($this->request->get['offset'])) {
			$this->response->setOutput($html);

			return;
		}

		return $html;
	}
}
