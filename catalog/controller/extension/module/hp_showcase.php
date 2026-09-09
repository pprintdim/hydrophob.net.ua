<?php
// Слайдер товарів hm-sec: інстансний модуль (хіти / новинки / акції / вручну).
class ControllerExtensionModuleHpShowcase extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_showcase');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['text_badge_sale'] = $this->language->get('text_badge_sale');
		$data['text_badge_new'] = $this->language->get('text_badge_new');
		$data['text_badge_top'] = $this->language->get('text_badge_top');
		$data['text_wishlist_add'] = $this->language->get('text_wishlist_add');
		$data['text_rating'] = $this->language->get('text_rating');
		$data['text_prev'] = $this->language->get('text_prev');
		$data['text_next'] = $this->language->get('text_next');
		$data['button_cart'] = $this->language->get('button_cart');

		$language_id = (int)$this->config->get('config_language_id');

		$data['heading_title'] = $this->language->get('heading_title');

		if (!empty($setting['showcase_description'][$language_id]['heading_title'])) {
			$data['heading_title'] = $setting['showcase_description'][$language_id]['heading_title'];
		}

		$data['anchor'] = !empty($setting['anchor']) ? preg_replace('/[^a-z0-9_-]/', '', strtolower($setting['anchor'])) : '';

		$type = !empty($setting['type']) ? $setting['type'] : 'bestseller';
		$limit = !empty($setting['limit']) ? (int)$setting['limit'] : 8;

		$width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width') ?: 450;
		$height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height') ?: 450;

		$results = array();

		if ($type == 'manual') {
			if (!empty($setting['product']) && is_array($setting['product'])) {
				foreach (array_slice($setting['product'], 0, $limit) as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						$results[] = $product_info;
					}
				}
			}
		} elseif ($type == 'special') {
			$results = $this->model_catalog_product->getProductSpecials(array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => $limit
			));
		} elseif ($type == 'latest') {
			$results = $this->model_catalog_product->getProducts(array(
				'sort'  => 'p.date_added',
				'order' => 'DESC',
				'start' => 0,
				'limit' => $limit
			));
		} else {
			$results = $this->model_catalog_product->getBestSellerProducts($limit);
		}

		// тип вибірки дає бейдж картці: хіти -> "Хіт", новинки -> "Новинка"
		$type_badge = '';

		if ($type == 'bestseller') {
			$type_badge = 'top';
		} elseif ($type == 'latest') {
			$type_badge = 'new';
		}

		$data['products'] = array();

		foreach ($results as $product_info) {
			if ($product_info['image']) {
				$image = $this->model_tool_image->resize($product_info['image'], $width, $height);
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			}

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$special = false;
			}

			if ($this->config->get('config_review_status')) {
				$rating = (int)$product_info['rating'];
			} else {
				$rating = false;
			}

			$badges = array();

			if ($type_badge) {
				$badges[] = $type_badge;
			}

			$data['products'][] = array(
				'product_id' => $product_info['product_id'],
				'thumb'      => $image,
				'name'       => $product_info['name'],
				'price'      => $price,
				'special'    => $special,
				'rating'     => $rating,
				'badges'     => $badges,
				'minimum'    => isset($product_info['minimum']) && $product_info['minimum'] ? $product_info['minimum'] : 1,
				'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
			);
		}

		if (!$data['products']) {
			return '';
		}

		// список товарів у вигляді, зрозумілому пошуковикам
		$items = array();

		foreach ($data['products'] as $index => $product) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'url'      => $product['href'],
				'name'     => $product['name']
			);
		}

		$data['schema'] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => $data['heading_title'],
			'numberOfItems'   => count($items),
			'itemListElement' => $items
		);

		// GA4 view_item_list: той самий список, але для аналітики
		$data['ga_list'] = array(
			'item_list_id'   => 'showcase_' . $type,
			'item_list_name' => $data['heading_title'],
			'items'          => array_map(function ($index, $product) use ($data) {
				return array(
					'item_id'        => (int)$product['product_id'],
					'item_name'      => $product['name'],
					'item_brand'     => 'Hydrophob',
					'item_list_name' => $data['heading_title'],
					'index'          => $index + 1
				);
			}, array_keys($data['products']), $data['products'])
		);

		return $this->load->view('extension/module/hp_showcase', $data);
	}
}
