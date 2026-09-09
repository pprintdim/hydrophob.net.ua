<?php
// Плитки напрямків (.variants) на головній: репітер зображення+назва+посилання.
class ControllerExtensionModuleHpCategories extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_categories');

		$language_id = (int)$this->config->get('config_language_id');

		$data['items'] = array();

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

		if (!$data['items']) {
			return '';
		}

		return $this->load->view('extension/module/hp_categories', $data);
	}
}
