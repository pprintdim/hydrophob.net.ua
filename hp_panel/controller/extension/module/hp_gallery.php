<?php
// "Hydrophob у дії": репітер фото/відео (плитка = зображення або постер + кліп).
class ControllerExtensionModuleHpGallery extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/hp_gallery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('hp_gallery', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/hp_gallery', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/hp_gallery', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $data['action']
		);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['name'] = $this->value('name', isset($module_info) ? $module_info : array(), '');
		$data['status'] = $this->value('status', isset($module_info) ? $module_info : array(), 0);
		$data['items'] = $this->value('items', isset($module_info) ? $module_info : array(), array());
		$data['gallery_description'] = $this->value('gallery_description', isset($module_info) ? $module_info : array(), array());

		$this->load->model('localisation/language');
		$this->load->model('tool/image');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		foreach ($data['items'] as $key => $item) {
			if (!empty($item['image']) && is_file(DIR_IMAGE . html_entity_decode($item['image'], ENT_QUOTES, 'UTF-8'))) {
				$data['items'][$key]['thumb'] = $this->model_tool_image->resize(html_entity_decode($item['image'], ENT_QUOTES, 'UTF-8'), 84, 84);
			} else {
				$data['items'][$key]['thumb'] = $this->model_tool_image->resize('no_image.png', 84, 84);
			}
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 84, 84);

		$data['video_files'] = $this->videoFiles();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hp_gallery', $data));
	}

	// відео беруться з теки теми vid/ — у формі лише імена файлів
	private function videoFiles() {
		$files = glob(DIR_CATALOG . 'view/theme/default/vid/*.mp4');

		$result = array();

		if ($files) {
			foreach ($files as $file) {
				$result[] = basename($file);
			}
		}

		return $result;
	}

	private function value($key, $module_info, $default) {
		if (isset($this->request->post[$key])) {
			return $this->request->post[$key];
		}

		if (isset($module_info[$key])) {
			return $module_info[$key];
		}

		return $default;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/hp_gallery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		// плитка без зображення: для відео постер обов'язковий, для фото — саме фото
		if (!empty($this->request->post['items'])) {
			foreach ($this->request->post['items'] as $item) {
				if (empty($item['image'])) {
					$this->error['warning'] = $this->language->get('error_item_image');

					break;
				}
			}
		}

		return !$this->error;
	}
}
