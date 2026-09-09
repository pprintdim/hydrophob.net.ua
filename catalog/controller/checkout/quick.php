<?php
// Швидке замовлення в один крок: покупець лишає імʼя й телефон прямо на картці
// товару, менеджер передзвонює і уточнює доставку/оплату. Замовлення створюється
// повноцінним записом OpenCart (видно в Продажі → Замовлення), тому далі з ним
// працюють як зі звичайним. Кошик при цьому не чіпаємо — товар іде повз нього.
class ControllerCheckoutQuick extends Controller {
	public function confirm() {
		$this->load->language('checkout/quick');

		$json = array();

		if (!isset($this->request->server['REQUEST_METHOD']) || $this->request->server['REQUEST_METHOD'] != 'POST') {
			$json['error']['warning'] = $this->language->get('error_request');
			$this->respond($json);
			return;
		}

		$post = $this->request->post;

		$product_id = isset($post['product_id']) ? (int)$post['product_id'] : 0;
		$quantity   = isset($post['quantity']) ? (int)$post['quantity'] : 1;
		$firstname  = isset($post['firstname']) ? trim($post['firstname']) : '';
		$telephone  = isset($post['telephone']) ? trim($post['telephone']) : '';
		$email      = isset($post['email']) ? trim($post['email']) : '';
		$comment    = isset($post['comment']) ? trim($post['comment']) : '';

		// приховане поле-пастка: люди його не заповнюють, боти — так
		if (!empty($post['company_website'])) {
			$json['error']['warning'] = $this->language->get('error_request');
			$this->respond($json);
			return;
		}

		if ((utf8_strlen($firstname) < 2) || (utf8_strlen($firstname) > 32)) {
			$json['error']['firstname'] = $this->language->get('error_firstname');
		}

		// у телефоні має бути щонайменше 9 цифр — коротші номери відсіюємо
		if (utf8_strlen(preg_replace('/\D/', '', $telephone)) < 9 || utf8_strlen($telephone) > 32) {
			$json['error']['telephone'] = $this->language->get('error_telephone');
		}

		if ($email !== '' && (utf8_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
			$json['error']['email'] = $this->language->get('error_email');
		}

		if (utf8_strlen($comment) > 500) {
			$comment = utf8_substr($comment, 0, 500);
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if (!$product_info) {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		// не частіше одного замовлення на 20 секунд з однієї сесії
		if (!$json && isset($this->session->data['quick_order_time']) && (time() - $this->session->data['quick_order_time']) < 20) {
			$json['error']['warning'] = $this->language->get('error_too_often');
		}

		if ($json) {
			$this->respond($json);
			return;
		}

		if ($quantity < (int)$product_info['minimum']) {
			$quantity = (int)$product_info['minimum'];
		}

		if ($quantity < 1) {
			$quantity = 1;
		}

		if ($quantity > 999) {
			$quantity = 999;
		}

		$option_data = $this->buildOptions($product_id, isset($post['option']) ? (array)$post['option'] : array());

		// ціна товару з урахуванням акції та надбавок обраних опцій
		$price = (float)$product_info['price'];

		$special = $this->getSpecialPrice($product_id);

		if ($special !== false) {
			$price = $special;
		}

		foreach ($option_data as $option) {
			if ($option['price_prefix'] == '+') {
				$price += $option['price'];
			} elseif ($option['price_prefix'] == '-') {
				$price -= $option['price'];
			}
		}

		$price_tax = $this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax'));
		$total     = $price_tax * $quantity;

		$order_id = $this->createOrder($product_info, $option_data, $quantity, $price_tax, $total, array(
			'firstname' => $firstname,
			'telephone' => $telephone,
			'email'     => $email,
			'comment'   => $comment
		));

		$this->session->data['quick_order_time'] = time();

		$json['success']  = sprintf($this->language->get('text_success'), $order_id);
		$json['order_id'] = $order_id;

		// GA4 purchase: купівля в один клік — таке саме замовлення, як через касу
		$ga_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');

		$json['ga_purchase'] = array(
			'transaction_id' => (string)$order_id,
			'currency'       => $ga_currency,
			'value'          => round((float)$this->currency->convert($total, $this->config->get('config_currency'), $ga_currency), 2),
			'items'          => array(array(
				'item_id'    => (int)$product_info['product_id'],
				'item_name'  => $product_info['name'],
				'item_brand' => 'Hydrophob',
				'price'      => round((float)$this->currency->convert($price_tax, $this->config->get('config_currency'), $ga_currency), 2),
				'quantity'   => (int)$quantity
			))
		);

		$this->respond($json);
	}

	// Опції товару, обрані в попапі, у форматі, який очікує addOrder().
	private function buildOptions($product_id, $selected) {
		$option_data = array();

		if (!$selected) {
			return $option_data;
		}

		$product_options = $this->model_catalog_product->getProductOptions($product_id);

		foreach ($product_options as $product_option) {
			if (!isset($selected[$product_option['product_option_id']])) {
				continue;
			}

			$value = $selected[$product_option['product_option_id']];

			if (!in_array($product_option['type'], array('select', 'radio'))) {
				continue;
			}

			foreach ($product_option['product_option_value'] as $option_value) {
				if ($option_value['product_option_value_id'] != $value) {
					continue;
				}

				$option_data[] = array(
					'product_option_id'       => $product_option['product_option_id'],
					'product_option_value_id' => $option_value['product_option_value_id'],
					'option_id'               => $product_option['option_id'],
					'option_value_id'         => $option_value['option_value_id'],
					'name'                    => $product_option['name'],
					'value'                   => $option_value['name'],
					'type'                    => $product_option['type'],
					'price'                   => (float)$option_value['price'],
					'price_prefix'            => $option_value['price_prefix']
				);
			}
		}

		return $option_data;
	}

	// Активна акційна ціна товару або false.
	private function getSpecialPrice($product_id) {
		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info && $product_info['special']) {
			return (float)$product_info['special'];
		}

		return false;
	}

	private function createOrder($product_info, $option_data, $quantity, $price_tax, $total, $customer) {
		$this->load->model('checkout/order');

		$order_data = array();

		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id']       = $this->config->get('config_store_id');
		$order_data['store_name']     = $this->config->get('config_name');
		$order_data['store_url']      = $this->config->get('config_url');

		if ($this->customer->isLogged()) {
			$order_data['customer_id']       = $this->customer->getId();
			$order_data['customer_group_id'] = $this->customer->getGroupId();
			$order_data['lastname']          = $this->customer->getLastName();
		} else {
			$order_data['customer_id']       = 0;
			$order_data['customer_group_id'] = $this->config->get('config_customer_group_id');
			$order_data['lastname']          = '';
		}

		$order_data['firstname']    = $customer['firstname'];
		$order_data['telephone']    = $customer['telephone'];
		// без email штатні листи OpenCart падають, тож підставляємо адресу магазину
		$order_data['email']        = $customer['email'] !== '' ? $customer['email'] : $this->config->get('config_email');
		$order_data['fax']          = '';
		$order_data['custom_field'] = array();

		// Доставку й оплату менеджер узгоджує по телефону — лишаємо порожніми,
		// але з підписом, щоб у замовленні було видно, звідки воно прийшло.
		$order_data['payment_firstname']      = $customer['firstname'];
		$order_data['payment_lastname']       = '';
		$order_data['payment_company']        = '';
		$order_data['payment_address_1']      = '';
		$order_data['payment_address_2']      = '';
		$order_data['payment_city']           = '';
		$order_data['payment_postcode']       = '';
		$order_data['payment_country']        = '';
		$order_data['payment_country_id']     = 0;
		$order_data['payment_zone']           = '';
		$order_data['payment_zone_id']        = 0;
		$order_data['payment_address_format'] = '';
		$order_data['payment_custom_field']   = array();
		$order_data['payment_method']         = $this->language->get('text_payment_method');
		$order_data['payment_code']           = 'quick';

		$order_data['shipping_firstname']      = $customer['firstname'];
		$order_data['shipping_lastname']       = '';
		$order_data['shipping_company']        = '';
		$order_data['shipping_address_1']      = '';
		$order_data['shipping_address_2']      = '';
		$order_data['shipping_city']           = '';
		$order_data['shipping_postcode']       = '';
		$order_data['shipping_country']        = '';
		$order_data['shipping_country_id']     = 0;
		$order_data['shipping_zone']           = '';
		$order_data['shipping_zone_id']        = 0;
		$order_data['shipping_address_format'] = '';
		$order_data['shipping_custom_field']   = array();
		$order_data['shipping_method']         = $this->language->get('text_shipping_method');
		$order_data['shipping_code']           = 'quick';

		$order_data['products'] = array(array(
			'product_id' => $product_info['product_id'],
			'name'       => $product_info['name'],
			'model'      => $product_info['model'],
			'option'     => $option_data,
			'download'   => array(),
			'quantity'   => $quantity,
			'subtract'   => $product_info['subtract'],
			'price'      => $price_tax,
			'total'      => $total,
			'tax'        => 0,
			'reward'     => 0
		));

		$order_data['vouchers'] = array();

		$order_data['totals'] = array(
			array(
				'code'       => 'sub_total',
				'title'      => $this->language->get('text_sub_total'),
				'value'      => $total,
				'sort_order' => 1
			),
			array(
				'code'       => 'total',
				'title'      => $this->language->get('text_total'),
				'value'      => $total,
				'sort_order' => 9
			)
		);

		$note = $this->language->get('text_order_source');

		if ($customer['comment'] !== '') {
			$note .= "\n" . $customer['comment'];
		}

		$order_data['comment']         = $note;
		$order_data['total']           = $total;
		$order_data['affiliate_id']    = 0;
		$order_data['commission']      = 0;
		$order_data['marketing_id']    = 0;
		$order_data['tracking']        = '';
		$order_data['language_id']     = $this->config->get('config_language_id');
		$order_data['currency_id']     = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code']   = $this->session->data['currency'];
		$order_data['currency_value']  = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip']              = $this->request->server['REMOTE_ADDR'];
		$order_data['forwarded_ip']    = isset($this->request->server['HTTP_X_FORWARDED_FOR']) ? $this->request->server['HTTP_X_FORWARDED_FOR'] : '';
		$order_data['user_agent']      = isset($this->request->server['HTTP_USER_AGENT']) ? $this->request->server['HTTP_USER_AGENT'] : '';
		$order_data['accept_language'] = isset($this->request->server['HTTP_ACCEPT_LANGUAGE']) ? $this->request->server['HTTP_ACCEPT_LANGUAGE'] : '';

		$order_id = $this->model_checkout_order->addOrder($order_data);

		// Лист покупцю не шлемо (email може бути адресою магазину), сповіщення
		// менеджеру приходить штатною подією mail/order/alert.
		$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'), $note, false);

		// швидке замовлення дублюємо в списку заявок: менеджеру видно контакт
		// і товар навіть якщо лист-сповіщення не дійшов
		$this->load->model('tool/lead');

		$this->model_tool_lead->store('quick', array(
			'name'      => $order_data['firstname'],
			'email'     => $order_data['email'],
			'telephone' => $order_data['telephone'],
			'enquiry'   => $note
		), 'Швидке замовлення №' . $order_id);

		return $order_id;
	}

	private function respond($json) {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
