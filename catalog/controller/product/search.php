<?php
class ControllerProductSearch extends Controller {
	public function index() {
		// index.php?route=product/search і ?search=… — 301 на ЧПУ /poshuk/<запит>
		if ($this->config->get('config_seo_url')) {
			$query_string = isset($this->request->server['QUERY_STRING']) ? $this->request->server['QUERY_STRING'] : '';

			$has_route = !isset($this->request->get['_route_']) && isset($this->request->get['route']);
			$has_search_param = strpos($query_string, 'search=') !== false;

			if ($has_route || $has_search_param) {
				$args = $this->request->get;
				unset($args['route'], $args['_route_']);

				$this->response->redirect($this->url->link('product/search', http_build_query($args)), 301);
			}
		}

		// порожній запит = сторінка пошуку без результатів, а не весь каталог
		if (isset($this->request->get['search']) && trim($this->request->get['search']) === '') {
			unset($this->request->get['search']);
		}

		if (isset($this->request->get['filter'])) {
			$filter = is_array($this->request->get['filter'])
				? implode(',', array_filter(array_map('intval', $this->request->get['filter'])))
				: $this->request->get['filter'];

			$this->request->get['filter'] = $filter;
		} else {
			$filter = '';
		}

		$this->load->language('product/search');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['tag'])) {
			$tag = $this->request->get['tag'];
		} elseif (isset($this->request->get['search'])) {
			$tag = $this->request->get['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->get['description'])) {
			$description = $this->request->get['description'];
		} else {
			$description = '';
		}

		if (isset($this->request->get['category_id'])) {
			$category_id = $this->request->get['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->get['sub_category'])) {
			$sub_category = $this->request->get['sub_category'];
		} else {
			$sub_category = '';
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

		if (isset($this->request->get['search'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->request->get['search']);
		} elseif (isset($this->request->get['tag'])) {
			$this->document->setTitle($this->language->get('heading_title') .  ' - ' . $this->language->get('heading_tag') . $this->request->get['tag']);
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$url = '';

		if (isset($this->request->get['search'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['tag'])) {
			$url .= '&tag=' . urlencode(html_entity_decode(trim($this->request->get['tag']), ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['description'])) {
			$url .= '&description=' . $this->request->get['description'];
		}

		if (isset($this->request->get['category_id'])) {
			$url .= '&category_id=' . $this->request->get['category_id'];
		}

		if (isset($this->request->get['sub_category'])) {
			$url .= '&sub_category=' . $this->request->get['sub_category'];
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/search', $url)
		);

		if (isset($this->request->get['search'])) {
			$data['heading_title'] = $this->language->get('heading_title') .  ' - ' . $this->request->get['search'];
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}

		$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$data['compare'] = $this->url->link('product/compare');

		// 3 Level Category Search
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
						'category_id' => $category_3['category_id'],
						'name'        => $category_3['name'],
					);
				}

				$level_2_data[] = array(
					'category_id' => $category_2['category_id'],
					'name'        => $category_2['name'],
					'children'    => $level_3_data
				);
			}

			$data['categories'][] = array(
				'category_id' => $category_1['category_id'],
				'name'        => $category_1['name'],
				'children'    => $level_2_data
			);
		}

		$data['products'] = array();

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_filter'       => $filter,
				'filter_tag'          => $tag,
				'filter_description'  => $description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

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

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/search', 'sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/search', 'sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/search', 'sort=p.model&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
					'href'  => $this->url->link('product/search', $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . urlencode(html_entity_decode($this->request->get['tag'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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

			// тексти тулбара/сайдбара — ті самі, що на сторінці каталогу
			$this->load->language('product/category');

			foreach (array('text_sections', 'text_popular', 'text_apply', 'text_reset',
				'text_filters', 'text_close', 'text_grid4', 'text_grid2') as $cat_key) {
				$data[$cat_key] = $this->language->get($cat_key);
			}

			// фільтри (глобальні лічильники, як на /katalog)
			$this->load->model('catalog/filter');

			$data['filter_groups'] = array();
			$filter_selected = $filter ? explode(',', $filter) : array();
			$filter_counts = $this->model_catalog_product->getFilterCounts();

			$all_groups = $this->db->query("SELECT filter_group_id FROM " . DB_PREFIX . "filter_group ORDER BY sort_order");

			foreach ($all_groups->rows as $group_row) {
				$filter_group_info = $this->model_catalog_filter->getFilterGroup($group_row['filter_group_id']);

				if (!$filter_group_info) {
					continue;
				}

				$filter_group_data = array();

				foreach ($this->model_catalog_filter->getFiltersByGroupId($group_row['filter_group_id']) as $filter_info) {
					$filter_id = (int)$filter_info['filter_id'];
					$filter_total = isset($filter_counts[$filter_id]) ? $filter_counts[$filter_id] : 0;
					$filter_checked = in_array($filter_id, $filter_selected);

					if (!$filter_total && !$filter_checked) {
						continue;
					}

					$filter_group_data[] = array(
						'filter_id' => $filter_id,
						'name'      => $filter_info['name'],
						'total'     => $filter_total,
						'selected'  => $filter_checked
					);
				}

				if ($filter_group_data) {
					$data['filter_groups'][] = array(
						'name'   => $filter_group_info['name'],
						'filter' => $filter_group_data
					);
				}
			}

			$data['filter_action'] = $this->url->link('product/search', 'search=' . urlencode($search));
			// ЧПУ-база для фільтрів: /poshuk/<запит>, далі приклеюються слаги
			$data['filter_seo_base'] = html_entity_decode($this->url->link('product/search', 'search=' . urlencode($search)), ENT_QUOTES, 'UTF-8');
			$data['filter_selected_count'] = count($filter_selected);

			// популярні товари в сайдбар
			$data['popular'] = array();

			foreach ($this->model_catalog_product->getBestSellerProducts(5) as $popular_product) {
				$data['popular'][] = array(
					'name'  => $popular_product['name'],
					'thumb' => $this->model_tool_image->resize($popular_product['image'] ? $popular_product['image'] : 'placeholder.png', 74, 74),
					'price' => $this->currency->format($this->tax->calculate($popular_product['price'], $popular_product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					'href'  => $this->url->link('product/product', 'product_id=' . $popular_product['product_id'])
				);
			}

			// сайдбар: кореневі категорії з товарами (як на сторінці каталогу)
			$this->load->model('catalog/category');

			$data['category_options'] = array();

			foreach ($this->model_catalog_category->getCategories(0) as $sidebar_category) {
				$sidebar_total = $this->model_catalog_product->getTotalProducts(array(
					'filter_category_id'  => $sidebar_category['category_id'],
					'filter_sub_category' => true
				));

				if ($sidebar_total) {
					$data['category_options'][] = array(
						'name' => $sidebar_category['name'],
						'href' => $this->url->link('product/category', 'path=' . $sidebar_category['category_id'])
					);
				}
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/search', $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			// SEO пагінації пошуку
			$canonical_url = 'search=' . urlencode($search) . ($page > 1 ? '&page=' . $page : '');
			$this->document->addLink($this->url->link('product/search', $canonical_url), 'canonical');

			if ($page > 1) {
				$this->document->addLink($this->url->link('product/search', 'search=' . urlencode($search) . ($page > 2 ? '&page=' . ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
				$this->document->addLink($this->url->link('product/search', 'search=' . urlencode($search) . '&page=' . ($page + 1)), 'next');
			}

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			if (isset($this->request->get['search']) && $this->config->get('config_customer_search')) {
				$this->load->model('account/search');

				if ($this->customer->isLogged()) {
					$customer_id = $this->customer->getId();
				} else {
					$customer_id = 0;
				}

				if (isset($this->request->server['REMOTE_ADDR'])) {
					$ip = $this->request->server['REMOTE_ADDR'];
				} else {
					$ip = '';
				}

				$search_data = array(
					'keyword'       => $search,
					'category_id'   => $category_id,
					'sub_category'  => $sub_category,
					'description'   => $description,
					'products'      => $product_total,
					'customer_id'   => $customer_id,
					'ip'            => $ip
				);

				$this->model_account_search->addSearch($search_data);
			}
		}

		$data['search'] = $search;
		$data['description'] = $description;
		$data['category_id'] = $category_id;
		$data['sub_category'] = $sub_category;

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		// ── Структуровані дані: сторінка результатів пошуку зі списком знахідок ──
		$items = array();

		foreach ($data['products'] as $index => $product) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'url'      => $product['href'],
				'name'     => $product['name']
			);
		}

		$search_page = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'SearchResultsPage',
			'name'       => $data['heading_title'],
			'url'        => $this->url->link('product/search', $search !== '' ? 'search=' . urlencode($search) : ''),
			'inLanguage' => $this->config->get('config_language') == 'ru-ru' ? 'ru-UA' : 'uk-UA'
		);

		if ($items) {
			$search_page['mainEntity'] = array(
				'@type'           => 'ItemList',
				'numberOfItems'   => count($items),
				'itemListElement' => $items
			);
		}

		$data['schema_blocks'] = array($search_page);

		// GA4: сам запит окремою подією + список знахідок
		$data['ga_search'] = $search !== '' ? array('search_term' => $search) : array();

		$data['ga_list'] = $data['products'] ? array(
			'item_list_id'   => 'search',
			'item_list_name' => $data['heading_title'],
			'items'          => array_map(function ($index, $product) use ($data) {
				return array(
					'item_id'        => (int)$product['product_id'],
					'item_name'      => $product['name'],
					'item_brand'     => 'Hydrophob',
					'item_list_name' => $data['heading_title'],
					'index'          => $index + 1
				);
			}, array_keys($data['products']), $data['products'])
		) : array();

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		// форма пошуку в сайдбарі шле на ЧПУ, а не на index.php?route=…
		$data['search_action'] = html_entity_decode($this->url->link('product/search'), ENT_QUOTES, 'UTF-8');

		$this->response->setOutput($this->load->view('product/search', $data));
	}
}
