<?php
// Тизер відгуків покупців: останні схвалені відгуки з БД, світлий/темний варіант.
class ControllerExtensionModuleHpReviewsTeaser extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/hp_reviews_teaser');

		$data['text_all'] = $this->language->get('text_all');
		$data['text_product'] = $this->language->get('text_product');
		$data['text_rating'] = $this->language->get('text_rating');

		$language_id = (int)$this->config->get('config_language_id');

		$data['heading_title'] = $this->language->get('heading_title');

		if (!empty($setting['teaser_description'][$language_id]['heading_title'])) {
			$data['heading_title'] = $setting['teaser_description'][$language_id]['heading_title'];
		}

		$data['light'] = !empty($setting['light']);

		$limit = !empty($setting['limit']) ? (int)$setting['limit'] : 3;

		$query = $this->db->query("SELECT r.review_id, r.product_id, r.author, r.text, r.rating, r.date_added, pd.name AS product_name FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product_description pd ON (r.product_id = pd.product_id AND pd.language_id = '" . $language_id . "') LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) WHERE r.status = '1' AND p.status = '1' ORDER BY r.date_added DESC LIMIT " . $limit);

		$data['reviews'] = array();

		foreach ($query->rows as $row) {
			$data['reviews'][] = array(
				'author'       => $row['author'],
				'initial'      => function_exists('mb_substr') ? mb_substr($row['author'], 0, 1) : substr($row['author'], 0, 1),
				'date'         => date('d/m/Y', strtotime($row['date_added'])),
				'source'       => $this->language->get('text_source'),
				'rating'       => (int)$row['rating'],
				'text'         => $row['text'],
				'product_name' => $row['product_name'],
				'product_href' => $row['product_name'] ? $this->url->link('product/product', 'product_id=' . $row['product_id']) : ''
			);
		}

		if (!$data['reviews']) {
			return '';
		}

		$data['all_href'] = $this->url->link('information/reviews');

		return $this->load->view('extension/module/hp_reviews_teaser', $data);
	}
}
