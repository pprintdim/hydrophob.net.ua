<?php
// Структуровані дані для пошуковиків. Загальносайтова частина (організація,
// пошук по сайту) рендериться з футера; типи сторінок додають свої блоки.
class ControllerCommonSchema extends Controller {
	public function index() {
		$store = $this->config->get('config_url') ?: HTTP_SERVER;
		$name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$organisation = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
			'name'     => $name,
			'url'      => $store,
			'email'    => $this->config->get('config_email'),
			'address'  => array(
				'@type'          => 'PostalAddress',
				'streetAddress'  => html_entity_decode($this->config->get('config_address'), ENT_QUOTES, 'UTF-8'),
				'addressCountry' => 'UA'
			),
			'brand'    => array('@type' => 'Brand', 'name' => 'Hydrophob')
		);

		if ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$organisation['logo'] = $store . 'image/' . $this->config->get('config_logo');
		}

		// Один бренд, кілька офіційних майданчиків: перелік дає пошуковикам
		// звʼязати їх у одну організацію (решта доменів групи + соцмережі
		// + профіль на маркетплейсі, який пошук уже знає).
		$organisation['sameAs'] = array_values(array_filter(array(
			$this->groupSite('https://hydrophob.net/'),
			$this->groupSite('https://hydrophob.ua/'),
			$this->groupSite('https://hydrophob.net.ua/'),
			$this->groupSite('https://hydrophob.com.ua/'),
			'https://hydrophob.in.ua/',
			'https://t.me/Hydrophob1',
			'https://www.tiktok.com/@hydrophob.ua'
		)));

		$telephone = trim((string)$this->config->get('config_telephone'));

		if ($telephone) {
			$organisation['contactPoint'] = array(
				'@type'             => 'ContactPoint',
				'telephone'         => $telephone,
				'contactType'       => 'customer service',
				'areaServed'        => 'UA',
				'availableLanguage' => array('uk', 'ru')
			);
		}

		$website = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => $name,
			'url'             => $store,
			'inLanguage'      => $this->config->get('config_language') == 'ru-ru' ? 'ru-UA' : 'uk-UA',
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => $store . 'index.php?route=product/search&search={search_term_string}'
				),
				'query-input' => 'required name=search_term_string'
			)
		);

		$data['blocks'] = array($organisation, $website);

		return $this->load->view('common/schema', $data);
	}

	// Сайт групи потрапляє в sameAs лише якщо це не поточний домен.
	private function groupSite($url) {
		$store = $this->config->get('config_url') ?: HTTP_SERVER;

		return rtrim($url, '/') === rtrim($store, '/') ? '' : $url;
	}
}
