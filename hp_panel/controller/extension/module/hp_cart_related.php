<?php
// Акордеон "Питання та відповіді": репітер питання/відповідь по мовах.
class ControllerExtensionModuleHpCartRelated extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/hp_cart_related');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('hp_cart_related', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/hp_cart_related', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/hp_cart_related', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $data['action']
		);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['name'] = $this->value('name', isset($module_info) ? $module_info : array(), '');
		$data['status'] = $this->value('status', isset($module_info) ? $module_info : array(), 0);
		$data['limit'] = $this->value('limit', isset($module_info) ? $module_info : array(), 8);
		$data['cart_related_description'] = $this->value('cart_related_description', isset($module_info) ? $module_info : array(), array());

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hp_cart_related', $data));
	}

	private function value($key, $module_info, $default) {
		if (isset($this->request->post[$key])) {
			return $this->request->post[$key];
		}

		if (isset($module_info[$key])) {
			return $module_info[$key];
		}

		return $default;
	}

	private function ukLanguageId() {
		$this->load->model('localisation/language');

		foreach ($this->model_localisation_language->getLanguages() as $language) {
			if ($language['code'] == 'uk-ua') {
				return (int)$language['language_id'];
			}
		}

		return 0;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/hp_cart_related')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}
