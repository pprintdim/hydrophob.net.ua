<?php
// Метадані відеороликів теми для sitemap і schema.org. Тривалість заміряна
// ffprobe один раз — щоб не смикати процес на кожен запит.
class ModelToolVideo extends Model {
	private $clips = array(
		'porsche'       => 20,
		'porsche-2'     => 15,
		'porsche-3'     => 18,
		'moto'          => 15,
		'moto-2'        => 15,
		'talk'          => 16,
		'talk-2'        => 16,
		'twerk'         => 16,
		'twerk-2'       => 21,
		'instruction'   => 83,
		'instruction-2' => 71,
		'HYDROFOB'      => 59
	);

	private function base() {
		return rtrim($this->config->get('config_ssl') ?: $this->config->get('config_url'), '/') . '/';
	}

	// Постер ролика: спершу той, що заданий у модулі, далі однойменний файл
	public function poster($file, $fallback = '') {
		$name = basename($file, '.mp4');

		if ($fallback && is_file(DIR_IMAGE . $fallback)) {
			return $this->base() . 'image/' . $fallback;
		}

		if (is_file(DIR_IMAGE . 'catalog/video-posters/' . $name . '.webp')) {
			return $this->base() . 'image/catalog/video-posters/' . $name . '.webp';
		}

		return '';
	}

	public function url($file) {
		return $this->base() . 'catalog/view/theme/default/vid/' . basename($file);
	}

	public function seconds($file) {
		$name = basename($file, '.mp4');

		return isset($this->clips[$name]) ? $this->clips[$name] : 0;
	}

	public function exists($file) {
		return is_file(DIR_APPLICATION . 'view/theme/default/vid/' . basename($file));
	}

	// Назва й опис ролика для пошуковика — з мовного файлу, з фолбеком на назву магазину
	public function title($file) {
		$this->load->language('tool/video');

		$name = basename($file, '.mp4');
		$key = 'video_' . str_replace('-', '_', strtolower($name));
		$text = $this->language->get($key);

		return $text && $text != $key ? $text : html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
	}

	public function description($file) {
		$this->load->language('tool/video');

		$name = basename($file, '.mp4');
		$key = 'video_' . str_replace('-', '_', strtolower($name)) . '_text';
		$text = $this->language->get($key);

		return $text && $text != $key ? $text : $this->title($file);
	}
}
