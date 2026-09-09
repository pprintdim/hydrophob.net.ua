<?php
class ControllerInformationContact extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		// у <title> — розгорнутий варіант, у h1 шаблону — коротке «Контакти»
		$this->document->setTitle($this->language->get('text_meta_title'));
		$data['heading_title'] = $this->language->get('heading_title');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setReplyTo($this->request->post['email']);
			$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->request->post['enquiry']);
			$mail->send();

			$this->response->redirect($this->url->link('information/contact/success'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['enquiry'])) {
			$data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$data['error_enquiry'] = '';
		}

		$data['button_submit'] = $this->language->get('button_submit');

		$data['action'] = $this->url->link('information/contact', '', true);

		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');

		// координати для OpenStreetMap-карти (той самий формат, що у футері)
		$geo = array_map('trim', explode(',', (string)$this->config->get('config_geocode')));
		$data['map_lat'] = isset($geo[0]) && is_numeric($geo[0]) ? $geo[0] : '';
		$data['map_lng'] = isset($geo[1]) && is_numeric($geo[1]) ? $geo[1] : '';
		$data['map_title'] = $this->config->get('config_name');
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['fax'] = $this->config->get('config_fax');
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');
		$data['store_email'] = $this->config->get('config_email');


		$data['locations'] = array();

		$this->load->model('localisation/location');

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
				} else {
					$image = false;
				}

				$data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				);
			}
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'];
		} else {
			$data['enquiry'] = '';
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		// Донатяжка верстки: лід, месенджери, плашки допомоги, відео над картою
		$data['text_contacts_lead'] = $this->language->get('text_contacts_lead');
		$data['text_sound_on'] = $this->language->get('text_sound_on');

		$data['telegram'] = $this->language->get('contact_telegram');
		$data['viber'] = $this->config->get('config_telephone')
			? 'viber://chat?number=' . rawurlencode('+' . preg_replace('/[^0-9]/', '', $this->config->get('config_telephone')))
			: '';

		$data['help_items'] = array();
		for ($i = 1; $i <= 3; $i++) {
			$help_name = $this->language->get('help' . $i . '_name');
			if ($help_name && $help_name != 'help' . $i . '_name') {
				$data['help_items'][] = array(
					'name' => $help_name,
					'text' => $this->language->get('help' . $i . '_text')
				);
			}
		}

		// ── Структуровані дані: магазин з адресою, телефоном і графіком ──
		$store = array(
			'@context'  => 'https://schema.org',
			'@type'     => 'Store',
			'name'      => html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'),
			'url'       => $this->url->link('common/home'),
			'email'     => $this->config->get('config_email'),
			'address'   => array(
				'@type'          => 'PostalAddress',
				'streetAddress'  => trim(preg_replace('/\s+/u', ' ', html_entity_decode((string)$this->config->get('config_address'), ENT_QUOTES, 'UTF-8'))),
				'addressCountry' => 'UA'
			),
			'priceRange' => '$$',
			'currenciesAccepted' => 'UAH',
			'paymentAccepted'    => 'Cash, Credit Card'
		);

		if ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$store['image'] = ($this->config->get('config_ssl') ?: $this->config->get('config_url')) . 'image/' . $this->config->get('config_logo');
		}

		if (trim((string)$this->config->get('config_telephone')) !== '') {
			$store['telephone'] = trim($this->config->get('config_telephone'));
		}

		// «49.999, 36.230» з налаштувань магазину → geo-координати
		$geo_parts = array_map('trim', explode(',', (string)$this->config->get('config_geocode')));

		if (count($geo_parts) == 2 && is_numeric($geo_parts[0]) && is_numeric($geo_parts[1])) {
			$store['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float)$geo_parts[0],
				'longitude' => (float)$geo_parts[1]
			);
		}

		$open = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string)$this->config->get('config_open'), ENT_QUOTES, 'UTF-8'))));

		if ($open !== '') {
			// графік в налаштуваннях — вільний текст («Працюємо з 08:00 до 22:00,
			// сб-нд вихідний»); у schema.org він має бути форматом «Mo-Fr 08:00-22:00»,
			// тож витягуємо години, а сам текст лишаємо описом
			$store['description'] = $open;

			if (preg_match_all('/\b([0-2]?\d:[0-5]\d)\b/u', $open, $hours) && count($hours[1]) >= 2) {
				// згадка вихідних у тексті означає, що робочі дні — лише будні
				$weekend = preg_match('/(вихідн|выходн|сб|сб\.|субот)/ui', $open);

				$store['openingHoursSpecification'] = array(array(
					'@type'     => 'OpeningHoursSpecification',
					'dayOfWeek' => $weekend
						? array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')
						: array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
					'opens'     => $hours[1][0],
					'closes'    => $hours[1][1]
				));
			}
		}

		$data['schema_blocks'] = array($store, array(
			'@context' => 'https://schema.org',
			'@type'    => 'ContactPage',
			'name'     => $this->language->get('heading_title'),
			'url'      => $this->url->link('information/contact')
		));

		$data['contact_video'] = 'catalog/view/theme/default/vid/talk.mp4';
		$data['contact_poster'] = is_file(DIR_IMAGE . 'catalog/video-posters/hero-talk-02168e.webp')
			? 'image/catalog/video-posters/hero-talk-02168e.webp' : '';

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/contact', $data));
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
			$this->error['enquiry'] = $this->language->get('error_enquiry');
		}

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		return !$this->error;
	}

	public function success() {
		$this->load->language('information/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

 		$data['text_message'] = $this->language->get('text_message'); 

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
