<?php
// Вітальна сторінка першого візиту: показується один раз (localStorage hydro_visited),
// прогріває кеш hero-відео головної і віддає PageSpeed легку сторінку.
class ControllerCommonWelcome extends Controller {
	public function index() {
		$this->response->setOutput($this->render());
	}

	// Повертає HTML вітальної сторінки (використовує і common/home при першому візиті)
	public function render() {
		$this->load->language('common/welcome');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setDescription($this->language->get('text_meta_description'));

		foreach (array('heading_title', 'text_eyebrow', 'text_lead', 'text_scroll_pre', 'text_scroll',
			'text_choose_eyebrow', 'text_choose_title', 'text_choose_lead', 'text_go_home',
			'text_sound_on', 'text_sound_off', 'text_seo',
			'text_perk_delivery', 'text_perk_payment', 'text_perk_range') as $key) {
			$data[$key] = $this->language->get($key);
		}

		// метадані сторінки (контролер може викликатись і з common/home)
		$data['title'] = $this->document->getTitle();
		$data['description'] = $this->document->getDescription();
		$data['lang'] = $this->language->get('code');
		$data['base'] = $this->config->get('config_url');

		$data['icon'] = '';
		$icon_path = $this->config->get('config_icon');
		if ($icon_path && is_file(DIR_IMAGE . $icon_path)) {
			$data['icon'] = $this->config->get('config_url') . 'image/' . $icon_path;
		}

		// вітальна — перша сторінка нового відвідувача, тож аналітика тут теж
		$data['ga4_id'] = trim((string)$this->config->get('config_ga4_id'));
		$data['ga4_debug'] = (bool)$this->config->get('config_ga4_debug');

		$data['logo'] = 'catalog/view/theme/default/img/logo-animated.svg';
		$data['video'] = 'catalog/view/theme/default/vid/porsche.mp4';

		// на вузьких екранах віддаємо легшу копію того ж ролика
		$data['video_mobile'] = is_file(DIR_APPLICATION . 'view/theme/default/vid/porsche-mobile.mp4')
			? 'catalog/view/theme/default/vid/porsche-mobile.mp4'
			: '';
		$data['poster'] = 'image/catalog/video-posters/hero-porsche-d41ff2.webp';

		// решта hero-відео головної — прогріваються фоном після завантаження
		$data['prefetch'] = array(
			'catalog/view/theme/default/vid/moto.mp4',
			'catalog/view/theme/default/vid/instruction.mp4',
			'catalog/view/theme/default/vid/twerk.mp4'
		);

		$data['home'] = $this->url->link('common/home');
		$data['phone'] = $this->config->get('config_telephone');

		// плитки напрямків — беремо з модуля hp_categories, щоб контент був один
		$data['items'] = array();

		$module_query = $this->db->query("SELECT setting FROM " . DB_PREFIX . "module WHERE code = 'hp_categories' ORDER BY module_id ASC LIMIT 1");

		if ($module_query->num_rows) {
			$setting = json_decode($module_query->row['setting'], true);
			$language_id = (int)$this->config->get('config_language_id');

			if (!empty($setting['items']) && is_array($setting['items'])) {
				foreach ($setting['items'] as $item) {
					$title = isset($item[$language_id]['title']) ? trim($item[$language_id]['title']) : '';

					if ($title === '') {
						continue;
					}

					$image = '';

					if (!empty($item['image'])) {
						$image_path = html_entity_decode($item['image'], ENT_QUOTES, 'UTF-8');

						if (is_file(DIR_IMAGE . $image_path)) {
							$image = 'image/' . $image_path;
						}
					}

					$data['items'][] = array(
						'title' => $title,
						'image' => $image,
						'href'  => isset($item['href']) ? $item['href'] : ''
					);
				}
			}
		}

		$data['footer_links'] = array();

		$this->load->model('catalog/information');

		foreach ($this->model_catalog_information->getInformations() as $information) {
			$data['footer_links'][] = array(
				'title' => $information['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $information['information_id'])
			);
		}

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		return $this->load->view('common/welcome', $data);
	}
}
