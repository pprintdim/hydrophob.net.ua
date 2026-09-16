<?php
// Cookie-банер: показується раз, вибір памʼятається в localStorage і в куці.
// Аналітика вмикається лише після згоди (Google Consent Mode), тому банер
// має сенс не тільки юридично, а й технічно.
class ControllerCommonCookie extends Controller {
	public function index() {
		$this->load->language('common/cookie');

		foreach (array('text_message', 'text_more', 'button_necessary', 'button_accept') as $key) {
			$data[$key] = $this->language->get($key);
		}

		// Посилання на політику конфіденційності шукаємо серед інфо-сторінок,
		// щоб не зашивати id: у кожного магазину він свій.
		$data['privacy_href'] = '';

		$this->load->model('catalog/information');

		foreach ($this->model_catalog_information->getInformations() as $information) {
			$title = mb_strtolower($information['title']);

			if (mb_strpos($title, 'конфіденц') !== false || mb_strpos($title, 'конфиденц') !== false || mb_strpos($title, 'privacy') !== false) {
				$data['privacy_href'] = $this->url->link('information/information', 'information_id=' . (int)$information['information_id']);
				break;
			}
		}

		return $this->load->view('common/cookie', $data);
	}
}
