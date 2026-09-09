<?php
// Ajax-форма «Поставити питання» (сторінка FAQ): лист власнику магазину.
// Захист — honeypot + пауза між надсиланнями в сесії, як у швидкому замовленні.
class ControllerInformationQuestion extends Controller {
	public function send() {
		$this->load->language('information/faq');

		$json = array();
		$post = $this->request->post;

		$name = isset($post['name']) ? trim((string)$post['name']) : '';
		$telephone = isset($post['telephone']) ? trim((string)$post['telephone']) : '';
		$email = isset($post['email']) ? trim((string)$post['email']) : '';
		$question = isset($post['question']) ? trim((string)$post['question']) : '';

		// авторизованому контакти беремо з акаунта — форма їх лише показує
		if ($this->customer->isLogged()) {
			if ($email === '') {
				$email = $this->customer->getEmail();
			}

			if ($telephone === '') {
				$telephone = $this->customer->getTelephone();
			}
		}

		$contact = $email . ($telephone ? ', ' . $telephone : '');

		// бот заповнює приховане поле — тихо вдаємо успіх
		if (!empty($post['company_website'])) {
			$json['success'] = true;

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));

			return;
		}

		if (utf8_strlen($name) < 2) {
			$json['error']['name'] = $this->language->get('error_q_name');
		}

		if ((utf8_strlen($telephone) < 5) || (utf8_strlen($telephone) > 32)) {
			$json['error']['telephone'] = $this->language->get('error_q_phone');
		}

		if ((utf8_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$json['error']['email'] = $this->language->get('error_q_email');
		}

		// пошта має бути підтверджена: гість проходить код і стає покупцем
		if (!$json && !$this->customer->isLogged()) {
			$json['error']['email'] = $this->language->get('error_q_verify');
		}

		if (utf8_strlen($question) < 10) {
			$json['error']['question'] = $this->language->get('error_q_text');
		}

		if (!$json && isset($this->session->data['question_sent']) && (time() - $this->session->data['question_sent']) < 30) {
			$json['error']['question'] = $this->language->get('error_q_often');
		}

		if (!$json) {
			$store = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$body = sprintf($this->language->get('text_q_body'), $name, $contact, $question);

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_q_subject'), $name), ENT_QUOTES, 'UTF-8'));
			$mail->setText($body);

			// пошта покупця у Reply-To, щоб відповідати одним кліком
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$mail->setReplyTo($email);
			}

			$mail->send();

			// лист може загубитись — заявку дублюємо в список адмінки
			$this->load->model('tool/lead');

			$this->model_tool_lead->store('question', array(
				'name'      => $name,
				'email'     => $email,
				'telephone' => $telephone,
				'enquiry'   => $question
			), sprintf($this->language->get('text_q_subject'), $name));

			$this->session->data['question_sent'] = time();

			$json['success'] = true;
			$json['message'] = $this->language->get('text_q_thanks');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
