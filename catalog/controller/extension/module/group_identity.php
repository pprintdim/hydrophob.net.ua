<?php
/**
 * Спільна база клієнтів групи магазинів: бік магазину.
 *
 * - export   — CRM тягне звідси клієнтів, адреси й замовлення сторінками
 *              (звірковий прохід, щоб нічого не загубилось між подіями);
 * - orders   — блок «Замовлення в інших магазинах групи» для кабінету;
 * - consent  — перемикач згоди в кабінеті (відкликати / дати знову).
 */
class ControllerExtensionModuleGroupIdentity extends Controller {
	public function export() {
		$this->load->library('group');

		$token = '';

		if (isset($this->request->server['HTTP_AUTHORIZATION']) && stripos($this->request->server['HTTP_AUTHORIZATION'], 'Bearer ') === 0) {
			$token = trim(substr($this->request->server['HTTP_AUTHORIZATION'], 7));
		} elseif (isset($this->request->get['token'])) {
			$token = (string)$this->request->get['token'];
		}

		if (!$this->group->enabled() || !$this->group->verifyIncomingToken($token)) {
			return $this->json(array('success' => false, 'data' => array(), 'meta' => array(), 'errors' => array(array('code' => 'unauthorized', 'message' => 'Bad token'))), 401);
		}

		$type = isset($this->request->get['type']) ? (string)$this->request->get['type'] : 'customers';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;
		$limit = isset($this->request->get['limit']) ? max(1, min(500, (int)$this->request->get['limit'])) : 200;
		$since = (isset($this->request->get['since']) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $this->request->get['since'])) ? $this->request->get['since'] : '';
		$start = ($page - 1) * $limit;

		$rows = array();

