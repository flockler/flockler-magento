<?php
class Flockler_Flockler_Adminhtml_Flockler_WebhooksController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function showAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create new Webhook
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit Webhook
     */
    public function editAction()
    {
        $this->_title($this->__('Flockler Webhooks'))
             ->_title($this->__('Manage Webhooks'));

        // 1. instance webhook model
        $model = Mage::getModel('flockler/webhook');

        // 2. if ID exists, check it and load data
        $webhookId = $this->getRequest()->getParam('id');
        if ($webhookId) {
            $model->load($webhookId);
            if (!$model->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('flockler_flockler')->__('Webhook does not exist.')
                );
                return $this->_redirect('*/*/');
            }
            $this->_title($model->getTitle());
        } else {
            $this->_title(Mage::helper('flockler_flockler')->__('New Webhook'));

            // Webhook URL generation
            try {
                $webhookUrl = Mage::app()->getStore(1)->getBaseUrl() . "flockler/webhook?flockler-hook=" . substr(hash('sha256', rand()), 0, 32);
            } catch(Exception $e) {
                $webhookUrl = Mage::getUrl('flockler') . "webhook?flockler-hook=" . substr(hash('sha256', rand()), 0, 32);
            }
            $model->setWebhookid($webhookUrl);

            // Webhook default name
            $model->setName("Default Webhook");
        }

        $this->loadLayout();

        // 3. Set entered data if there was an error during save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        // 4. Register model ($news_item) to use later in blocks
        Mage::register('flockler_webhook', $model);

        // 5. render layout
        $this->renderLayout();

    }

    // ----------

    public function saveAction()
    {
        $redirectPath   = '*/*';
        $redirectParams = array();

        // check if data sent
        $data = $this->getRequest()->getPost();

        if ($data) {
            // init model and set data
            $model = Mage::getModel('flockler/webhook');

            // if webhook exists, try to load it
            $webhookId = $this->getRequest()->getParam('flockler_webhook_id');
            if ($webhookId) {
                $model->load($webhookId);
            }
            $model->addData($data);

            try {
                $hasError = false;
                $model->save();

                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('flockler_flockler')->__('The webhook has been saved.')
                );

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $redirectPath   = '*/*/edit';
                    $redirectParams = array('id' => $model->getId());
                }
            } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('flockler_flockler')->__('An error occurred while saving the webhook.')
                );
            }

            if ($hasError) {
                $this->_getSession()->setFormData($data);
                $redirectPath = '*/*/edit';
                $redirectParams = array('id' => $this->getRequest()->getParam('flockler_webhook_id'));
            }
        }

        $this->_redirect($redirectPath, $redirectParams);
    }

    public function deleteAction()
    {
        // check if we know what should be deleted
        $itemId = $this->getRequest()->getParam('id');
        if ($itemId) {
            try {
                // init model and delete
                $model = Mage::getModel('flockler/webhook');
                $model->load($itemId);
                if (!$model->getData('flockler_webhook_id')) {
                    Mage::throwException(Mage::helper('flockler_flockler')->__('Unable to find a webhook.'));
                }
                $model->delete();

                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('flockler_flockler')->__('The webhook has been deleted.')
                );

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('flockler_flockler')->__('An error occurred while deleting the webhook.')
                );
            }
        }
        // go to grid
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $webhooks = $this->getRequest()->getParam('flockler_webhooks');
        if(!is_array($webhooks)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('flockler_flockler')->__('Please select webhook(s).'));
        } else {
            try {
                $webhookModel = Mage::getModel('flockler/webhook');
                foreach ($webhooks as $webhook) {
                    $webhookModel->load($webhook)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('flockler_flockler')->__('Total of %d webhook(s) were deleted.', count($webhooks))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('flockler_settings');
    }
}
