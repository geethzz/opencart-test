<?php

class ModelExtensionTotalPcDiscount extends Model {
    
    private $code = 'pc_discount';
    
    private $settings = [];

    public function getTotal($total) {
        $this->load->model('setting/setting');
        $this->load->language('extension/total/pc_discount');
        $this->settings = $this->model_setting_setting->getSetting('pushconnect', $this->config->get('config_store_id'));
        if ($this->validate()) {
            
            if($this->settings['pushconnect_ot_module_discount_type'] === 'F') {
                $value = (float)$this->settings['pushconnect_ot_module_discount_value'];
            } else {
                $discount_percent = $this->settings['pushconnect_ot_module_discount_value'];
                $value = round(($total['total']/100)*$discount_percent, 2);               
            }    
            
            $total['totals'][] = array(
                'code' => $this->code,
                'title' => $this->language->get('text_push_discount'),
                'value' => -$value,
                'sort_order' => $this->config->get('total_pc_discount_sort_order')
            );

            $total['total'] -= $value;
        }
    }
    
    /**
     * Validate the discount
     * @return boolean
     */
    private function validate() {
        if (!$this->settings['pushconnect_status'] || !$this->settings['pushconnect_ot_module_status'] || !isset($this->request->cookie['pc_push_subscription']) || $this->usedBefore()) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Check if the customer has used the voucher before
     * @return boolean
     */    
    private function usedBefore() {
        if($this->customer->getId() && $this->orders($this->customer->getId()) >= $this->settings['pushconnect_ot_module_discount_times']) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get the number of orders for the customer id
     * @param int $customer_id
     * @return int
     */    
    private function orders($customer_id) {
        $sql = "SELECT o.order_id FROM
                oc_order o
                INNER JOIN oc_order_total ot ON o.order_id = ot.order_id
                WHERE order_status_id!=0 and o.customer_id = '" . (int)$customer_id . "' and code ='" . $this->code . "'";
        $orders = $this->db->query($sql);
        return $orders->num_rows;
    }

}
