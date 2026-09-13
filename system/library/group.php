<?php
/**
 * Спільна база клієнтів групи магазинів (stores.crm).
 *
 * Магазин працює як звичайний окремий магазин; цей клас лише доповнює його:
 * впізнає покупця з іншого магазину групи, підтягує його дані й адреси,
 * показує плашки замовлень з інших магазинів і штовхає туди свої.
 *
 * Усі виклики «мʼякі»: CRM недоступна — метод мовчки повертає порожнє,
 * магазин працює далі. Нічого з цього не має ламати оформлення замовлення.
 *
 * Налаштування — константи в config.php магазину:
 *   GROUP_API_URL      https://46.224.100.254:8442/crm/api/group
 *   GROUP_API_TOKEN    токен магазину, виданий у CRM («Клієнти групи → Магазини»)
 *   GROUP_API_INSECURE true, якщо CRM на самопідписаному сертифікаті (IP без домену)
 */
class Group {
	private $registry;
	private $url;
	private $token;
	private $insecure;
	private $timeout = 4;

	public function __construct($registry) {
		$this->registry = $registry;
		$this->url = defined('GROUP_API_URL') ? rtrim((string)GROUP_API_URL, '/') : '';
		$this->token = defined('GROUP_API_TOKEN') ? (string)GROUP_API_TOKEN : '';
		$this->insecure = defined('GROUP_API_INSECURE') && GROUP_API_INSECURE;
	}

	public function enabled() {
		return $this->url !== '' && $this->token !== '' && function_exists('curl_init');
	}

	/** Чи знає група цю людину. Повертає профіль або false. */
	public function resolve($email, $phone = '') {
		$result = $this->request('POST', '/resolve', array('email' => (string)$email, 'phone' => (string)$phone));

		return (is_array($result) && !empty($result['data'])) ? $result['data'] : false;
	}

	/**
	 * Привʼязати локального покупця до спільного профілю (після реєстрації
	 * чи входу). Повертає профіль (імʼя, телефон, адреси з усіх магазинів).
	 */
	public function link($customer_id, array $customer, $consent = true) {
		$result = $this->request('POST', '/link', array(
			'customer_id'   => (int)$customer_id,
			'email'         => isset($customer['email']) ? (string)$customer['email'] : '',
			'telephone'     => isset($customer['telephone']) ? (string)$customer['telephone'] : '',
			'firstname'     => isset($customer['firstname']) ? (string)$customer['firstname'] : '',
			'lastname'      => isset($customer['lastname']) ? (string)$customer['lastname'] : '',
			'registered_at' => isset($customer['date_added']) ? (string)$customer['date_added'] : '',
			'last_login_at' => date('Y-m-d H:i:s'),
			'consent'       => (bool)$consent
		));

		$this->forget($customer_id);

		return (is_array($result) && !empty($result['data'])) ? $result['data'] : false;
	}

	public function unlink($customer_id) {
		$this->forget($customer_id);

		return (bool)$this->request('POST', '/unlink', array('customer_id' => (int)$customer_id));
	}

	/** Плашки замовлень з інших магазинів групи для цього покупця (кеш у сесії на 5 хв). */
	public function orders($customer_id) {
		return $this->cached('orders', $customer_id, function () use ($customer_id) {
			$result = $this->request('GET', '/orders?customer_id=' . (int)$customer_id);

			return (is_array($result) && isset($result['data']) && is_array($result['data'])) ? $result['data'] : array();
		});
	}

	public function addresses($customer_id) {
		return $this->cached('addresses', $customer_id, function () use ($customer_id) {
			$result = $this->request('GET', '/addresses?customer_id=' . (int)$customer_id);

			return (is_array($result) && isset($result['data']) && is_array($result['data'])) ? $result['data'] : array();
		});
	}

