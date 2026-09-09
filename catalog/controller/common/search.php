<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}

		// живий пошук: ендпоінт + підписи груп дропдауна
		$data['search_action']    = $this->url->link('product/search', '', true);
		$data['ajax_search_url']  = $this->url->link('common/ajax_search/search', '', true);

		foreach (array('text_ss_categories', 'text_ss_brands', 'text_ss_products', 'text_ss_empty', 'text_ss_all') as $key) {
			$data[$key] = $this->language->get($key);
		}

		return $this->load->view('common/search', $data);
	}
}