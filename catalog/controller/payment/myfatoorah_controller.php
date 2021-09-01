<?php

class MyfatoorahController extends Controller {

    protected $id;
    protected $path;
    protected $ocCode;
    protected $logger;
    protected $isLog;
    protected $orderId;
    protected $mfObj;

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function __construct($registry) {
        parent::__construct($registry);

        $this->path   = 'payment/' . $this->id;
        $this->ocCode = $this->id;

        $this->logger = new Log($this->id . '.log');
        $this->isLog  = $this->config->get($this->ocCode . '_debug') === '1' ? true : false;

        $apiKey = $this->config->get($this->ocCode . '_apiKey');

        $isTest = $this->config->get($this->ocCode . '_test') === '1' ? true : false;

        require_once DIR_SYSTEM . 'library/myfatoorah/PaymentMyfatoorahApiV2.php';
        $this->mfObj = ($this->isLog) ? new PaymentMyfatoorahApiV2($apiKey, $isTest, $this->logger, 'write') : new PaymentMyfatoorahApiV2($apiKey, $isTest);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    protected function getPayload($order_info) {

        $orderId = $this->orderId;

        $gFname = isset($this->session->data['guest']['firstname']) ? $this->session->data['guest']['firstname'] : '';
        $gLname = isset($this->session->data['guest']['lastname']) ? $this->session->data['guest']['lastname'] : '';
        $gEmail = isset($this->session->data['guest']['email']) ? $this->session->data['guest']['email'] : '';
        $gPhone = isset($this->session->data['guest']['telephone']) ? $this->session->data['guest']['telephone'] : '';

        $fname = $this->customer->getFirstName() ?: $gFname;
        $lname = $this->customer->getLastName() ?: $gLname;
        $email = $this->customer->getEmail() ?: $gEmail;
        $phone = $this->customer->getTelephone() ?: $gPhone;

        $phoneArr = $this->mfObj->getPhone($phone);

        $returnUrl = $this->url->link($this->path . '/callback', '', true) . '&orid=' . base64_encode($orderId);


        $shippingMethod    = null;
        $shippingAddress   = !empty($this->session->data['shipping_address']['address_1']) ? $this->session->data['shipping_address']['address_1'] . ' ' . $this->session->data['shipping_address']['address_2'] : '';
        $shippingConsignee = null;
        if (isset($this->session->data['shipping_method']['mfid'])) {
            $shippingMethod = $this->session->data['shipping_method']['mfid'];

            $shippingConsignee = array(
                'PersonName'   => $fname . ' ' . $lname,
                'Mobile'       => $phoneArr[1],
                'EmailAddress' => $email,
                'LineAddress'  => $shippingAddress,
                'CityName'     => !empty($this->session->data['shipping_address']['zone']) ? $this->session->data['shipping_address']['zone'] : $this->session->data['shipping_address']['city'],
                'PostalCode'   => $this->session->data['shipping_address']['postcode'],
                'CountryCode'  => $this->session->data['shipping_address']['iso_code_2']
            );
        }

//        $amount = $order_info['total'] * $order_info['currency_value'];
//        $amount = $this->currency->format($amount, $order_info['currency_code'], '', false);
//        $amount = round($amount, 3);
//        $invoiceItems = [['ItemName' => "Total Amount of Order #$orderId", 'Quantity' => 1, 'UnitPrice' => $amount]];

        $amount       = 0;
        $invoiceItems = $this->getInvoiceItems($order_info, $amount, $shippingMethod);

        $data = [
            'CustomerName'       => $fname . ' ' . $lname,
            'DisplayCurrencyIso' => $order_info['currency_code'],
            'MobileCountryCode'  => $phoneArr[0],
            'CustomerMobile'     => $phoneArr[1],
            'CustomerEmail'      => $email,
            'InvoiceValue'       => "$amount",
            'CallBackUrl'        => $returnUrl,
            'ErrorUrl'           => $returnUrl,
            'Language'           => $this->language->get('code'),
            'CustomerReference'  => $orderId,
            'ShippingConsignee'  => $shippingConsignee,
            'CustomerCivilId'    => '',
            'UserDefinedField'   => $this->id,
            'ExpiryDate'         => '',
            'CustomerAddress'    => [
                'Block'               => '',
                'Street'              => '',
                'HouseBuildingNo'     => '',
                'Address'             => $this->session->data['payment_address']['city'] . ', ' . $this->session->data['payment_address']['zone'] . ', ' . $this->session->data['payment_address']['postcode'] . ', ' . $this->session->data['payment_address']['country'],
                'AddressInstructions' => $shippingAddress,
            ],
            'InvoiceItems'       => $invoiceItems,
            'ShippingMethod'     => $shippingMethod,
            'SourceInfo'         => 'OpenCart ' . VERSION . ' ' . $this->id,
        ];
        return $data;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    protected function getInvoiceItems($order_info, &$amount, $shippingMethod = null) {

        $invoiceItemsArr = array();

        //---------------------product items
        $products = $this->cart->getProducts();
        foreach ($products as $product) {
            $weightRate    = ($shippingMethod) ? $this->mfObj->getWeightRate($this->weight->getUnit($product['weight_class_id'])) : 1;
            $dimensionRate = ($shippingMethod) ? $this->mfObj->getDimensionRate($this->length->getUnit($product['length_class_id'])) : 1;

            $unitPrice = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
//            $unitPrice = $unitPrice * $order_info['currency_value'];
            $unitPrice = $this->currency->format($unitPrice, $order_info['currency_code'], '', false);
            $unitPrice = round($unitPrice, 3);

            $amount += $unitPrice * $product['quantity'];

            $invoiceItemsArr[] = [
                'ItemName'  => $product['name'],
                'Quantity'  => $product['quantity'],
                'UnitPrice' => $unitPrice,
                'weight'    => $product['weight'] * $weightRate,
                'Width'     => $product['width'] * $dimensionRate,
                'Height'    => $product['height'] * $dimensionRate,
                'Depth'     => $product['length'] * $dimensionRate,
            ];
        }

        //---------------------Gift User Created Vouchers 
        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {

                $unitPrice = $voucher['amount'] * $order_info['currency_value'];
                $unitPrice = $this->currency->format($unitPrice, $order_info['currency_code'], '', false);
                $unitPrice = round($unitPrice, 3);

                $amount += $unitPrice;

                $invoiceItemsArr[] = array('ItemName' => $voucher['description'], 'Quantity' => 1, 'UnitPrice' => "$unitPrice", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0');
            }
        }

        //---------------------shipping, tax, credit, handling, low_order_fee, coupon, reward, voucher, sub_total and total prices
        $items = $this->getCartItems();
        //other items in cart 
        foreach ($items as $item) {
            if ($item['code'] != 'sub_total' && $item['code'] != 'total' && ($item['code'] != 'shipping' || ($shippingMethod == null && $item['code'] == 'shipping' ))) {

                $unitPrice = $this->tax->calculate($item['value'], $product['tax_class_id'], $this->config->get('config_tax'));
//                $unitPrice = $unitPrice * $order_info['currency_value'];
                $unitPrice = $this->currency->format($unitPrice, $order_info['currency_code'], '', false);
                $unitPrice = round($unitPrice, 3);

                $amount += $unitPrice;

                $invoiceItemsArr[] = array('ItemName' => $item['title'], 'Quantity' => 1, 'UnitPrice' => "$unitPrice", 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0');
            }
        }

        return $invoiceItemsArr;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    private function getCartItems() {

        $items = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $this->load->model('extension/extension');
        $results = $this->model_extension_extension->getExtensions('total');


        //to sort data and recalc them right very imp
        $sort_order = array();
        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }
        array_multisort($sort_order, SORT_ASC, $results);


        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('total/' . $result['code']);

                // We have to put the totals in an array so that they pass by reference.
                $this->{'model_total_' . $result['code']}->getTotal($items, $total, $taxes);
            }
        }

//        $sort_order = array();
//        foreach ($totals as $key => $value) {
//            $sort_order[$key] = $value['sort_order'];
//        }
//        array_multisort($sort_order, SORT_ASC, $totals);

        return $items;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function callback() {
        $paymentId = $_REQUEST['paymentId'];
        $orderId   = base64_decode($_REQUEST['orid']);

        if (empty($paymentId) || empty($orderId)) {
            $err = 'Ops, you are accessing wrong data';
            $this->log($err);

            $this->session->data['error'] = $err;
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        $this->addOrderHistory($orderId, $paymentId);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    public function addOrderHistory($orderId, $paymentId) {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($orderId);
        if ($order_info['order_status_id'] == $this->config->get($this->ocCode . '_order_status_id')) {
            $this->response->redirect($this->url->link('checkout/success', '', true));
        }

        $msgLog = "Order #$orderId ----- Get Payment Status";
        $json   = $this->mfObj->getPaymentStatus($paymentId, 'PaymentId', $orderId);
        if (!empty($order_info['payment_custom_field']['paymentId']) && $order_info['payment_custom_field']['paymentId'] == $json->focusTransaction->PaymentId) {
            $this->response->redirect($this->url->link('checkout/success', '', true));
                }
        $this->log("$msgLog - Result: " . json_encode($json->focusTransaction));
        $msg  = $this->orderHistoryNote($json->focusTransaction);
        $name = $this->id . ' (' . $json->focusTransaction->PaymentGateway . ')';
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET payment_method = '" . $this->db->escape($name) . "',payment_custom_field = '" . $this->db->escape(json_encode(array('paymentId' => $paymentId))) . "' WHERE order_id = '" . (int) $orderId . "'");

        if ($json->InvoiceStatus == 'Paid') {
            $this->model_checkout_order->addOrderHistory($orderId, $this->config->get($this->ocCode . '_order_status_id'), $msg, true, false);
            $this->response->redirect($this->url->link('checkout/success', '', true));
        } else {
            $this->model_checkout_order->addOrderHistory($orderId, $this->config->get($this->ocCode . '_failed_order_status_id'), $msg);
            $this->session->data['error'] = "Order #$orderId: $json->InvoiceError";
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    public function orderHistoryNote($data) {
        $note = '<b>MyFatoorah Payment Details:</b><br>';
        $note .= 'Gateway: ' . $data->PaymentGateway . '<br>';
        $note .= 'Transaction Status: ' . $data->TransactionStatus . '<br>';
        $note .= 'PaymentId: ' . $data->PaymentId . '<br>';
        $note .= 'AuthorizationId: ' . $data->AuthorizationId . '<br>';
        $note .= 'PaidCurrency: ' . $data->PaidCurrency . '<br>';
        $note .= empty($data->Error) ? '' : 'Error: ' . $data->Error . '<br>';
        return $note;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    protected function log($message) {
        if ($this->isLog) {
            $this->logger->write($message);
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
