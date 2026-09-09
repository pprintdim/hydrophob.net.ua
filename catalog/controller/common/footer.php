<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}
$data['store_email'] = $this->config->get('config_email');
$data['store_phone'] = $this->config->get('config_telephone');

		// логотип у футері веде на головну поточної мови (/ або /ru/)
		$data['home'] = $this->url->link('common/home');
		$data['contact'] = $this->url->link('information/contact');
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Тексти OTP-модалки (спільна для всього сайту)
		$this->load->language('common/user_popup');
		foreach (array('otp_title', 'otp_close', 'otp_sent', 'otp_digit', 'otp_confirm',
			'otp_resend', 'otp_sending', 'otp_resend_in', 'otp_error_generic', 'otp_error_incomplete', 'otp_need_email') as $otp_key) {
			$data[$otp_key] = $this->language->get($otp_key);
		}

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		$data['styles'] = $this->document->getStyles('footer');
		
		// нижній ряд футера: юридичні сторінки (як footer__legal у верстці)
		$this->load->model('catalog/information');

		$data['legal_links'] = array();

		foreach (array(6, 7, 3, 5) as $legal_id) { // 7 = «Обмін і повернення»
			$legal_info = $this->model_catalog_information->getInformation($legal_id);

			if ($legal_info) {
				$data['legal_links'][] = array(
					'text' => $legal_info['title'],
					'href' => $this->url->link('information/information', 'information_id=' . $legal_id)
				);
			}
		}

		// карта сайту — остання в тому ж списку «Покупцям»
		$data['legal_links'][] = array(
			'text' => $this->language->get('text_sitemap'),
			'href' => $this->url->link('information/sitemap')
		);

		// координати магазину для карти у футері (Налаштування -> Магазин -> Geocode)
		$geocode = array_map('trim', explode(',', (string)$this->config->get('config_geocode')));
		$data['map_lat'] = isset($geocode[0]) && is_numeric($geocode[0]) ? $geocode[0] : '';
		$data['map_lng'] = isset($geocode[1]) && is_numeric($geocode[1]) ? $geocode[1] : '';
		$data['map_title'] = $this->config->get('config_name');

		$data['text_in_cart'] = $this->language->get('text_in_cart');

		// модалка відгуку — спільна для сторінки товару й сторінки відгуків
		foreach (array('text_review_title', 'text_review_product', 'text_review_product_ph',
			'text_review_name', 'text_review_text', 'text_review_rating', 'text_review_send',
			'text_review_thanks', 'text_review_pick', 'text_review_phone', 'text_review_email') as $review_key) {
			$data[$review_key] = $this->language->get($review_key);
		}

		$data['review_search_url'] = $this->url->link('common/ajax_search/search', '', true);
		$data['review_action'] = html_entity_decode($this->url->link('product/product/write'), ENT_QUOTES, 'UTF-8');
		$this->load->language('product/product');
		$data['button_cart'] = $this->language->get('button_cart');

		$data['text_added_toast'] = $this->language->get('text_added_toast');
		$data['text_wish_added'] = $this->language->get('text_wish_added');
		$data['text_wish_removed'] = $this->language->get('text_wish_removed');

		$data['reviews_href'] = $this->url->link('information/reviews');
		$data['faq_href'] = $this->url->link('information/faq');

		$data['schema'] = $this->load->controller('common/schema');

		// дані залогіненого покупця для автозаповнення форм (попапи: швидке
		// замовлення, питання, відгук)
		$data['text_review_verify'] = $this->language->get('text_review_verify');
		$data['entry_quick_phone'] = $this->language->get('entry_quick_phone');
		$data['entry_quick_email'] = $this->language->get('entry_quick_email');

		$data['hp_customer'] = array();

		if ($this->customer->isLogged()) {
			$data['hp_customer'] = array(
				'firstname' => $this->customer->getFirstName(),
				'lastname'  => $this->customer->getLastName(),
				'email'     => $this->customer->getEmail(),
				'telephone' => $this->customer->getTelephone(),
				// контакти беруться з акаунта: у формах їх не редагують,
				// а міняють у кабінеті — туди веде олівець біля поля
				'edit_url'  => $this->url->link('account/edit', '', true),
				'edit_hint' => $this->language->get('text_edit_in_account')
			);
		}

		return $this->load->view('common/footer', $data);
	}
}
