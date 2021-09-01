<?php

require_once 'myfatoorah_controller.php';

class ControllerPaymentMyFatoorahDirect extends MyfatoorahController {

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function index() {
        $id = 'myfatoorah_direct';

        // Set default values for fields
        $fields = [
            'apiKey',
            'status', 'test', 'webhook_secret_key','sort_order', 'debug', 'initial_order_status_id', 'order_status_id', 'failed_order_status_id'
        ];

        $this->loadAdmin($id, $fields);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