		if ($type == 'customers') {
			$sql = "SELECT customer_id, firstname, lastname, email, telephone, date_added FROM `" . DB_PREFIX . "customer` WHERE status = '1'";
			if ($since) {
				$sql .= " AND date_added >= '" . $this->db->escape($since) . "'";
			}
			$query = $this->db->query($sql . " ORDER BY customer_id ASC LIMIT " . (int)$start . ", " . (int)($limit + 1));

			foreach ($query->rows as $row) {
				$rows[] = array(
					'customer_id'   => (int)$row['customer_id'],
					'email'         => $row['email'],
					'telephone'     => $row['telephone'],
					'firstname'     => $row['firstname'],
					'lastname'      => $row['lastname'],
					'registered_at' => $row['date_added']
				);
			}
		} elseif ($type == 'addresses') {
			$query = $this->db->query("SELECT a.*, (c.address_id = a.address_id) AS is_default FROM `" . DB_PREFIX . "address` a LEFT JOIN `" . DB_PREFIX . "customer` c ON (c.customer_id = a.customer_id) WHERE c.customer_id IS NOT NULL ORDER BY a.address_id ASC LIMIT " . (int)$start . ", " . (int)($limit + 1));

			foreach ($query->rows as $row) {
				$rows[] = array(
					'address_id'   => (int)$row['address_id'],
					'customer_id'  => (int)$row['customer_id'],
					'firstname'    => $row['firstname'],
					'lastname'     => $row['lastname'],
					'phone'        => null,
					'city'         => $row['city'],
					'carrier'      => $this->carrierOf($row['address_1']),
					'address_line' => trim($row['address_1'] . ' ' . $row['address_2']),
					'postcode'     => $row['postcode'],
					'is_default'   => (bool)$row['is_default']
				);
			}
		} elseif ($type == 'orders') {
			$sql = "SELECT o.order_id, o.customer_id, o.firstname, o.lastname, o.email, o.telephone, o.total, o.currency_code, o.currency_value, o.order_status_id, o.date_added, os.name AS status_name, (SELECT COALESCE(SUM(op.quantity), 0) FROM `" . DB_PREFIX . "order_product` op WHERE op.order_id = o.order_id) AS items FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "order_status` os ON (os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE o.order_status_id > '0'";
			if ($since) {
				$sql .= " AND o.date_modified >= '" . $this->db->escape($since) . "'";
			}
			$query = $this->db->query($sql . " ORDER BY o.order_id ASC LIMIT " . (int)$start . ", " . (int)($limit + 1));

			foreach ($query->rows as $row) {
				$total = (float)$row['total'];
				$currency = $row['currency_code'];

				// у базі total у валюті магазину; замовлення в іншій валюті
				// перераховуємо за курсом, зафіксованим у замовленні
				if ($currency != $this->config->get('config_currency') && (float)$row['currency_value'] > 0) {
					$total = $total * (float)$row['currency_value'];
				} else {
					$currency = $this->config->get('config_currency');
				}

				$rows[] = array(
					'order_id'        => (int)$row['order_id'],
					'customer_id'     => (int)$row['customer_id'],
					'email'           => $row['email'],
					'telephone'       => $row['telephone'],
					'firstname'       => $row['firstname'],
					'lastname'        => $row['lastname'],
					'date_added'      => $row['date_added'],
					'total'           => round($total, 2),
					'currency'        => $currency ?: 'UAH',
					'order_status_id' => (int)$row['order_status_id'],
					'status_name'     => (string)$row['status_name'],
					'items_count'     => (int)$row['items'],
					'order_url'       => $this->url->link('account/order/info', 'order_id=' . (int)$row['order_id'], true)
				);
			}
		} else {
			return $this->json(array('success' => false, 'data' => array(), 'meta' => array(), 'errors' => array(array('code' => 'bad_type', 'message' => 'Unknown type'))), 422);
		}

		$has_more = count($rows) > $limit;

		if ($has_more) {
			array_pop($rows);
		}

		return $this->json(array('success' => true, 'data' => $rows, 'meta' => array('page' => $page, 'limit' => $limit, 'has_more' => $has_more), 'errors' => array()));
	}

	/** Блок для сторінки «Мої замовлення»: плашки з інших магазинів групи. */
	public function orders() {
		if (!$this->customer->isLogged()) {
			return '';
		}

		$this->load->library('group');

		if (!$this->group->enabled()) {
			return '';
		}

		$this->load->language('extension/module/group_identity');

		$data['orders'] = array();

		foreach ($this->group->orders($this->customer->getId()) as $order) {
			$data['orders'][] = array(
				'site_name'   => $order['site_name'],
				'site_domain' => $order['site_domain'],
				'badge_color' => !empty($order['badge_color']) && preg_match('/^#[0-9a-f]{3,8}$/i', $order['badge_color']) ? $order['badge_color'] : '',
				'order_id'    => (int)$order['order_id'],
				'date_added'  => $order['date_added'] ? date($this->language->get('date_format_short'), strtotime($order['date_added'])) : '',
				'total'       => $this->currency->format((float)$order['total'], $order['currency'] ?: 'UAH', 1),
				'status'      => $order['status_name'],
				'items'       => (int)$order['items_count'],
				'href'        => $order['order_url']
			);
		}

		if (!$data['orders']) {
			return '';
		}

		return $this->load->view('extension/module/group_orders', $data);
	}

	/** Перемикач згоди на єдиний профіль групи (кабінет). */
	public function consent() {
		$json = array();

		if (!$this->customer->isLogged()) {
			$json['error'] = 'login';
		} else {
			$this->load->library('group');

			$enable = !empty($this->request->post['consent']);

			if ($enable) {
				$this->group->link($this->customer->getId(), array(
					'email'     => $this->customer->getEmail(),
					'telephone' => $this->customer->getTelephone(),
					'firstname' => $this->customer->getFirstName(),
					'lastname'  => $this->customer->getLastName()
				), true);
			} else {
				$this->group->unlink($this->customer->getId());
			}

			$json['success'] = true;
			$json['consent'] = $enable;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function carrierOf($address) {
		$text = mb_strtolower((string)$address);

		if (strpos($text, 'нова') !== false || strpos($text, 'нової') !== false || strpos($text, 'nova') !== false) {
			return 'novaposhta';
		}

		if (strpos($text, 'укрпошт') !== false || strpos($text, 'ukrposhta') !== false) {
			return 'ukrposhta';
		}

		if (strpos($text, 'meest') !== false || strpos($text, 'міст') !== false) {
			return 'meest';
		}

		return null;
	}

	private function json(array $payload, $status = 200) {
		if ($status != 200) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' ' . $status . ' ' . ($status == 401 ? 'Unauthorized' : 'Unprocessable Entity'));
		}

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->addHeader('X-Robots-Tag: noindex');
		$this->response->setOutput(json_encode($payload, JSON_UNESCAPED_UNICODE));
	}
}
