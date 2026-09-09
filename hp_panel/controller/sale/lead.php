<?php
// Submissions from the site forms (contact, dealer application) live here so
// they are not lost if a mail goes missing.
class ControllerSaleLead extends Controller {
	public function index() {
		$this->load->language('sale/lead');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	public function view() {
		$this->load->language('sale/lead');
		$this->load->model('sale/lead');

		$lead_id = isset($this->request->get['lead_id']) ? (int)$this->request->get['lead_id'] : 0;
		$lead = $this->model_sale_lead->getLead($lead_id);

		if (!$lead) {
			$this->response->redirect($this->url->link('sale/lead', 'user_token=' . $this->session->data['user_token'], true));
		}

		// opening a submission counts as reading it
		if (!$lead['status']) {
			$this->model_sale_lead->setStatus($lead_id, 1);
			$lead['status'] = 1;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['lead'] = $lead;
		$data['lead']['form_name'] = $this->formName($lead['form']);
		$data['lead']['date_added'] = date($this->language->get('datetime_format'), strtotime($lead['date_added']));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['back'] = $this->url->link('sale/lead', 'user_token=' . $this->session->data['user_token'], true);
		$data['delete'] = $this->url->link('sale/lead/delete', 'user_token=' . $this->session->data['user_token'] . '&lead_id=' . $lead_id, true);

		$data['breadcrumbs'] = $this->breadcrumbs();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/lead_info', $data));
	}

	public function delete() {
		$this->load->language('sale/lead');
		$this->load->model('sale/lead');

		if ($this->user->hasPermission('modify', 'sale/lead') && isset($this->request->get['lead_id'])) {
			$this->model_sale_lead->deleteLead($this->request->get['lead_id']);
			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->response->redirect($this->url->link('sale/lead', 'user_token=' . $this->session->data['user_token'], true));
	}

	private function getList() {
		$this->load->model('sale/lead');

		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
		$filter_form = isset($this->request->get['filter_form']) ? (string)$this->request->get['filter_form'] : '';
		$limit = 20;

		$filter = array(
			'filter_form' => $filter_form,
			'start'       => ($page - 1) * $limit,
			'limit'       => $limit
		);

		$total = $this->model_sale_lead->getTotalLeads($filter);

		$data['leads'] = array();

		foreach ($this->model_sale_lead->getLeads($filter) as $result) {
			$data['leads'][] = array(
				'lead_id'    => $result['lead_id'],
				'form_name'  => $this->formName($result['form']),
				'name'       => $result['name'],
				'email'      => $result['email'],
				'telephone'  => $result['telephone'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'view'       => $this->url->link('sale/lead/view', 'user_token=' . $this->session->data['user_token'] . '&lead_id=' . $result['lead_id'], true),
				'delete'     => $this->url->link('sale/lead/delete', 'user_token=' . $this->session->data['user_token'] . '&lead_id=' . $result['lead_id'], true)
			);
		}

		$data['forms'] = array(
			array('code' => '', 'name' => $this->language->get('text_all_forms')),
			array('code' => 'quick', 'name' => $this->language->get('text_form_quick')),
			array('code' => 'question', 'name' => $this->language->get('text_form_question')),
			array('code' => 'contact', 'name' => $this->language->get('text_form_contact'))
		);

		$data['filter_form'] = $filter_form;
		$data['total_new'] = $this->model_sale_lead->getTotalNew();

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('sale/lead', 'user_token=' . $this->session->data['user_token'] . '&filter_form=' . urlencode($filter_form) . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), $total ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit), $total, ceil($total / $limit));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['action'] = $this->url->link('sale/lead', 'user_token=' . $this->session->data['user_token'], true);
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = $this->breadcrumbs();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/lead_list', $data));
	}

	private function formName($code) {
		$names = array(
			'quick'    => $this->language->get('text_form_quick'),
			'question' => $this->language->get('text_form_question'),
			'contact'  => $this->language->get('text_form_contact')
		);

		return isset($names[$code]) ? $names[$code] : $code;
	}

	private function breadcrumbs() {
		return array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('sale/lead', 'user_token=' . $this->session->data['user_token'], true)
			)
		);
	}
}