	/**
	 * Плашка замовлення в спільну базу: мінімум даних, без складу замовлення
	 * (його видно за посиланням у самому магазині).
	 */
	public function pushOrder(array $order, $order_status_id) {
		if (!$this->enabled() || empty($order['order_id'])) {
			return false;
		}

		$db = $this->registry->get('db');
		$config = $this->registry->get('config');
		$url = $this->registry->get('url');

		$status = '';
		$status_query = $db->query("SELECT name FROM `" . DB_PREFIX . "order_status` WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$config->get('config_language_id') . "'");

		if ($status_query->num_rows) {
			$status = $status_query->row['name'];
		}

		$items_query = $db->query("SELECT COALESCE(SUM(quantity), 0) AS items FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order['order_id'] . "'");

		$total = isset($order['total']) ? (float)$order['total'] : 0;
		$currency = isset($order['currency_code']) ? $order['currency_code'] : 'UAH';

		if (isset($order['currency_value']) && (float)$order['currency_value'] > 0 && $currency != $config->get('config_currency')) {
			$total = $total * (float)$order['currency_value'];
		} else {
			$currency = $config->get('config_currency') ?: 'UAH';
		}

		return (bool)$this->request('POST', '/orders', array(
			'order_id'        => (int)$order['order_id'],
			'customer_id'     => isset($order['customer_id']) ? (int)$order['customer_id'] : 0,
			'email'           => isset($order['email']) ? (string)$order['email'] : '',
			'telephone'       => isset($order['telephone']) ? (string)$order['telephone'] : '',
			'firstname'       => isset($order['firstname']) ? (string)$order['firstname'] : '',
			'lastname'        => isset($order['lastname']) ? (string)$order['lastname'] : '',
			'date_added'      => isset($order['date_added']) ? (string)$order['date_added'] : date('Y-m-d H:i:s'),
			'total'           => round($total, 2),
			'currency'        => $currency,
			'order_status_id' => (int)$order_status_id,
			'status_name'     => $status,
			'items_count'     => (int)$items_query->row['items'],
			'order_url'       => $url ? $url->link('account/order/info', 'order_id=' . (int)$order['order_id'], true) : ''
		));
	}

	/**
	 * Скопіювати адреси з інших магазинів у локальну адресну книгу, якщо
	 * своїх ще немає. Перша стає адресою за замовчуванням — далі чекаут
	 * підставляє її сам, як звичайну збережену адресу.
	 */
	public function importAddresses($customer_id, array $addresses) {
		$db = $this->registry->get('db');

		$own = $db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "address` WHERE customer_id = '" . (int)$customer_id . "'");

		if ((int)$own->row['total'] > 0 || !$addresses) {
			return 0;
		}

		$country_query = $db->query("SELECT country_id FROM `" . DB_PREFIX . "country` WHERE iso_code_2 = 'UA' LIMIT 1");
		$country_id = $country_query->num_rows ? (int)$country_query->row['country_id'] : 220;

		$added = 0;
		$seen = array();

		foreach ($addresses as $address) {
			if (!empty($address['own'])) {
				continue;
			}

			$city = isset($address['city']) ? trim((string)$address['city']) : '';
			$line = isset($address['address_line']) ? trim((string)$address['address_line']) : '';

			if ($city === '' && $line === '') {
				continue;
			}

			$key = mb_strtolower($city . '|' . $line);

			if (isset($seen[$key])) {
				continue;
			}

			$seen[$key] = true;

			$db->query("INSERT INTO `" . DB_PREFIX . "address` SET customer_id = '" . (int)$customer_id . "', firstname = '" . $db->escape(isset($address['firstname']) ? $address['firstname'] : '') . "', lastname = '" . $db->escape(isset($address['lastname']) ? $address['lastname'] : '') . "', company = '', address_1 = '" . $db->escape($line) . "', address_2 = '', postcode = '" . $db->escape(isset($address['postcode']) ? (string)$address['postcode'] : '') . "', city = '" . $db->escape($city) . "', zone_id = '0', country_id = '" . (int)$country_id . "', custom_field = ''");

			$address_id = $db->getLastId();

			if ($added === 0) {
				$db->query("UPDATE `" . DB_PREFIX . "customer` SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "' AND address_id = '0'");
			}

			$added++;

			if ($added >= 5) {
				break;
			}
		}

		return $added;
	}

	/** Перевірка токена для експорт-ендпоінта (CRM тягне дані сторінками). */
	public function verifyIncomingToken($token) {
		return $this->token !== '' && is_string($token) && hash_equals($this->token, $token);
	}

	private function cached($kind, $customer_id, $loader) {
		if (!$this->enabled() || (int)$customer_id < 1) {
			return array();
		}

		$session = $this->registry->get('session');
		$key = $kind . ':' . (int)$customer_id;

		if (isset($session->data['group_cache'][$key]) && $session->data['group_cache'][$key]['until'] > time()) {
			return $session->data['group_cache'][$key]['data'];
		}

		$data = $loader();

		$session->data['group_cache'][$key] = array('until' => time() + 300, 'data' => $data);

		return $data;
	}

	private function forget($customer_id) {
		$session = $this->registry->get('session');

		unset($session->data['group_cache']['orders:' . (int)$customer_id], $session->data['group_cache']['addresses:' . (int)$customer_id]);
	}

	private function request($method, $path, array $body = array()) {
		if (!$this->enabled()) {
			return false;
		}

		$ch = curl_init($this->url . $path);

		$headers = array(
			'Accept: application/json',
			'Authorization: Bearer ' . $this->token
		);

		if ($method === 'POST') {
			$headers[] = 'Content-Type: application/json';
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
		}

		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_SSL_VERIFYPEER => !$this->insecure,
			CURLOPT_SSL_VERIFYHOST => $this->insecure ? 0 : 2
		));

		$raw = curl_exec($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);

		curl_close($ch);

		if ($raw === false || $code < 200 || $code >= 300) {
			$log = $this->registry->get('log');

			if ($log) {
				$log->write('group: ' . $method . ' ' . $path . ' -> ' . ($raw === false ? $error : 'HTTP ' . $code));
			}

			return false;
		}

		$json = json_decode($raw, true);

		return is_array($json) ? $json : false;
	}
}
