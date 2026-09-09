<?php
// Сторінка всіх відгуків магазину (окремий роут information/reviews).
class ControllerInformationReviews extends Controller {
	public function index() {
		$this->load->language('information/reviews');

		// h1 лишається коротким («Відгуки»), а в <title> — розгорнутий з брендом
		$this->document->setTitle($this->language->get('text_meta_title'));
		$this->document->setDescription($this->language->get('text_meta_description'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/reviews')
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_reviews_count'] = $this->language->get('text_reviews_count');
		$data['text_write_review'] = $this->language->get('text_write_review');
		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_product'] = $this->language->get('text_product');

		$language_id = (int)$this->config->get('config_language_id');

		$limit = 12;
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

		if ($page < 1) {
			$page = 1;
		}

		$total_query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) WHERE r.status = '1' AND p.status = '1'");
		$total = (int)$total_query->row['total'];

		$pages = max(1, (int)ceil($total / $limit));

		if ($page > $pages) {
			$page = $pages;
		}

		$start = ($page - 1) * $limit;

		$query = $this->db->query("SELECT r.review_id, r.product_id, r.author, r.text, r.rating, r.date_added, r.source_url, pd.name AS product_name FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product_description pd ON (r.product_id = pd.product_id AND pd.language_id = '" . $language_id . "') LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) WHERE r.status = '1' AND p.status = '1' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		$data['reviews'] = array();
		$rating_sum = 0;

		foreach ($query->rows as $row) {
			$rating_sum += (int)$row['rating'];

			$data['reviews'][] = array(
				'author'       => $row['author'],
				'text'         => nl2br($row['text']),
				'rating'       => (int)$row['rating'],
				'product_name' => $row['product_name'],
				'product_href' => $this->url->link('product/product', 'product_id=' . (int)$row['product_id']),
				'date_added'   => date($this->language->get('date_format_short'), strtotime($row['date_added'])),
				'source_url'   => $row['source_url']
			);
		}

		$avg_query = $this->db->query("SELECT AVG(r.rating) AS avg_rating FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) WHERE r.status = '1' AND p.status = '1'");

		$data['rating_avg'] = $total ? round((float)$avg_query->row['avg_rating'], 1) : 0;
		$data['rating_avg_int'] = (int)round($data['rating_avg']);
		$data['reviews_total'] = $total;

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('information/reviews', 'page={page}');

		$data['pagination'] = $pagination->render();

		// SEO пагінації: canonical на поточну сторінку + rel prev/next
		$this->document->addLink($this->url->link('information/reviews', $page > 1 ? 'page=' . $page : ''), 'canonical');

		if ($page > 1) {
			$this->document->addLink($this->url->link('information/reviews', $page > 2 ? 'page=' . ($page - 1) : ''), 'prev');
		}

		if ($limit && ceil($total / $limit) > $page) {
			$this->document->addLink($this->url->link('information/reviews', 'page=' . ($page + 1)), 'next');
		}
		$data['results'] = sprintf($this->language->get('text_pagination'), $total ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit), $total, $pages);

		// ── Структуровані дані: відгуки сторінки, кожен привʼязаний до свого товару ──
		$review_items = array();

		foreach ($data['reviews'] as $index => $review) {
			$review_items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'item'     => array(
					'@type'         => 'Review',
					'author'        => array('@type' => 'Person', 'name' => $review['author']),
					'reviewBody'    => trim(strip_tags(html_entity_decode($review['text'], ENT_QUOTES, 'UTF-8'))),
					'reviewRating'  => array('@type' => 'Rating', 'ratingValue' => $review['rating'], 'bestRating' => 5, 'worstRating' => 1),
					'itemReviewed'  => array(
						'@type' => 'Product',
						'name'  => $review['product_name'],
						'url'   => $review['product_href'],
						'brand' => array('@type' => 'Brand', 'name' => 'Hydrophob')
					)
				)
			);
		}

		$data['schema_blocks'] = array();

		if ($review_items) {
			$data['schema_blocks'][] = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'CollectionPage',
				'name'       => $data['heading_title'],
				'url'        => $this->url->link('information/reviews'),
				'inLanguage' => $this->config->get('config_language') == 'ru-ru' ? 'ru-UA' : 'uk-UA',
				'mainEntity' => array(
					'@type'           => 'ItemList',
					'numberOfItems'   => $total,
					'itemListElement' => $review_items
				)
			);
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/reviews', $data));
	}
}
