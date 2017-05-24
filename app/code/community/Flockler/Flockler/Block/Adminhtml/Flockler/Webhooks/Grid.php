<?php

class Flockler_Flockler_Block_Adminhtml_Flockler_Webhooks_Grid extends Mage_adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('flockler_webhooks_list_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()

    {
        $collection = Mage::getModel('flockler/webhook')->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header' => Mage::helper('flockler_flockler')->__('Webhook name'),
            'index'  => 'name',
        ));

        $this->addColumn('webhookid', array(
            'header' => Mage::helper('flockler_flockler')->__('Webhook URL'),
            'index' => 'webhookid',
            'width' => '300px',
        ));

        $this->addColumn('secret', array(
            'header' => Mage::helper('flockler_flockler')->__('Secret key'),
            'index' => 'secret',
            'width' => '230px',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('flockler_webhook_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('flockler_webhooks');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('flockler_flockler')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => Mage::helper('flockler_flockler')->__('Are you sure?')
        ));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
