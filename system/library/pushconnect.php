<?php

use GuzzleHttp\Client;

class PushConnect {

    private $endpoint = 'https://pushconnect.tech/api/';
    private $api_key;
    private $customer;
    private $db;
    private $subscription;
    private $session;
    private $enable_logging = false;
    
    public function __construct($registry) {
        
        
        
        if(isset($_COOKIE['pc_push_subscription'])) {
            $this->subscription = json_decode($_COOKIE['pc_push_subscription']);
        }
        
        $this->session = $registry->get('session');
        $this->db = $registry->get('db');
        $this->logger = new Log('pushconnect.log');
        $this->config = $registry->get('config');
        
        $this->enable_logging = $this->config->get('pushconnect_logging');
       
        if ($customer = $registry->get('customer') && $this->subscription && $this->subscription->endpoint) {
            
            $this->customer = $registry->get('customer');            
            $this->setCustomerId();
        }
    }
    
    public function setCustomerId() {
        if($this->customer->getId()) {
            $data = $this->db->query("SELECT `key` from " . DB_PREFIX . "pc_tracking 
                                      WHERE `key` = 'customer_id' 
                                      AND `endpoint` = '" . $this->db->escape($this->subscription->endpoint) . "'");
            if($data->num_rows) {
                $this->db->query("UPDATE " . DB_PREFIX . "pc_tracking 
                                  SET `value` = '" . (int)$this->customer->getId() . "', `timestamp` = NOW()
                                  WHERE `endpoint` = '" . $this->db->escape($this->subscription->endpoint) . "' 
                                  AND `key` = 'customer_id'
                              ");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "pc_tracking SET 
                              `endpoint` = '" . $this->db->escape($this->subscription->endpoint) . "', 
                              `key` = 'customer_id', 
                              `value` = '" . (int)$this->customer->getId() . "',
                              `timestamp` = NOW()");
            }            
        }
    }
    
    public function getEndpointForCustomerId($customer_id) {
        if(!is_array($customer_id)) {
            $customers = [$customer_id];
        } else {
            $customers = $customer_id;
        }
        $customer_ids = array_map(function($customer) {
           return (int)$customer; 
        }, $customers);
        $data = $this->db->query("SELECT endpoint, value as customer_id from " . DB_PREFIX . "pc_tracking where `key` = 'customer_id' AND `value` IN (" . implode(',',$customer_ids) . ")");
        if($data->num_rows) {
            return array_map(function($customer){
                return $customer['endpoint'];
            }, $data->rows);
        } else {
            return null;
        }
    }

    public function setApiKey($api_key) {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * Assign generic data to an endpoint
     * @param array $data [['endpoint' => '', 'key' => '', 'value' => '']]
     */
    public function setEndpointData($data) {
        if (isset($data['endpoint']))
            $data = [$data]; //put singles into an array to enable multiple key/values in one request
        return $this->post('testapi', ['endpoint' => $data]);
    }

    public function filterEndpoints($filters) {
        if (isset($filters['key']))
            $filters = [$filters]; //put singles into an array to enable multiple key/values in one request
        return $this->post('endpoint/filter', ['filters' => $filters]);
    }
    
    /**
     * Create a message
     */
    
    public function createMessage() {
        
    }

    public function sendNotification($endpoints, $message) {
        if(!is_array($endpoints)) {
            $endpoints = [$endpoints];
        }
        return $this->post('endpoint/sendpush', ['message' => $message, 'endpoints' => $endpoints]);
    }

    public function post($route, $data) {
        try {
            $client = new Client();
            $response = $client->post($this->endpoint . $route, [
                'debug' => FALSE,
                'body' => $data,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'x-authorization' => $this->api_key
                ]
            ]);
            return json_decode($response->getBody()->getContents());
        } catch(Exception $e) {
            $this->log($e->getMessage());
            return ['api_success' => false, 'message' => $e->getMessage()];
        }
        
    }
    
    public function log($data) {
        if($this->enable_logging) {
            if(function_exists('getmypid')) {
                    $process_id = getmypid();
                    $data = $process_id . ' - ' . $data;
            }        
            $this->logger->write($data);
        }
    }

}
