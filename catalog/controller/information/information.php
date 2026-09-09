<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}


		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

			$data['heading_title'] = $information_info['title'];

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			// Донатяжка верстки: кроки доставки + CTA (за id сторінки)
			$information_id = (int)$this->request->get['information_id'];

			$data['show_steps'] = ($information_id == 6);   // Доставка та оплата
			$data['show_cta'] = in_array($information_id, array(4, 6));

			// «Показати більше» лише там, де це презентаційний текст;
			// юридичні сторінки (політика, умови) читаються цілком
			$data['collapsible'] = in_array($information_id, array(4, 6));
			$data['telephone'] = $this->config->get('config_telephone');

			$data['text_steps_title'] = $this->language->get('text_steps_title');
			$data['text_cta_name'] = $this->language->get('text_cta_name');
			$data['text_cta_text'] = $this->language->get('text_cta_text');
			$data['text_cta_ask'] = $this->language->get('text_cta_ask');

			$data['steps'] = array();
			for ($i = 1; $i <= 4; $i++) {
				$step_name = $this->language->get('step' . $i . '_name');
				if ($step_name && $step_name != 'step' . $i . '_name') {
					$data['steps'][] = array('name' => $step_name, 'text' => $this->language->get('step' . $i . '_text'));
				}
			}

			$perk_icons = array(
				'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>',
				'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
				'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>',
				'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'
			);

			$data['perks'] = array();
			for ($i = 1; $i <= 4; $i++) {
				$perk_text = $this->language->get('perk' . $i . '_text');
				if ($perk_text && $perk_text != 'perk' . $i . '_text') {
					$data['perks'][] = array('icon' => $perk_icons[$i - 1], 'text' => $perk_text);
				}
			}

			$data['text_show_more'] = $this->language->get('text_show_more');
			$data['text_show_less'] = $this->language->get('text_show_less');

			// ілюстрація сторінки «Про нас» — як у верстці
			$data['about_image'] = '';

			if ($information_id == 4 && is_file(DIR_IMAGE . 'hydrophob/about-bg.webp')) {
				$data['about_image'] = $this->config->get('config_url') . 'image/hydrophob/about-bg.webp';
			}

			// ── Структуровані дані сторінки: «Про нас» окремим типом, решта — WebPage ──
			$page_schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => $information_id == 4 ? 'AboutPage' : 'WebPage',
				'name'        => $information_info['title'],
				'url'         => $this->url->link('information/information', 'information_id=' . $information_id),
				'inLanguage'  => $this->config->get('config_language') == 'ru-ru' ? 'ru-UA' : 'uk-UA',
				'description' => trim((string)$information_info['meta_description']) !== ''
					? $information_info['meta_description']
					: utf8_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($data['description']))), 0, 300),
				'isPartOf'    => array(
					'@type' => 'WebSite',
					'name'  => html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'),
					'url'   => $this->url->link('common/home')
				)
			);

			$data['schema_blocks'] = array($page_schema);

			// «Доставка та оплата»: кроки замовлення — готовий HowTo
			if ($information_id == 6 && count($data['steps']) >= 2) {
				$steps = array();

				foreach ($data['steps'] as $index => $step) {
					$steps[] = array(
						'@type'    => 'HowToStep',
						'position' => $index + 1,
						'name'     => trim(strip_tags(html_entity_decode($step['name'], ENT_QUOTES, 'UTF-8'))),
						'text'     => trim(strip_tags(html_entity_decode($step['text'], ENT_QUOTES, 'UTF-8')))
					);
				}

				$data['schema_blocks'][] = array(
					'@context' => 'https://schema.org',
					'@type'    => 'HowTo',
					'name'     => $data['text_steps_title'],
					'step'     => $steps
				);
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			if ($information_id == 6) { // ID страницы "Доставка и оплата"
    $this->response->setOutput($this->load->view('information/shipping', $data));
} else {
    $this->response->setOutput($this->load->view('information/information', $data));
}

		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->addHeader('X-Robots-Tag: noindex');

		$this->response->setOutput($output);
	}
}
