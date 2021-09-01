<?php

class ControllerExtensionModulePushConnect extends Controller {

    private $errors;
    
    public function index() {
        $this->load->language('extension/module/pushconnect');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            foreach($this->request->post['settings'] as $store_id => $settings) {
                $this->model_setting_setting->editSetting('pushconnect', $settings, $store_id);
            }
            unset($this->request->post['settings']);
            $this->model_setting_setting->editSetting('module_pushconnect', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/pushconnect', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        } else {
            $this->getForm();
        }
        
    }
    
    private function validate() {
        foreach($this->request->post['settings'] as $store_id => $settings) {
            
            if($this->request->post['module_pushconnect_status'] && isset($settings['pushconnect_status']) && $settings['pushconnect_status'] == 1) {
                if(!isset($settings['pushconnect_api_key']) || empty($settings['pushconnect_api_key'])) {
                    $this->errors[$store_id]['pushconnect_api_key'] = $this->language->get('error_api_key');
                }
                if(!isset($settings['pushconnect_script']) || empty($settings['pushconnect_script'])) {
                    $this->errors[$store_id]['pushconnect_script'] = $this->language->get('error_script');
                }
            }
            
            if($this->request->post['module_pushconnect_status'] && $settings['pushconnect_ot_module_status'] == 1) {
                
                if($settings['pushconnect_ot_module_discount_value'] <= 0) {
                    $this->errors[$store_id]['pushconnect_ot_module_discount_value'] = $this->language->get('error_ot_module_value_minus');
                }
                
                if($settings['pushconnect_ot_module_discount_value'] > 100 && $settings['pushconnect_ot_module_discount_type'] == 'P') {
                    $this->errors[$store_id]['pushconnect_ot_module_discount_value'] = $this->language->get('error_ot_module_value_over_100');
                }
                
                if($settings['pushconnect_ot_module_discount_times'] < 0) {
                    $this->errors[$store_id]['pushconnect_ot_module_discount_times'] = $this->language->get('error_ot_module_times_minus');
                }
                
            }
            
        }
        return !$this->errors;
    }

    private function getForm() {
        $this->load->model('setting/store');
        $this->load->model('setting/setting');
        $data = array();
        
        $data['stores'] = $this->getStores();
        
        
        if (isset($this->request->post['module_pushconnect_status'])) {
			$data['module_pushconnect_status'] = $this->request->post['module_pushconnect_status'];
		} else {
			$data['module_pushconnect_status'] = $this->config->get('module_pushconnect_status');
		}
        
        foreach($data['stores'] as &$store) {
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_status'])) {
                $store['pushconnect_status'] = $this->request->post['settings'][$store['store_id']]['pushconnect_status'];
            } else if (!isset($store['pushconnect_status'])) {
                $store['pushconnect_status'] = 0;
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_logging'])) {
                $store['pushconnect_logging'] = $this->request->post['settings'][$store['store_id']]['pushconnect_logging'];
            } else if (!isset($store['pushconnect_logging'])) {
                $store['pushconnect_logging'] = 0;
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_status'])) {
                $store['pushconnect_ot_module_status'] = $this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_status'];
            } else if (!isset($store['pushconnect_ot_module_status'])) {
                $store['pushconnect_ot_module_status'] = 0;
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_discount_type'])) {
                $store['pushconnect_ot_module_discount_type'] = $this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_discount_type'];
            } else if (!isset($store['pushconnect_ot_module_discount_type'])) {
                $store['pushconnect_ot_module_discount_type'] = 'P';
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_discount_value'])) {
                $store['pushconnect_ot_module_discount_value'] = $this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_discount_value'];
            } else if (!isset($store['pushconnect_ot_module_discount_value'])) {
                $store['pushconnect_ot_module_discount_value'] = 0;
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_discount_times'])) {
                $store['pushconnect_ot_module_discount_times'] = $this->request->post['settings'][$store['store_id']]['pushconnect_ot_module_discount_times'];
            } else if (!isset($store['pushconnect_ot_module_discount_times'])) {
                $store['pushconnect_ot_module_discount_times'] = 0;
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_api_key'])) {
                $store['pushconnect_api_key'] = $this->request->post['settings'][$store['store_id']]['pushconnect_api_key'];
            } else if (!isset($store['pushconnect_api_key'])) {
                $store['pushconnect_api_key'] = '';
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_script'])) {
                $store['pushconnect_script'] = $this->request->post['settings'][$store['store_id']]['pushconnect_script'];
            } else if (!isset($store['pushconnect_script'])) {
                $store['pushconnect_script'] = '';
            }
            
            if(isset($this->request->post['settings'][$store['store_id']]['pushconnect_order_updates'])) {
                $store['pushconnect_order_updates'] = $this->request->post['settings'][$store['store_id']]['pushconnect_order_updates'];
            } else if (!isset($store['pushconnect_order_updates'])) {
                $store['pushconnect_order_updates'] = 0;
            }
        }
        
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
          'text' => $this->language->get('text_home'),
          'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
        
        $data['action'] = $this->url->link('extension/module/pushconnect', 'user_token=' . $this->session->data['user_token'], 'SSL'); 
		
		$data['install_link'] = $this->url->link('extension/module/pushconnect/install_sw', 'user_token=' . $this->session->data['user_token'], 'SSL'); 
		
		$data['sw_installed'] = file_exists($_SERVER['DOCUMENT_ROOT'] . '/pushconnect-sw.js');
        
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        
		$data['errors'] = $this->errors;
        
		if (isset($this->errors['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}        

        if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/pushconnect', $data));
       
    }
    
    private function getStores() {
        $stores = array(array('store_id' => 0, 'name'=> 'Default'));
        if($additional_stores = $this->model_setting_store->getStores()) {
            foreach($additional_stores as $store) {
                $stores[] = $store;
            }
        }
        foreach($stores as &$store) {
            $store = array_merge($store, $this->model_setting_setting->getSetting('pushconnect', $store['store_id']));
        }
        return $stores;
    }
    
     public function install_sw() {
        $url = 'https://pushconnect.tech/pushconnect-sw.js';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $data = curl_exec($curl);        
        $type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);
		
        if($type === 'application/javascript'){			
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/pushconnect-sw.js', $data);
        } 
		
        $response = ['success' => file_exists($_SERVER['DOCUMENT_ROOT'] . '/pushconnect-sw.js')];
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
    }
    
    public function install() {
        $sql = "CREATE TABLE IF NOT EXISTS `oc_pc_tracking` (
                    `endpoint` text,
                    `key` varchar(255) DEFAULT NULL,
                    `value` text,
                    `timestamp` timestamp NULL DEFAULT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;";
        $this->db->query($sql);
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('pushconnect', 'catalog/controller/common/header/before', 'extension/module/pushconnect/update');
        $this->model_setting_event->addEvent('pushconnect', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/module/pushconnect/addOrderHistory');        
	}
    public function uninstall() {
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('pushconnect');
	}

}

?>