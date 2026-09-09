<?php
// Email OTP flow (ported from the shokeru.in.ua pattern): sendCode issues a
// 6-digit code to the given email (register validates the form first, login
// requires an existing customer), verifyCode checks it and either creates the
// account (passwordless, random internal password) or logs the customer in
// via the override login. Codes live in the session with a TTL, resends are
// rate-limited, verification attempts are capped.
class ControllerCommonUserPopup extends Controller {
	public function sendCode() {
		$this->load->language('common/user_popup');

		$json = array();

		$allowed = array('login', 'register', 'email', 'checkout');
		$type = isset($this->request->post['type']) && in_array($this->request->post['type'], $allowed) ? $this->request->post['type'] : 'register';
		$email = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';

		if ((utf8_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_email');
		}

		$this->load->model('account/customer');

		$data = array();

		if (!$json && $type == 'register') {
			$firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
			$lastname = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
			$telephone = isset($this->request->post['telephone']) ? trim($this->request->post['telephone']) : '';

			if ((utf8_strlen($firstname) < 1) || (utf8_strlen($firstname) > 32)) {
				$json['error']['firstname'] = $this->language->get('error_firstname');
			}

			if ((utf8_strlen($telephone) < 3) || (utf8_strlen($telephone) > 32)) {
				$json['error']['telephone'] = $this->language->get('error_telephone');
			}

			if (!$json && $this->model_account_customer->getTotalCustomersByEmail($email)) {
				$json['error']['email'] = $this->language->get('error_exists');
			}

			$data = array(
				'firstname' => $firstname,
				'lastname'  => $lastname,
				'telephone' => $telephone
			);
		}

		if (!$json && $type == 'email') {
			if (!$this->customer->isLogged()) {
				$json['error']['warning'] = $this->language->get('error_login_required');
			} elseif ($email == $this->customer->getEmail()) {
				$json['error']['email'] = $this->language->get('error_email_same');
			} elseif ($this->model_account_customer->getTotalCustomersByEmail($email)) {
				$json['error']['email'] = $this->language->get('error_exists');
			}
		}

		if (!$json && $type == 'login' && !$this->model_account_customer->getTotalCustomersByEmail($email)) {
			$json['error']['email'] = $this->language->get('error_not_found');
		}

		// checkout: підтвердження пошти перед замовленням — існуючого логінимо,
		// нового реєструємо даними з форми чекауту
		if (!$json && $type == 'checkout') {
			$data = array(
				'firstname' => isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '',
				'lastname'  => isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '',
				'telephone' => isset($this->request->post['telephone']) ? trim($this->request->post['telephone']) : ''
			);
		}

		// Код на цю пошту вже надіслано й ще дійсний — новий лист не шлемо, але
		// це не помилка: користувач має ввести отриманий код, тож віддаємо успіх
		// (раніше тут була помилка, і вікно введення коду просто не відкривалось).
		if (!$json && isset($this->session->data['otp']) && $this->session->data['otp']['email'] == $email && (time() - $this->session->data['otp']['sent']) < 55) {
			// сценарій міг змінитись (вхід/реєстрація/чекаут) — оновлюємо, код лишається той самий
			$this->session->data['otp']['type'] = $type;
			$this->session->data['otp']['data'] = $data;

			$json['success'] = true;
			$json['already_sent'] = true;
			$json['message'] = $this->language->get('error_too_often');

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));

			return;
		}

		if (!$json) {
			$code = (string)rand(100000, 999999);

			$this->session->data['otp'] = array(
				'email'    => $email,
				'code'     => $code,
				'type'     => $type,
				'data'     => $data,
				'expires'  => time() + 600,
				'attempts' => 0,
				'sent'     => time()
			);

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($email);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_mail_subject'), $code), ENT_QUOTES, 'UTF-8'));
			$mail->setText(sprintf($this->language->get('text_mail_body'), $code));
			$mail->send();

			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function verifyCode() {
		$this->load->language('common/user_popup');

		$json = array();

		$email = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';
		$code = isset($this->request->post['code']) ? trim($this->request->post['code']) : '';

		$otp = isset($this->session->data['otp']) ? $this->session->data['otp'] : false;

		if (!$otp || $otp['email'] != $email) {
			$json['error']['code'] = $this->language->get('error_code_expired');
		} elseif ($otp['expires'] < time()) {
			unset($this->session->data['otp']);

			$json['error']['code'] = $this->language->get('error_code_expired');
		} elseif ($otp['attempts'] >= 5) {
			unset($this->session->data['otp']);

			$json['error']['code'] = $this->language->get('error_code_attempts');
		} elseif ($otp['code'] !== $code) {
			$this->session->data['otp']['attempts']++;

			$json['error']['code'] = $this->language->get('error_code_wrong');
		}

		if (!$json) {
			$this->load->model('account/customer');

			if ($otp['type'] == 'checkout' && !$this->model_account_customer->getTotalCustomersByEmail($email)) {
				$this->model_account_customer->addCustomer(array(
					'customer_group_id' => (int)$this->config->get('config_customer_group_id'),
					'firstname'         => $otp['data']['firstname'] ?: 'Клієнт',
					'lastname'          => $otp['data']['lastname'],
					'email'             => $email,
					'telephone'         => $otp['data']['telephone'],
					'password'          => token(20),
					'newsletter'        => 0
				));
			}

			if ($otp['type'] == 'register') {
				if ($this->model_account_customer->getTotalCustomersByEmail($email)) {
					$json['error']['code'] = $this->language->get('error_exists');
				} else {
					$this->model_account_customer->addCustomer(array(
						'customer_group_id' => (int)$this->config->get('config_customer_group_id'),
						'firstname'         => $otp['data']['firstname'],
						'lastname'          => $otp['data']['lastname'],
						'email'             => $email,
						'telephone'         => $otp['data']['telephone'],
						'password'          => token(20),
						'newsletter'        => 0
					));
				}
			}

			if (!$json && $otp['type'] == 'email') {
				if (!$this->customer->isLogged()) {
					$json['error']['warning'] = $this->language->get('error_login_required');
				} else {
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET email = '" . $this->db->escape($email) . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");

					unset($this->session->data['otp']);

					$json['success'] = true;
					$json['message'] = $this->language->get('text_email_changed');
					$json['redirect'] = $this->url->link('account/account', '', true);
				}
			}

			if (!$json && !isset($json['success'])) {
				$this->customer->login($email, '', true);

				unset($this->session->data['otp']);
				unset($this->session->data['guest']);

				$json['success'] = true;
				$json['message'] = ($otp['type'] == 'register') ? $this->language->get('text_registered') : $this->language->get('text_logged_in');

				// Optional return target (whitelisted) — e.g. back to checkout
				if (isset($this->request->post['redirect']) && $this->request->post['redirect'] == 'checkout') {
					$json['redirect'] = $this->url->link('checkout/checkout', '', true);
				} else {
					$json['redirect'] = $this->url->link('account/account', '', true);
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
