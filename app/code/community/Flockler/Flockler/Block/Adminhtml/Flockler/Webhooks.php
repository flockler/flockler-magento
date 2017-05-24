<?php

class Flockler_Flockler_Block_Adminhtml_Flockler_Webhooks extends Mage_adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'flockler_flockler';
        $this->_controller = 'adminhtml_flockler_webhooks';
        $this->_headerText = Mage::helper('flockler_flockler')->__('Flockler Webhooks');

        parent::__construct();
        $this->_updateButton('add', 'label', Mage::helper('flockler_flockler')->__('Add New Webhook'));
    }

}
