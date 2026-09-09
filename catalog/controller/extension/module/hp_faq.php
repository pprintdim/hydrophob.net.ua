<?php
// Акордеон "Питання та відповіді" + schema.org FAQPage.
class ControllerExtensionModuleHpFaq extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_faq');

		$language_id = (int)$this->config->get('config_language_id');

		$data['heading_title'] = $this->language->get('heading_title');

		if (!empty($setting['faq_description'][$language_id]['heading_title'])) {
			$data['heading_title'] = $setting['faq_description'][$language_id]['heading_title'];
		}

		$data['items'] = array();

		if (!empty($setting['items']) && is_array($setting['items'])) {
			foreach ($setting['items'] as $item) {
				$question = isset($item[$language_id]['question']) ? trim($item[$language_id]['question']) : '';

				if ($question === '') {
					continue;
				}

				$data['items'][] = array(
					'question' => $question,
					'answer'   => isset($item[$language_id]['answer']) ? $item[$language_id]['answer'] : ''
				);
			}
		}

		if (!$data['items']) {
			return '';
		}

		// ті самі питання у вигляді, який пошуковики показують як rich results
		$questions = array();

		foreach ($data['items'] as $item) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $item['question'],
				'acceptedAnswer' => array('@type' => 'Answer', 'text' => $item['answer'])
			);
		}

		$data['schema'] = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $questions);

		return $this->load->view('extension/module/hp_faq', $data);
	}
}
