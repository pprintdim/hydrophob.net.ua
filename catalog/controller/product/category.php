<?php
class ControllerProductCategory extends Controller {
	public function index() {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['filter'])) {
			$filter = is_array($this->request->get['filter'])
				? implode(',', array_filter(array_map('intval', $this->request->get['filter'])))
				: $this->request->get['filter'];

			$this->request->get['filter'] = $filter;
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit']) && (int)$this->request->get['limit'] > 0) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = $category_info['name'];

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			if ($category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['compare'] = $this->url->link('product/compare');

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			// «Розділи каталогу»: інші кореневі категорії, у яких є товари.
			// Поточну й порожні не показуємо — вони нікуди не ведуть.
			$data['category_options'] = array();

			foreach ($this->model_catalog_category->getCategories(0) as $category) {
				if ((int)$category['category_id'] === (int)$category_id) {
					continue;
				}

				$total = $this->model_catalog_product->getTotalProducts(array(
					'filter_category_id'  => $category['category_id'],
					'filter_sub_category' => true
				));

				if (!$total) {
					continue;
				}

				$data['category_options'][] = array(
					'name' => $category['name'],
					'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}

			$data['categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				);
			}

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			// батьківська категорія без власних товарів показує товари підкатегорій,
			// а коренева «вітрина» без підкатегорій — усю продукцію магазину
			if (!$product_total) {
				$filter_data['filter_sub_category'] = true;
				$product_total = $this->model_catalog_product->getTotalProducts($filter_data);
			}

			if (!$product_total) {
				unset($filter_data['filter_category_id'], $filter_data['filter_sub_category']);
				$product_total = $this->model_catalog_product->getTotalProducts($filter_data);
			}

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($result['special']) && (float)$result['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$tax_price = (float)$result['special'];
				} else {
					$special = false;
					$tax_price = (float)$result['price'];
				}
	
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format($tax_price, $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			// Рекламні банери в сітці (після кожного 8-го товару)
			$data['banners'] = array(
				array(
					'title' => $this->language->get('text_banner1_title'),
					'text'  => $this->language->get('text_banner1_text'),
					'btn'   => $this->language->get('text_banner1_btn'),
					'img'   => 'image/catalog/hydrophob/p2523929316.webp',
					'href'  => $this->url->link('product/product', 'product_id=70')
				),
				array(
					'title' => $this->language->get('text_banner2_title'),
					'text'  => $this->language->get('text_banner2_text'),
					'btn'   => $this->language->get('text_banner2_btn'),
					'img'   => 'image/catalog/hydrophob/p2524531368.webp',
					'href'  => $this->url->link('information/contact')
				)
			);

			// Популярні товари в сайдбарі (бестселери магазину)
			$data['popular'] = array();
			$this->load->model('catalog/product');
			$popular_results = $this->model_catalog_product->getBestSellerProducts(6);

			foreach ($popular_results as $popular_item) {
				$popular_thumb = $popular_item['image']
					? $this->model_tool_image->resize($popular_item['image'], 200, 200)
					: $this->model_tool_image->resize('placeholder.png', 200, 200);

				if ((float)$popular_item['special']) {
					$popular_price = $this->currency->format($this->tax->calculate($popular_item['special'], $popular_item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$popular_price = $this->currency->format($this->tax->calculate($popular_item['price'], $popular_item['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				}

				$data['popular'][] = array(
					'name'  => $popular_item['name'],
					'thumb' => $popular_thumb,
					'price' => $popular_price,
					'href'  => $this->url->link('product/product', 'product_id=' . $popular_item['product_id'])
				);
			}

			// Фільтри каталогу (групи + значення з активними позначками)
			$this->load->model('catalog/filter');

			$data['filter_groups'] = array();
			$filter_selected = $filter ? explode(',', $filter) : array();

			// лічильники товарів під поточною категорією (як на еталонному проєкті)
			$filter_counts = $this->model_catalog_product->getFilterCounts(array(
				'filter_category_ids' => array($category_id)
			));

			// модель віддає групи масивами (id + назва + значення), нам потрібні лише id
			$filter_group_ids = array();

			foreach ($this->model_catalog_category->getCategoryFilters($category_id) as $group) {
				$filter_group_ids[] = is_array($group) ? (int)$group['filter_group_id'] : (int)$group;
			}

			$filter_group_ids = array_unique($filter_group_ids);

			// категорія без власних привʼязок показує всі групи (плаский каталог)
			if (!$filter_group_ids) {
				$all_groups = $this->db->query("SELECT filter_group_id FROM " . DB_PREFIX . "filter_group ORDER BY sort_order");
				$filter_group_ids = array();

				foreach ($all_groups->rows as $group_row) {
					$filter_group_ids[] = $group_row['filter_group_id'];
				}

				$filter_counts = $this->model_catalog_product->getFilterCounts();
			}

			foreach ($filter_group_ids as $filter_group_id) {
				$filter_group_info = $this->model_catalog_filter->getFilterGroup($filter_group_id);

				if (!$filter_group_info) {
					continue;
				}

				$filter_group_data = array();

				foreach ($this->model_catalog_filter->getFiltersByGroupId($filter_group_id) as $filter_info) {
					$filter_id = (int)$filter_info['filter_id'];
					$filter_total = isset($filter_counts[$filter_id]) ? $filter_counts[$filter_id] : 0;
					$filter_checked = in_array($filter_id, $filter_selected);

					// порожнє значення ховаємо, якщо воно не вибране самим покупцем
					if (!$filter_total && !$filter_checked) {
						continue;
					}

					// слаг фільтра — щоб фронт зібрав ЧПУ /katalog/<слаг>/<слаг>
					$slug_query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = 'filter_id=" . $filter_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					$filter_group_data[] = array(
						'filter_id' => $filter_id,
						'name'      => $filter_info['name'],
						'total'     => $filter_total,
						'selected'  => $filter_checked,
						'slug'      => $slug_query->num_rows ? $slug_query->row['keyword'] : ''
					);
				}

				if ($filter_group_data) {
					$data['filter_groups'][] = array(
						'filter_group_id' => $filter_group_id,
						'name'            => $filter_group_info['name'],
						'filter'          => $filter_group_data
					);
				}
			}

			$data['filter_action'] = $this->url->link('product/category', 'path=' . $this->request->get['path']);
			$data['filter_seo_base'] = html_entity_decode($this->url->link('product/category', 'path=' . $this->request->get['path']), ENT_QUOTES, 'UTF-8');
			$data['filter_selected_count'] = count($filter_selected);
			$data['path_value'] = $this->request->get['path'];
			$data['text_apply'] = $this->language->get('text_apply');
			$data['text_reset'] = $this->language->get('text_reset');

			$data['text_popular'] = $this->language->get('text_popular');
			$data['text_close'] = $this->language->get('text_close');
			$data['text_grid4'] = $this->language->get('text_grid4');
			$data['text_grid2'] = $this->language->get('text_grid2');
			$data['text_about_category'] = $this->language->get('text_about_category');
			$data['text_more'] = $this->language->get('text_more');
			$data['text_less'] = $this->language->get('text_less');

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. $page), 'canonical');
			}
			
			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
			}

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			// ── Структуровані дані: розділ каталогу як список товарів ──
			$items = array();

			foreach ($data['products'] as $index => $product) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $index + 1,
					'url'      => $product['href'],
					'name'     => $product['name']
				);
			}

			$collection = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'CollectionPage',
				'name'        => $data['heading_title'],
				'url'         => $this->url->link('product/category', 'path=' . $category_info['category_id']),
				'inLanguage'  => $this->config->get('config_language') == 'ru-ru' ? 'ru-UA' : 'uk-UA'
			);

			if (!empty($data['description'])) {
				$collection['description'] = utf8_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($data['description']))), 0, 900);
			}

			if ($items) {
				$collection['mainEntity'] = array(
					'@type'           => 'ItemList',
					'numberOfItems'   => count($items),
					'itemListElement' => $items
				);
			}

			$data['schema_blocks'] = array($collection);

			// GA4 view_item_list: назви та позиції товарів розділу
			$data['ga_list'] = $data['products'] ? array(
				'item_list_id'   => 'category_' . (int)$category_info['category_id'],
				'item_list_name' => $data['heading_title'],
				'items'          => array_map(function ($index, $product) use ($data) {
					return array(
						'item_id'        => (int)$product['product_id'],
						'item_name'      => $product['name'],
						'item_brand'     => 'Hydrophob',
						'item_category'  => $data['heading_title'],
						'item_list_name' => $data['heading_title'],
						'index'          => $index + 1
					);
				}, array_keys($data['products']), $data['products'])
			) : array();

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
