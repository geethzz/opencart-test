<?php
class ControllerExtensionTotalPcDiscount extends Controller {
	private $error = array();

	public function index() {
        
		$this->load->language('extension/total/pc_discount');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_pc_discount', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', 'SSL'));
		}
        

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_total'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/pc_discount', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/total/pc_discount', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', 'SSL');

		if (isset($this->request->post['total_pc_discount_status'])) {
			$data['total_pc_discount_status'] = $this->request->post['total_pc_discount_status'];
		} else {
			$data['total_pc_discount_status'] = $this->config->get('total_pc_discount_status');
		}

		if (isset($this->request->post['total_pc_discount_sort_order'])) {
			$data['total_pc_discount_sort_order'] = $this->request->post['total_pc_discount_sort_order'];
		} else {
			$data['total_pc_discount_sort_order'] = $this->config->get('total_pc_discount_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total/pc_discount', $data));
	}

	protected function validate() {
		//if (!$this->user->hasPermission('modify', 'extension/total/pc_discount')) {
			//$this->error['warning'] = $this->language->get('error_permission');
		//}

		return !$this->error;
	}
}