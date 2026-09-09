<?php
// Одностраничне оформлення: вигляд з нашої верстки (checkout__block), логіка —
// як на hydrophob.net: одна форма, дані летять ланцюжком стокових ajax-кроків
// (guest/address → shipping_method → payment_method → confirm).
class ControllerCheckoutCheckout extends Controller {
	public function index() {
		// порожній кошик — на сторінку кошика, там уже є оформлений стан
		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		// мінімальна кількість по кожній позиції
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->load->language('checkout/checkout');
		$this->load->language('extension/shipping/delivery');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['logged'] = $this->customer->isLogged();
		$data['cart_link'] = $this->url->link('checkout/cart');
		$data['catalog_link'] = $this->url->link('product/category', 'path=33');
		$data['login_link'] = $this->url->link('account/login', '', true);

		$data['customer_firstname'] = $this->customer->getFirstName() ?: '';
		$data['customer_lastname'] = $this->customer->getLastName() ?: '';
		$data['customer_email'] = $this->customer->getEmail() ?: '';
		$data['customer_telephone'] = $this->customer->getTelephone() ?: '';

		// перевізники (як на .net) — код збігається з методом модуля delivery;
		// вимкнені в адмінці картки сюди не потрапляють
		$all_carriers = array(
			array('code' => 'delivery.novaposhta', 'key' => 'novaposhta', 'title' => $this->language->get('text_novaposhta'), 'warehouse' => true),
			array('code' => 'delivery.meest',      'key' => 'meest',      'title' => $this->language->get('text_meest'),      'warehouse' => true),
			array('code' => 'delivery.courier',    'key' => 'courier',    'title' => $this->language->get('text_courier'),    'warehouse' => false),
			array('code' => 'delivery.pickup',     'key' => 'pickup',     'title' => $this->language->get('text_pickup'),     'warehouse' => false)
		);

		$data['carriers'] = array();

		foreach ($all_carriers as $carrier) {
			if ($this->config->get('shipping_delivery_' . $carrier['key'] . '_enabled') !== '0') {
				$data['carriers'][] = $carrier;
			}
		}

		// оплати — беремо назву одразу після завантаження мовного файла,
		// бо всі використовують один ключ text_title
		$data['payment_methods'] = array();

		if ($this->config->get('payment_cod_status')) {
			$this->load->language('extension/payment/cod');
			$data['payment_methods'][] = array('code' => 'cod', 'title' => $this->language->get('text_title'));
		}

		if ($this->config->get('payment_bank_transfer_status')) {
			$this->load->language('extension/payment/bank_transfer');
			$data['payment_methods'][] = array('code' => 'bank_transfer', 'title' => $this->language->get('text_title'));
		}

		if ($this->config->get('payment_wayforpay_status')) {
			$this->load->language('extension/payment/wayforpay');

			$title = $this->config->get('payment_wayforpay_title' . (int)$this->config->get('config_language_id'));
			$data['payment_methods'][] = array('code' => 'wayforpay', 'title' => $title ?: $this->language->get('text_title'));
		}

		// підказки міст для автокомпліту
		$data['cities'] = array();

		$warehouse_file = DIR_APPLICATION . 'data/warehouses.json';

		if (is_file($warehouse_file)) {
			$warehouses = json_decode(file_get_contents($warehouse_file), true);

			if (is_array($warehouses)) {
				foreach (array_keys($warehouses) as $city) {
					if ($city != '_default') {
						$data['cities'][] = $city;
					}
				}
			}
		}

		$data['pickup_address'] = $this->config->get('config_address');

		// адреса залогіненого покупця підставляється в поля
		$data['default_city'] = '';
		$data['default_address'] = '';
		$data['default_dest_type'] = '';

		if ($this->customer->isLogged() && $this->customer->getAddressId()) {
			$this->load->model('account/address');

			$address = $this->model_account_address->getAddress($this->customer->getAddressId());

			if ($address) {
				$data['default_city'] = $address['city'];
				$data['default_address'] = $address['address_1'];

				// зі збереженої адреси видно, що це було: відділення, поштомат
				// чи курʼєрська адреса — підставляємо відповідний спосіб отримання
				if (mb_stripos($address['address_1'], 'поштомат') !== false) {
					$data['default_dest_type'] = 'postomat';
				} elseif (mb_stripos($address['address_1'], 'відділення') !== false || mb_stripos($address['address_1'], 'отделение') !== false) {
					$data['default_dest_type'] = 'branch';
				} else {
					$data['default_dest_type'] = 'courier';
				}
			}
		}

		// склад замовлення для бічної панелі
		$this->load->model('tool/image');

		$data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			$data['products'][] = array(
				'name'     => $product['name'],
				'thumb'    => $this->model_tool_image->resize($product['image'] ? $product['image'] : 'placeholder.png', 74, 74),
				'quantity' => $product['quantity'],
				'price'    => $this->currency->format($product['price'], $this->session->data['currency']),
				'total'    => $this->currency->format($product['total'], $this->session->data['currency']),
				'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		$data['totals'] = $this->cartTotals();

		// GA4 begin_checkout: склад кошика в поточній валюті покупця
		$ga_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
		$ga_items = array();
		$ga_value = 0;

		foreach ($this->cart->getProducts() as $product) {
			$ga_items[] = array(
				'item_id'    => (int)$product['product_id'],
				'item_name'  => $product['name'],
				'item_brand' => 'Hydrophob',
				'price'      => round((float)$this->currency->convert($product['price'], $this->config->get('config_currency'), $ga_currency), 2),
				'quantity'   => (int)$product['quantity']
			);

			$ga_value += (float)$this->currency->convert($product['total'], $this->config->get('config_currency'), $ga_currency);
		}

		$data['ga_checkout'] = $ga_items ? array(
			'currency' => $ga_currency,
			'value'    => round($ga_value, 2),
			'items'    => $ga_items
		) : array();

		// «також купують» у бічній панелі — той самий модуль, компактний вигляд
		$data['cart_related'] = $this->load->controller('extension/module/hp_cart_related/aside');

		$data['city_search_url'] = $this->url->link('tool/city');
		$data['warehouse_search_url'] = $this->url->link('extension/module/warehouse_suggest');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}

	private function cartTotals() {
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$sort_order = array();
		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$out = array();

		foreach ($totals as $item) {
			$out[] = array(
				'title' => $item['title'],
				'text'  => $this->currency->format($item['value'], $this->session->data['currency'])
			);
		}

		return $out;
	}
}
