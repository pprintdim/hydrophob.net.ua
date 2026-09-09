<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$this->load->language('checkout/success');

		$order_id = isset($this->session->data['order_id']) ? (int)$this->session->data['order_id'] : 0;

		// GA4 purchase: замовлення вже створене, читаємо його ДО чистки сесії
		$data['ga_purchase'] = array();

		if ($order_id) {
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$items = array();

				foreach ($this->model_checkout_order->getOrderProducts($order_id) as $product) {
					$items[] = array(
						'item_id'   => $product['product_id'],
						'item_name' => $product['name'],
						'price'     => round((float)$product['price'], 2),
						'quantity'  => (int)$product['quantity']
					);
				}

				$data['ga_purchase'] = array(
					'transaction_id' => (string)$order_id,
					'value'          => round((float)$order_info['total'], 2),
					'currency'       => $order_info['currency_code'],
					'shipping'       => 0,
					'items'          => $items
				);
			}
		}

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$data['order_id'] = $order_id;
		$data['logged'] = $this->customer->isLogged();
		$data['home'] = $this->url->link('common/home');
		$data['catalog'] = $this->url->link('product/category', 'path=33');
		$data['orders'] = $this->url->link('account/order', '', true);

		$data['text_title'] = $this->language->get('text_status_title');
		$data['text_lead'] = $this->language->get('text_status_lead');
		$data['text_note'] = $this->language->get('text_status_note');
		$data['text_order'] = $this->language->get('text_status_order');
		$data['button_home'] = $this->language->get('button_status_home');
		$data['button_catalog'] = $this->language->get('button_status_catalog');

		$this->response->setOutput($this->load->view('checkout/success', $data));
	}
}