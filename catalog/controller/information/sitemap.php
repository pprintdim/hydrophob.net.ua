<?php
class ControllerInformationSitemap extends Controller {
	public function index() {
		$this->load->language('information/sitemap');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/sitemap')
		);

		$this->load->model('catalog/category');

		$data['categories'] = array();

		$categories_1 = $this->model_catalog_category->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();

			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();

				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'name' => $category_3['name'],
						'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $category_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'])
				);
			}

			$data['categories'][] = array(
				'name'     => $category_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'])
			);
		}

		// Кабінет, кошик і оформлення закриті в robots — у карті для людей
		// лишаємо тільки те, що реально індексується.
		$data['groups'] = array();

		$data['groups'][] = array(
			'title' => $this->language->get('text_shop'),
			'links' => array(
				array('name' => $this->language->get('text_special'), 'href' => $this->url->link('product/special')),
				array('name' => $this->language->get('text_search'), 'href' => $this->url->link('product/search'))
			)
		);

		$this->load->model('catalog/information');

		$info_links = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			$info_links[] = array(
				'name' => $result['title'],
				'href' => $this->url->link('information/information', 'information_id=' . $result['information_id'])
			);
		}

		$info_links[] = array('name' => $this->language->get('text_contact'), 'href' => $this->url->link('information/contact'));
		$info_links[] = array('name' => $this->language->get('text_faq'), 'href' => $this->url->link('information/faq'));
		$info_links[] = array('name' => $this->language->get('text_reviews'), 'href' => $this->url->link('information/reviews'));

		$data['groups'][] = array('title' => $this->language->get('text_information'), 'links' => $info_links);

		$data['text_catalog'] = $this->language->get('text_catalog');
		$data['heading_title'] = $this->language->get('heading_title');

		$data['schema_blocks'] = array(array(
			'@context'   => 'https://schema.org',
			'@type'      => 'WebPage',
			'name'       => $data['heading_title'],
			'url'        => $this->url->link('information/sitemap'),
			'inLanguage' => $this->config->get('config_language') == 'ru-ru' ? 'ru-UA' : 'uk-UA'
		));

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/sitemap', $data));
	}
}