<?php
// SEO-текст про бренд з collapse-описом ("Показати більше" / "Згорнути").
class ControllerExtensionModuleHpAbout extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_about');

		$data['text_show_more'] = $this->language->get('text_show_more');
		$data['text_show_less'] = $this->language->get('text_show_less');

		$language_id = (int)$this->config->get('config_language_id');

		$data['html'] = '';

		if (!empty($setting['about_description'][$language_id]['text'])) {
			$data['html'] = html_entity_decode($setting['about_description'][$language_id]['text'], ENT_QUOTES, 'UTF-8');
		}

		if (trim(strip_tags($data['html'])) === '') {
			return '';
		}

		return $this->load->view('extension/module/hp_about', $data);
	}
}
