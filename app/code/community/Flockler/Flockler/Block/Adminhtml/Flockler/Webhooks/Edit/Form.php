<?php

class Flockler_Flockler_Block_Adminhtml_Flockler_Webhooks_Edit_Form extends Mage_adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $form->setUseContainer(true);

        // Fieldset
        $fieldset = $form->addFieldset(
            'base_fieldset',
            array(
                'legend' => Mage::helper('flockler_flockler')->__('Webhook details'),
            )
        );

        $model = Mage::registry('flockler_webhook');

        if ($model->getId()) {
            $fieldset->addField('flockler_webhook_id', 'hidden', array(
                'name' => 'flockler_webhook_id',
            ));
        }

        // Name
        $fieldset->addField('name', 'text',
            array(
                'name'     => 'name',
                'required' => true,
                'label'    => Mage::helper('flockler_flockler')->__('Name'),
                'class'    => 'required-entry',
                'style'     => 'width: 600px;',
            )
        );

        // Webhook ID
        $fieldset->addField('webhookid', 'text',
            array(
                'name'     => 'webhookid',
                'label'    => Mage::helper('flockler_flockler')->__('Webhook URL'),
                'style'     => 'width: 600px;',
            )
        );

        // Secret
        $fieldset->addField('secret', 'text',
            array(
                'name'     => 'secret',
                'required' => false,
                'label'    => Mage::helper('flockler_flockler')->__('Secret'),
                'style'     => 'width: 600px;',
            )
        );

        // Load existing data
        if ($model) {
            $form->setValues($model->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

}
