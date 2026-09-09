<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
$this->load->model('catalog/product');
$this->load->model('catalog/category');

$categories = $this->model_catalog_product->getCategories($this->request->get['product_id']);

// Берём «главную» категорию (например, первую)
$main_category_name = '';
$main_category_id = 0;
if (!empty($categories)) {
    $main_category_id = $categories[0]['category_id'];
    $category_info = $this->model_catalog_category->getCategory($main_category_id);
    if ($category_info) {
        $main_category_name = $category_info['name'];
    }
}

$data['main_category_name'] = $main_category_name;
$data['main_category_id'] = $main_category_id;
		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

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
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

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

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
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
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		//check product page open from cateory page
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
						
			if(empty($this->model_catalog_product->checkProductCategory($product_id, $parts))) {
				$product_info = array();
			}
		}

		//check product page open from manufacturer page
		if (isset($this->request->get['manufacturer_id']) && !empty($product_info)) {
			if($product_info['manufacturer_id'] !=  $this->request->get['manufacturer_id']) {
				$product_info = array();
			}
		}

		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
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
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			// meta_title у картках — сама назва товару; для видачі дописуємо
			// назву магазину, якщо її там ще немає (бренд у сніпеті + унікальність)
			$meta_title = trim($product_info['meta_title']);
			$store_name = trim((string)$this->config->get('config_name'));

			if ($store_name && stripos($meta_title, $store_name) === false) {
				$meta_title .= ' — ' . $store_name;
			}

			$this->document->setTitle($meta_title);
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			$data['heading_title'] = $product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			// довгий опис показуємо згорнутим, щоб вкладка не з'їдала півсторінки
			$data['description_long'] = utf8_strlen(trim(strip_tags($data['description']))) > 600;

			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');

			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				$popup_extra = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
				$thumb_extra = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));

				// файл міг бути видалений — тоді resize віддає порожній рядок і слайд ламається
				if (!$popup_extra || !$thumb_extra) {
					continue;
				}

				$data['images'][] = array(
					'popup' => $popup_extra,
					'thumb' => $thumb_extra
				);
			}

			// без жодного придатного фото показуємо плейсхолдер, а не порожні <img>
			if (!$data['thumb']) {
				$data['thumb'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			}

			if (!$data['popup']) {
				$data['popup'] = $data['thumb'];
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			// стікери як на картках: ХІТ — товар у топі продажів
			$data['text_badge_top'] = $this->language->get('text_badge_top');
			$data['text_badge_sale'] = $this->language->get('text_badge_sale');
			$data['badge_top'] = false;

			foreach ($this->model_catalog_product->getBestSellerProducts(8) as $bestseller) {
				if ((int)$bestseller['product_id'] == (int)$this->request->get['product_id']) {
					$data['badge_top'] = true;

					break;
				}
			}

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$tax_price = (float)$product_info['special'];
			} else {
				$data['special'] = false;
				$tax_price = (float)$product_info['price'];
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);

			// Вкладки картки товару (Опис / Характеристики / Інструкція / Відгуки)
			$data['reviews_total'] = (int)$product_info['reviews'];

			$this->load->model('catalog/review');
			$review_rows = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], 0, 20);
			$data['reviews'] = array();
			foreach ($review_rows as $review_row) {
				$data['reviews'][] = array(
					'author'     => $review_row['author'],
					'text'       => nl2br($review_row['text']),
					'rating'     => (int)$review_row['rating'],
					'date_added' => date($this->language->get('date_format_short'), strtotime($review_row['date_added']))
				);
			}

			$data['category_title'] = '';
			if (isset($this->request->get['path'])) {
				$path_parts = explode('_', (string)$this->request->get['path']);
				$last_category_id = (int)array_pop($path_parts);
				$this->load->model('catalog/category');
				$cat_info = $this->model_catalog_category->getCategory($last_category_id);
				if ($cat_info) {
					$data['category_title'] = $cat_info['name'];
				}
			}

			foreach (array('tab_description', 'tab_specs', 'tab_instruction', 'tab_reviews',
				'text_category_label', 'text_brand_label', 'text_country_label', 'text_country_value',
				'text_how_to_use', 'text_step1', 'text_step2', 'text_step3', 'text_step4', 'text_step5',
				'text_storage', 'text_no_reviews', 'text_write_review',
				'button_quick', 'text_quick_title', 'text_quick_sub', 'entry_quick_name', 'entry_quick_phone',
				'entry_quick_email', 'entry_quick_comment', 'button_quick_send', 'text_quick_note',
				'text_quick_close', 'text_quick_in_cart', 'text_quick_qty', 'text_instock_label', 'text_outstock_label',
				'text_show_more', 'text_show_less', 'text_sku_label', 'text_spec_volume', 'text_spec_type', 'text_spec_area', 'text_saving', 'text_perk_delivery', 'text_perk_payment', 'text_perk_quality') as $tab_key) {
				$data[$tab_key] = $this->language->get($tab_key);
			}

			// Швидке замовлення + дані шапки картки (наявність, економія)
			$data['quick_action'] = $this->url->link('checkout/quick/confirm', '', true);

			// Головні характеристики в шапці картки: Обʼєм, Тип засобу, Область застосування.
			// Шукаємо їх серед атрибутів за назвою — так працює для всіх мов одразу.
			$data['key_specs'] = array();

			$wanted = array(
				$this->language->get('text_spec_volume'),
				$this->language->get('text_spec_type'),
				$this->language->get('text_spec_area')
			);

			$found = array();

			foreach ($this->model_catalog_product->getProductAttributes($this->request->get['product_id']) as $group) {
				foreach ($group['attribute'] as $attribute) {
					$key = array_search($attribute['name'], $wanted);

					if ($key !== false && trim($attribute['text']) !== '') {
						$found[$key] = array('name' => $attribute['name'], 'text' => trim($attribute['text']));
					}
				}
			}

			ksort($found);
			$data['key_specs'] = array_values($found);
			$data['in_stock']     = (int)$product_info['quantity'] > 0;

			$data['saving'] = false;

			if ($product_info['special']) {
				$diff = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))
					- $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));

				if ($diff > 0) {
					$data['saving'] = $this->currency->format($diff, $this->session->data['currency']);
				}
			}
			$data['rating'] = (int)$product_info['rating'];

			// ── Структуровані дані картки: сам товар + інструкція застосування ──
			$product_schema = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'Product',
				'name'        => $product_info['name'],
				'description' => utf8_substr(trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')))), 0, 900),
				'brand'       => array('@type' => 'Brand', 'name' => 'Hydrophob'),
				'url'         => $this->url->link('product/product', 'product_id=' . $product_id)
			);

			if (trim((string)$product_info['model']) !== '') {
				$product_schema['sku'] = $product_info['model'];
				$product_schema['mpn'] = $product_info['model'];
			}

			if (!empty($product_info['image'])) {
				$product_schema['image'] = $this->model_tool_image->resize($product_info['image'], 1200, 1200);
			}

			// характеристики картки (обʼєм, тип, область) — пошуковикам як властивості
			foreach ($data['key_specs'] as $spec) {
				$product_schema['additionalProperty'][] = array(
					'@type' => 'PropertyValue',
					'name'  => $spec['name'],
					'value' => $spec['text']
				);
			}

			if ((float)$product_info['price']) {
				$product_schema['offers'] = array(
					'@type'           => 'Offer',
					'url'             => $product_schema['url'],
					// ціна рахується в базовій валюті магазину — з нею й підписуємо,
					// інакше перемикач валют розсинхронив би число і код валюти
					'price'           => number_format((float)$this->tax->calculate(!is_null($product_info['special']) && (float)$product_info['special'] >= 0 ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), 2, '.', ''),
					'priceCurrency'   => $this->config->get('config_currency'),
					'availability'    => $product_info['quantity'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'itemCondition'   => 'https://schema.org/NewCondition',
					'seller'          => array('@type' => 'Organization', 'name' => html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')),
					// merchant listing проходить валідацію лише з обома політиками
					'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
					'hasMerchantReturnPolicy' => array(
						'@type'                => 'MerchantReturnPolicy',
						'applicableCountry'    => 'UA',
						'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
						'merchantReturnDays'   => 14,
						'returnMethod'         => 'https://schema.org/ReturnByMail',
						'returnFees'           => 'https://schema.org/ReturnShippingFees'
					),
					'shippingDetails' => array(
						'@type'               => 'OfferShippingDetails',
						'shippingDestination' => array('@type' => 'DefinedRegion', 'addressCountry' => 'UA'),
						'deliveryTime'        => array(
							'@type'        => 'ShippingDeliveryTime',
							'handlingTime' => array('@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'),
							'transitTime'  => array('@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 3, 'unitCode' => 'DAY')
						)
					)
				);
			}

			if ((int)$product_info['reviews'] > 0 && (float)$product_info['rating'] > 0) {
				$product_schema['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => (float)$product_info['rating'],
					'reviewCount' => (int)$product_info['reviews'],
					'bestRating'  => 5,
					'worstRating' => 1
				);

				foreach ($this->model_catalog_review->getReviewsByProductId($product_id, 0, 5) as $review) {
					$product_schema['review'][] = array(
						'@type'         => 'Review',
						'author'        => array('@type' => 'Person', 'name' => $review['author']),
						'datePublished' => date('c', strtotime($review['date_added'])),
						'reviewBody'    => trim(strip_tags(html_entity_decode($review['text'], ENT_QUOTES, 'UTF-8'))),
						'reviewRating'  => array('@type' => 'Rating', 'ratingValue' => (int)$review['rating'], 'bestRating' => 5, 'worstRating' => 1)
					);
				}
			}

			$data['schema_blocks'] = array($product_schema);

			// у соцмережах картка товару має показувати сам товар, а не банер сайту
			if (!empty($product_info['image'])) {
				$this->session->data['og_image'] = $this->model_tool_image->resize($product_info['image'], 1200, 630);
			}

			// GA4 view_item: той самий набір даних піде і в add_to_cart з кнопки.
			// Валюта — поточна валюта покупця, щоб збігалася з цінами на сторінці.
			$ga_currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
			$ga_price = round((float)$this->currency->convert(
				$this->tax->calculate(!is_null($product_info['special']) && (float)$product_info['special'] >= 0 ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')),
				$this->config->get('config_currency'),
				$ga_currency
			), 2);

			$data['ga_item'] = array(
				'currency' => $ga_currency,
				'value'    => $ga_price,
				'items'    => array(array(
					'item_id'       => (int)$product_id,
					'item_name'     => $product_info['name'],
					'item_brand'    => 'Hydrophob',
					'item_category' => isset($data['category_title']) ? $data['category_title'] : '',
					'price'         => $ga_price,
					'quantity'      => 1
				))
			);

			// інструкція застосування з вкладки — саме її цитують AI-відповіді
			$steps = array();

			foreach (array('text_step1', 'text_step2', 'text_step3', 'text_step4', 'text_step5') as $step_key) {
				$text = trim((string)$this->language->get($step_key));

				if ($text !== '') {
					$steps[] = array('@type' => 'HowToStep', 'position' => count($steps) + 1, 'text' => $text);
				}
			}

			if (count($steps) >= 2) {
				$data['schema_blocks'][] = array(
					'@context' => 'https://schema.org',
					'@type'    => 'HowTo',
					'name'     => $this->language->get('text_how_to_use') . ' ' . $product_info['name'],
					'step'     => $steps
				);
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			// привʼязані товари не заповнені — пропонуємо сусідів по категорії
			if (!$results && !empty($data['main_category_id'])) {
				$fallback = $this->model_catalog_product->getProducts(array(
					'filter_category_id'  => $data['main_category_id'],
					'filter_sub_category' => true,
					'sort'                => 'p.sort_order',
					'order'               => 'ASC',
					'start'               => 0,
					'limit'               => 9
				));

				foreach ($fallback as $fallback_product) {
					if ((int)$fallback_product['product_id'] == (int)$this->request->get['product_id']) {
						continue;
					}

					$results[] = $fallback_product;

					if (count($results) >= 8) {
						break;
					}
				}
			}

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
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
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . urlencode(html_entity_decode(trim($tag), ENT_QUOTES, 'UTF-8')))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
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
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
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

	// «Переглянуті нещодавно»: фронт зберігає id у localStorage і питає дані сюди
	public function viewedInfo() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$json = array('products' => array());

		$ids = isset($this->request->get['ids']) ? explode(',', $this->request->get['ids']) : array();
		$ids = array_slice(array_filter(array_map('intval', $ids)), 0, 12);

		foreach ($ids as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if (!$product_info) {
				continue;
			}

			$image = $this->model_tool_image->resize($product_info['image'] ? $product_info['image'] : 'placeholder.png', 450, 450);

			$price = $this->currency->format($this->tax->calculate($product_info['special'] ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

			$json['products'][] = array(
				'product_id' => (int)$product_info['product_id'],
				'name'       => $product_info['name'],
				'thumb'      => $image,
				'price'      => $price,
				'minimum'    => $product_info['minimum'] > 0 ? (int)$product_info['minimum'] : 1,
				'href'       => html_entity_decode($this->url->link('product/product', 'product_id=' . $product_info['product_id']), ENT_QUOTES, 'UTF-8')
			);
		}

		$json['button_cart'] = $this->language->get('button_cart');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
					$json['error'] = $this->language->get('error_name');
				}

				if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
					$json['error'] = $this->language->get('error_text');
				}
			
				if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
					$json['error'] = $this->language->get('error_rating');
				}

				// відгук лишає лише покупець із підтвердженою поштою: гість
				// проходить код і стає зареєстрованим (як у швидкому замовленні)
				if (!isset($json['error']) && !$this->customer->isLogged()) {
					$json['error'] = $this->language->get('error_review_verify');
				}

				// Captcha
				if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
					$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

					if ($captcha) {
						$json['error'] = $captcha;
					}
				}

				if (!isset($json['error'])) {
					$this->load->model('catalog/review');

					$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

					$json['success'] = $this->language->get('text_success');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_product');
		} 

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function updatePrice() {
    $this->load->language('product/product');
    $this->load->model('catalog/product');

    $product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
    $product_info = $this->model_catalog_product->getProduct($product_id);

    if (!$product_info) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['error' => 'Product not found']));
        return;
    }

    // Базовая цена (учитываем special если есть)
    $base_price = $product_info['price'];
    if ((float)$product_info['special']) {
        $base_price = $product_info['special'];
    }

    // Получим все опции продукта (они содержат product_option_value с price/price_prefix)
    $product_options = $this->model_catalog_product->getProductOptions($product_id);

    $option_price = 0.0;

    if (!empty($this->request->post['option'])) {
        foreach ($this->request->post['option'] as $product_option_id => $value) {
            $values = is_array($value) ? $value : array($value);

            // найдем соответствующий product_option
            foreach ($product_options as $product_option) {
                if ((int)$product_option['product_option_id'] === (int)$product_option_id) {
                    if (!empty($product_option['product_option_value'])) {
                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            foreach ($values as $v) {
                                if ((int)$product_option_value['product_option_value_id'] === (int)$v) {
                                    $price_val = (float)$product_option_value['price'];
                                    $prefix = isset($product_option_value['price_prefix']) ? $product_option_value['price_prefix'] : '+';
                                    if ($prefix === '+') {
                                        $option_price += $price_val;
                                    } elseif ($prefix === '-') {
                                        $option_price -= $price_val;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $quantity = isset($this->request->post['quantity']) ? (int)$this->request->post['quantity'] : 1;

    // Итоговая цена (без учёта скидочных наборов/опций типа price on demand)
    $price_with_options = $base_price + $option_price;
    $total = $price_with_options * max(1, $quantity);

    // Применяем налог и форматируем как OpenCart
    $formatted = $this->currency->format($this->tax->calculate($total, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode([
        'price' => $formatted,
        'raw' => $total
    ]));
}

}
