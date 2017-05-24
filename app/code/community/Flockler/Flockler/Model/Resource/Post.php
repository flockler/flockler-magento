<?php

class Flockler_Flockler_Model_Resource_Post extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('flockler/post', 'flockler_post_id');
    }
}
