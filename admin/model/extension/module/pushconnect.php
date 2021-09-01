<?php

class ModelExtensionModulePushConnect extends Model {

    public function getSettings() {
        return $this->db->query("SELECT * from " . DB_PREFIX . "settings where code = 'pushconnect'")->rows;
    }
    
}
    