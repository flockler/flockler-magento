<?php

class Flockler_Flockler_Model_Resource_Webhook extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('flockler/webhook', 'flockler_webhook_id');
    }
}
