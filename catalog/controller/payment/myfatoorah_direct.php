<?php

require_once 'myfatoorah_controller.php';

class ControllerPaymentMyfatoorahDirect extends MyfatoorahController {

    protected $id = 'myfatoorah_direct';

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function __construct($registry) {
        parent::__construct($registry);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function index() {

        $data = $this->language->load($this->path);

        $data['action'] = 'index.php?route=' . $this->path . '/confirm';

        if (($this->config->get($this->ocCode . '_test') != '1') && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')) {
            $data['error'] = $data['error_enable_ssl'];
        } else {
            $this->displayCardForm($data);
        }


        $file = $this->path . '.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $file)) {
            $file = $this->config->get('config_template') . '/template/' . $file;
        }
        return $this->load->view($file, $data);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function confirm() {

        if (!isset($this->session->data['order_id'])) {
            $this->session->data['error'] = 'Session has been expired please try again later';
            return $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        $this->load->model('checkout/order');

        $this->orderId = $this->session->data['order_id'];
        $order_info    = $this->model_checkout_order->getOrder($this->orderId);


        $cardInfo = [
            'PaymentType' => 'card',
            'Bypass3DS'   => 'false',
            'Card'        => [
                'CardHolderName' => $this->request->post['cc_owner'],
                'Number'         => $this->request->post['cc_number'],
                'ExpiryMonth'    => $this->request->post['cc_expire_date_month'],
                'ExpiryYear'     => substr($this->request->post['cc_expire_date_year'], 2),
                'SecurityCode'   => $this->request->post['cc_cvv2'],
            ]
        ];

        try {
            $curlData = $this->getPayload($order_info);
            $gateway  = $this->request->post['cc_type'];

            $json = $this->mfObj->directPayment($curlData, $gateway, $cardInfo, $this->orderId);
            $msg  = '<b>MyFatoorah Invoice Details:</b><br> Invoice ID ' . $json['invoiceId'] . '<br>';
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get($this->ocCode . '_initial_order_status_id'), $msg, false);

            $this->response->redirect($json['invoiceURL']);
        } catch (Exception $ex) {
            $this->session->data['error'] = $ex->getMessage();
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    private function displayCardForm(&$data) {
        try {
            $gateways = $this->mfObj->getVendorGateways();

            foreach ($gateways as $value) {
                if ($value->IsDirectPayment) {
                    $data['cards'][] = array(
                        'text'  => ($this->language->get('code') == 'ar') ? $value->PaymentMethodAr : $value->PaymentMethodEn,
                        'value' => $value->PaymentMethodId
                    );
                }
            }

            if (!isset($data['cards'])) {
                $data['error'] = $this->language->get('error_enable_direct');
                return;
            }

            for ($i = 1; $i <= 12; $i++) {
                $data['months'][] = sprintf('%02d', $i);
            }

            $today = getdate()['year'];
            for ($i = $today; $i < $today + 11; $i++) {
                $data['years'][] = $i;
            }

            $file = $this->path . '_paymentType.tpl';
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $file)) {
                $file = $this->config->get('config_template') . '/template/' . $file;
            }
            
            $data['directPaymentForm'] = $this->load->view($file, $data);
        } catch (Exception $ex) {
            $data['error'] = $ex->getMessage();
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
