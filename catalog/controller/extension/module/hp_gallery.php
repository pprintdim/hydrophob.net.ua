<?php
// "Hydrophob у дії": сітка фото/відео з лайтбоксом. Репітер у налаштуваннях модуля.
class ControllerExtensionModuleHpGallery extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_gallery');

		$data['text_alt'] = $this->language->get('text_alt');
		$data['text_close'] = $this->language->get('text_close');
		$data['text_prev'] = $this->language->get('text_prev');
		$data['text_next'] = $this->language->get('text_next');

		$language_id = (int)$this->config->get('config_language_id');

		$data['heading_title'] = $this->language->get('heading_title');

		if (!empty($setting['gallery_description'][$language_id]['heading_title'])) {
			$data['heading_title'] = $setting['gallery_description'][$language_id]['heading_title'];
		}

		$video_dir = 'catalog/view/theme/default/vid/';

		$data['items'] = array();

		if (!empty($setting['items']) && is_array($setting['items'])) {
			foreach ($setting['items'] as $item) {
				$image = '';

				if (!empty($item['image'])) {
					$image_path = html_entity_decode($item['image'], ENT_QUOTES, 'UTF-8');

					if (is_file(DIR_IMAGE . $image_path)) {
						$image = 'image/' . $image_path;
					}
				}

				$video = !empty($item['video']) ? basename(trim($item['video'])) : '';

				if ($video !== '') {
					// відео: постер обов'язковий як прев'ю-плитка
					$data['items'][] = array(
						'type'   => 'video',
						'src'    => $video_dir . $video,
						'thumb'  => $image,
						'poster' => $image
					);
				} elseif ($image !== '') {
					$data['items'][] = array(
						'type'   => 'image',
						'src'    => $image,
						'thumb'  => $image,
						'poster' => ''
					);
				}
			}
		}

		if (!$data['items']) {
			return '';
		}

		// VideoObject на кожен ролик — Google показує їх у відео-видачі
		$this->load->model('tool/video');

		$base = rtrim($this->config->get('config_ssl') ?: $this->config->get('config_url'), '/') . '/';
		$data['schema_videos'] = array();

		foreach ($data['items'] as $item) {
			if ($item['type'] !== 'video') {
				continue;
			}

			$file = basename($item['src']);
			$thumb = $item['poster'] ? $base . ltrim($item['poster'], '/') : $this->model_tool_video->poster($file);

			if (!$thumb) {
				continue;
			}

			$block = array(
				'@context'     => 'https://schema.org',
				'@type'        => 'VideoObject',
				'name'         => $this->model_tool_video->title($file),
				'description'  => $this->model_tool_video->description($file),
				'thumbnailUrl' => $thumb,
				'contentUrl'   => $base . ltrim($item['src'], '/'),
				'uploadDate'   => date('c', is_file(DIR_APPLICATION . '../' . $item['src']) ? filemtime(DIR_APPLICATION . '../' . $item['src']) : time()),
				'isFamilyFriendly' => true,
				'publisher'    => array(
					'@type' => 'Organization',
					'name'  => html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')
				)
			);

			$seconds = $this->model_tool_video->seconds($file);

			if ($seconds) {
				$block['duration'] = 'PT' . (int)floor($seconds / 60) . 'M' . ($seconds % 60) . 'S';
			}

			$data['schema_videos'][] = $block;
		}

		return $this->load->view('extension/module/hp_gallery', $data);
	}
}
