<?php
class ControllerExtensionModulePushconnect extends Controller {
    
    protected $registry;
    
    private $settings;
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('setting/setting');
        $this->registry = $registry;
        $this->settings = $this->model_setting_setting->getSetting('pushconnect', $this->config->get('config_store_id'));
        $this->enabled  = $this->config->get('module_pushconnect_status');        
    }   
    
    /**
     * common/header/after, updates the customer_id & endpoint
     */
    public function update() {
        if($this->enabled && $this->settings['pushconnect_status'] && $this->settings['pushconnect_script']) {
            $this->document->addScript($this->settings['pushconnect_script']);
        } 
        if($this->enabled && $this->settings['pushconnect_status'] && $this->customer->getId() ) {
            $pushconnect = new PushConnect($this->registry);
        }
    }
    
    /**
     * catalog/model/checkout/order/addOrderHistory/after
     */
    public function addOrderHistory(&$route, &$args, &$output){
        if($this->enabled && count($args) > 3) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($args[0]);
            $notify = $args[3];
            if ($notify) {
                $this->load->model('setting/setting');
                $pc_settings = $this->model_setting_setting->getSetting('pushconnect', $order_info['store_id']);
                
                if($pc_settings['pushconnect_status'] && $pc_settings['pushconnect_order_updates'] && $order_info['customer_id']) {
                    
                    if($order_info['customer_id']) {
                        $this->load->language('extension/module/pushconnect');
                        $pushconnect = new PushConnect($this->registry);
                        
                        $endpoints = $pushconnect->setApiKey($pc_settings['pushconnect_api_key'])->getEndpointForCustomerId($order_info['customer_id']);
                         
                        if(count($endpoints)) {
                            
                            $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_info['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
	
                            $message = array(
                                'title'     => $this->language->get('text_pc_update_title'), 
                                'body'   => sprintf($this->language->get('text_pc_update_body'), $order_id, date($this->language->get('date_format_short'), strtotime($order_info['date_added'])), $order_status_query->row['name']),
                                'url'       => $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_info['order_id']
                            );
                            
                            $pushconnect->sendNotification($endpoints, $message);
                        }
                    }
                }
            }
        }
    }
}