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

		// офіційні профілі бренду: так Google зводить сайт і сторінки
		// в соцмережах в одну сутність (панель знань)
		$organisation['sameAs'] = array('https://t.me/Hydrophob1');

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
}
