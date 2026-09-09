<?php
// Секція «Залишились питання?» з попапом питання. Один модуль на весь сайт:
// вішається на будь-яку сторінку через лейаути, попап і бекенд завжди при ньому.
class ControllerExtensionModuleHpQuestionCta extends Controller {
	public function index() {
		$this->load->language('extension/module/hp_question_cta');
		$this->load->language('information/faq');

		$data['text_cta_name'] = $this->language->get('text_cta_name');
		$data['text_cta_text'] = $this->language->get('text_cta_text');
		$data['text_ask'] = $this->language->get('text_ask');

		// тексти модалки живуть у мовному файлі FAQ — форма та сама
		foreach (array('text_q_title', 'text_q_lead', 'entry_q_name', 'entry_q_contact', 'entry_q_text', 'button_q_send', 'text_q_thanks', 'ph_q_name', 'ph_q_contact', 'ph_q_text') as $key) {
			$data[$key] = $this->language->get($key);
		}

		$data['telephone'] = $this->config->get('config_telephone');

		// авторизованому підставляємо його контакти; гостю пошту треба підтвердити
		// кодом — існуючий акаунт логіниться, нового реєструємо (як на чекауті)
		$data['logged'] = $this->customer->isLogged();
		$data['customer_firstname'] = $data['logged'] ? $this->customer->getFirstName() : '';
		$data['customer_lastname'] = $data['logged'] ? $this->customer->getLastName() : '';
		$data['customer_telephone'] = $data['logged'] ? $this->customer->getTelephone() : '';
		$data['customer_email'] = $data['logged'] ? $this->customer->getEmail() : '';

		foreach (array('entry_q_phone', 'entry_q_email', 'ph_q_phone', 'ph_q_email', 'button_q_code', 'text_q_verify') as $key) {
			$data[$key] = $this->language->get($key);
		}

		return $this->load->view('extension/module/hp_question_cta', $data);
	}
}
