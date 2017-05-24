<?php

class Flockler_Flockler_Block_Adminhtml_Flockler_Webhooks_Edit extends Mage_adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'flockler_flockler';
        $this->_controller = 'adminhtml_flockler_webhooks';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('flockler_flockler')->__('Save Webhook'));

        $this->_updateButton('delete', 'label', Mage::helper('flockler_flockler')->__('Delete Webhook'));

    }

    public function getHeaderText()
    {
        $model = Mage::registry('flockler_webhook');
        if ($model->getId()) {
            return Mage::helper('flockler_flockler')->__("Edit Webhook",
                    $this->escapeHtml($model->getTitle()));
        } else {
            return Mage::helper('flockler_flockler')->__('New Webhook');
        }
    }
}
