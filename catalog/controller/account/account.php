<?php
class ControllerAccountAccount extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/account');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		} 
		
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);		
		
		
		$this->load->model('account/customer');
		
$this->load->language('account/edit'); // entry_firstname/lastname/email/telephone

		// форма редагування прямо в кабінеті (патерн hydrophob.net)
		$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

		$data['firstname'] = $customer_info['firstname'];
		$data['lastname'] = $customer_info['lastname'];
		$data['email'] = $customer_info['email'];
		$data['telephone'] = $customer_info['telephone'];
		$data['save_action'] = $this->url->link('account/account/save', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('account/account', $data));
	}

	// ajax-збереження даних кабінету: імʼя, прізвище, телефон, підписка.
	// E-mail сюди не входить — він міняється лише через код підтвердження.
	public function save() {
		$this->load->language('account/account');
		$this->load->language('account/edit');

		$json = array();

		if (!$this->customer->isLogged()) {
			$json['error']['warning'] = $this->language->get('error_login_required');
			$json['redirect'] = $this->url->link('account/login', '', true);
		}

		if (!isset($this->request->server['REQUEST_METHOD']) || $this->request->server['REQUEST_METHOD'] != 'POST') {
			$json['error']['warning'] = 'Bad request';
		}

		if (!$json) {
			$firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
			$lastname  = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
			$telephone = isset($this->request->post['telephone']) ? trim($this->request->post['telephone']) : '';

			if ((utf8_strlen($firstname) < 1) || (utf8_strlen($firstname) > 32)) {
				$json['error']['firstname'] = $this->language->get('error_firstname');
			}

			if (utf8_strlen($lastname) > 32) {
				$json['error']['lastname'] = $this->language->get('error_lastname');
			}

			if ((utf8_strlen($telephone) < 3) || (utf8_strlen($telephone) > 32)) {
				$json['error']['telephone'] = $this->language->get('error_telephone');
			}
		}

		if (!$json) {
			$this->load->model('account/customer');

			$this->model_account_customer->editCustomer($this->customer->getId(), array(
				'firstname' => $firstname,
				'lastname'  => $lastname,
				'email'     => $this->customer->getEmail(),
				'telephone' => $telephone
			));

			$this->model_account_customer->editNewsletter(!empty($this->request->post['newsletter']) ? 1 : 0);

			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
