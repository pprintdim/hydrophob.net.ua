<?php
class ControllerStartupSession extends Controller {
	public function index() {
		if (isset($this->request->get['api_token']) && isset($this->request->get['route']) && substr($this->request->get['route'], 0, 4) == 'api/') {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "api_session` WHERE TIMESTAMPADD(HOUR, 1, date_modified) < NOW()");
					
			// Make sure the IP is allowed
			$api_query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "api` `a` LEFT JOIN `" . DB_PREFIX . "api_session` `as` ON (a.api_id = as.api_id) LEFT JOIN " . DB_PREFIX . "api_ip `ai` ON (a.api_id = ai.api_id) WHERE a.status = '1' AND `as`.`session_id` = '" . $this->db->escape($this->request->get['api_token']) . "' AND ai.ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");
		 
			if ($api_query->num_rows) {
				$this->session->start($this->request->get['api_token']);
				
				// keep the session alive
				$this->db->query("UPDATE `" . DB_PREFIX . "api_session` SET `date_modified` = NOW() WHERE `api_session_id` = '" . (int)$api_query->row['api_session_id'] . "'");
			}
		} else {
			if (isset($_COOKIE[$this->config->get('session_name')])) {
				$session_id = $_COOKIE[$this->config->get('session_name')];
			} else {
				$session_id = '';
			}
			
			$this->session->start($session_id);
			
			// SameSite=None: платіжки (WayForPay) повертають покупця крос-сайтовим
			// POST — з дефолтним Lax браузер не шле сесійну куку, покупця
			// розлогінювало, а стара кука перезаписувалась порожньою сесією.
			// Заголовок пишемо вручну: setcookie() тут дає непередбачуваний
			// результат (див. нотатки проєкту).
			$expires = '';

			if (ini_get('session.cookie_lifetime')) {
				$expires = '; expires=' . gmdate('D, d-M-Y H:i:s T', time() + (int)ini_get('session.cookie_lifetime'));
			}

			// детект по константі конфіга: настройки магазину на цьому етапі ще
			// не завантажені, а $_SERVER['HTTPS'] за проксі ненадійний
			$https = (defined('HTTPS_SERVER') && strpos(HTTPS_SERVER, 'https://') === 0)
				|| (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');

			header_remove('Set-Cookie');
			header(
				'Set-Cookie: ' . $this->config->get('session_name') . '=' . $this->session->getId()
				. $expires . '; path=/'
				. ($https ? '; Secure; SameSite=None' : '; SameSite=Lax')
				. '; HttpOnly',
				false
			);
		}
	}
}
