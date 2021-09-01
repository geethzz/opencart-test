<?php

class MyfatoorahController extends Controller {

    private $error = array();

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function loadAdmin($id, $fields) {
        $path = "payment/$id";

        // Load language
        $data = $this->language->load($path);

        //diff from oc version
        $ocUserToken    = 'token=' . $this->session->data['token'];
        $ocExLink       = 'extension/payment';
        $ocCode         = $data['ocCode'] = $id;


        // Set document title
        $this->document->setTitle($this->language->get('heading_title'));

        // Load settings
        $this->load->model('setting/setting');


        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }


        // If isset request to change settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($ocCode)) {

            // Edit settings
            $this->model_setting_setting->editSetting($ocCode, $this->request->post);

            // Set success message
            $this->session->data['success'] = $this->language->get('text_success');

            // Return to extensions page
            $this->response->redirect($this->url->link($path, $ocUserToken, true));
        }

        //Load errors if exist
        $data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning'] : '';

        $data['error_apiKey'] = (isset($this->error['apiKey'])) ? $this->error['apiKey'] : '';


        // Load action buttons urls
        $data['action'] = $this->url->link($path, $ocUserToken, true);
        $data['cancel'] = $this->url->link($ocExLink, $ocUserToken, true);

        // Load breadcrumbs
        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $ocUserToken, true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $data['cancel']
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $data['action']
        );

        // Set default values for fields
        foreach ($fields as $field) {
            $key        = $ocCode . '_' . $field;
            $data[$key] = (isset($this->request->post[$key])) ? $this->request->post[$key] : $this->config->get($key);
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Default values
        if (!isset($data[$ocCode . '_sort_order'])) {
            $data[$ocCode . '_sort_order'] = 1;
        }
        if (!isset($data[$ocCode . '_debug'])) {
            $data[$ocCode . '_debug'] = 1;
        }
        if (!isset($data[$ocCode . '_initial_order_status_id'])) {
            $data[$ocCode . '_initial_order_status_id'] = 1;
        }
        if (!isset($data[$ocCode . '_order_status_id'])) {
            $data[$ocCode . '_order_status_id'] = $this->config->get('config_processing_status');
        }
        if (!isset($data[$ocCode . '_failed_order_status_id'])) {
            $data[$ocCode . '_failed_order_status_id'] = 10;
        } 

        // Load default layout, must be in the end
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($path . '.tpl', $data));
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    protected function validate($ocCode) {

        if (!trim($this->request->post[$ocCode . '_apiKey'])) {
            $this->error['apiKey']  = $this->error['warning'] = $this->language->get('error_apiKey');
        }

        return !$this->error;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
