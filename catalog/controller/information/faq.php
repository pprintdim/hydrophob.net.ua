<?php
// Сторінка «Питання та відповіді» (окремий роут information/faq).
// Контент береться з мовного файлу — редагується там або через модуль hp_faq на лейауті.
class ControllerInformationFaq extends Controller {
	public function index() {
		$this->load->language('information/faq');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('text_meta_description'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/faq')
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_cta_name'] = $this->language->get('text_cta_name');
		$data['text_cta_text'] = $this->language->get('text_cta_text');
		$data['text_ask'] = $this->language->get('text_ask');

		$data['phone'] = $this->config->get('config_telephone');

		$data['groups'] = array();

		for ($g = 1; $g <= 3; $g++) {
			$group_title = $this->language->get('group' . $g . '_title');

			if (!$group_title || $group_title == 'group' . $g . '_title') {
				continue;
			}

			$items = array();

			for ($i = 1; $i <= 6; $i++) {
				$q = $this->language->get('group' . $g . '_q' . $i);
				$a = $this->language->get('group' . $g . '_a' . $i);

				if (!$q || $q == 'group' . $g . '_q' . $i) {
					continue;
				}

				$items[] = array('question' => $q, 'answer' => $a);
			}

			if ($items) {
				$data['groups'][] = array('title' => $group_title, 'items' => $items);
			}
		}

		// ── Структуровані дані: усі питання сторінки одним FAQPage ──
		$questions = array();

		foreach ($data['groups'] as $group) {
			foreach ($group['items'] as $item) {
				$questions[] = array(
					'@type'          => 'Question',
					'name'           => trim(strip_tags(html_entity_decode($item['question'], ENT_QUOTES, 'UTF-8'))),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => trim(strip_tags(html_entity_decode($item['answer'], ENT_QUOTES, 'UTF-8')))
					)
				);
			}
		}

		$data['schema_blocks'] = array();

		if ($questions) {
			$data['schema_blocks'][] = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'name'       => $data['heading_title'],
				'url'        => $this->url->link('information/faq'),
				'mainEntity' => $questions
			);
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/faq', $data));
	}
}
