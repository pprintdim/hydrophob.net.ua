<?php
// One mailer for every "write to us" form on the site (contact, dealer
// application, ...) so a new form only needs its field list, not another copy
// of the store mail settings.
class ModelToolLead extends Model {
	public function send($subject, $fields, $reply_to = '', $to = '') {
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($to ? $to : $this->config->get('config_email'));
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		if ($reply_to && filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
			$mail->setReplyTo($reply_to);
		}

		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));

		$lines = array();

		foreach ($fields as $label => $value) {
			$value = trim((string)$value);

			if ($value !== '') {
				$lines[] = html_entity_decode($label . ': ' . $value, ENT_QUOTES, 'UTF-8');
			}
		}

		$mail->setText(implode("\n", $lines));
		$mail->send();
	}

	// Mail can bounce or be missed, so every submission is also kept in the
	// admin's own list.
	public function store($form, $values, $subject) {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "lead` (
			`lead_id` int(11) NOT NULL AUTO_INCREMENT,
			`form` varchar(32) NOT NULL,
			`subject` varchar(255) NOT NULL,
			`name` varchar(96) NOT NULL,
			`email` varchar(96) NOT NULL,
			`telephone` varchar(64) NOT NULL,
			`city` varchar(128) NOT NULL,
			`message` text NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '0',
			`ip` varchar(45) NOT NULL,
			`date_added` datetime NOT NULL,
			PRIMARY KEY (`lead_id`),
			KEY `form` (`form`),
			KEY `date_added` (`date_added`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "lead` SET
			`form` = '" . $this->db->escape($form) . "',
			`subject` = '" . $this->db->escape($subject) . "',
			`name` = '" . $this->db->escape(isset($values['name']) ? $values['name'] : '') . "',
			`email` = '" . $this->db->escape(isset($values['email']) ? $values['email'] : '') . "',
			`telephone` = '" . $this->db->escape(isset($values['telephone']) ? $values['telephone'] : (isset($values['phone']) ? $values['phone'] : '')) . "',
			`city` = '" . $this->db->escape(isset($values['city']) ? $values['city'] : '') . "',
			`message` = '" . $this->db->escape(isset($values['enquiry']) ? $values['enquiry'] : '') . "',
			`ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "',
			`date_added` = NOW()");
	}
}
