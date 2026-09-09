<?php
// «Переглянуті нещодавно»: id товарів живуть у localStorage браузера, тому
// секція малюється на клієнті — сервер віддає лише каркас і тексти.
// Порожній список — секції на сторінці немає взагалі.
class ControllerExtensionModuleHpViewed extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_viewed');

		$data['heading_title'] = $this->language->get('heading_title');

		$language_id = (int)$this->config->get('config_language_id');

		if (!empty($setting['viewed_description'][$language_id]['heading_title'])) {
			$data['heading_title'] = $setting['viewed_description'][$language_id]['heading_title'];
		}

		$data['text_prev'] = $this->language->get('text_prev');
		$data['text_next'] = $this->language->get('text_next');
		$data['limit'] = !empty($setting['limit']) ? (int)$setting['limit'] : 12;

		// на картці товару сам товар зі списку виключаємо
		$data['current_product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		return $this->load->view('extension/module/hp_viewed', $data);
	}
}
