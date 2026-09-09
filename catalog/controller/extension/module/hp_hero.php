<?php
// Hero-відеослайдер головної: слайди з репітера модуля (Design->Layouts).
class ControllerExtensionModuleHpHero extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_hero');

		$data['text_sound_on'] = $this->language->get('text_sound_on');
		$data['text_sound_off'] = $this->language->get('text_sound_off');
		$data['text_slide'] = $this->language->get('text_slide');

		$language_id = (int)$this->config->get('config_language_id');

		$video_dir = 'catalog/view/theme/default/vid/';

		$data['slides'] = array();

		if (!empty($setting['slides']) && is_array($setting['slides'])) {
			foreach ($setting['slides'] as $slide) {
				$title = isset($slide[$language_id]['title']) ? trim($slide[$language_id]['title']) : '';
				$video = isset($slide['video']) ? basename(trim($slide['video'])) : '';

				if ($title === '' || $video === '') {
					continue;
				}

				$poster = '';

				if (!empty($slide['poster'])) {
					$poster_path = html_entity_decode($slide['poster'], ENT_QUOTES, 'UTF-8');

					if (is_file(DIR_IMAGE . $poster_path)) {
						$poster = 'image/' . $poster_path;
					}
				}

				$data['slides'][] = array(
					'h'        => (isset($slide['h']) && $slide['h'] == 'h1') ? 'h1' : 'h2',
					'title'    => $title,
					'descr'    => isset($slide[$language_id]['descr']) ? $slide[$language_id]['descr'] : '',
					'note'     => isset($slide[$language_id]['note']) ? $slide[$language_id]['note'] : '',
					'btn_text' => isset($slide[$language_id]['btn_text']) ? $slide[$language_id]['btn_text'] : '',
					'btn_href' => isset($slide['btn_href']) ? $slide['btn_href'] : '',
					'video'    => $video_dir . $video,
					'poster'   => $poster
				);
			}
		}

		if (!$data['slides']) {
			return '';
		}

		return $this->load->view('extension/module/hp_hero', $data);
	}
}
