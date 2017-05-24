<?php
class Flockler_Flockler_Adminhtml_Flockler_PostsController extends Mage_Adminhtml_Controller_Action
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

    public function massDeleteAction()
    {
        $posts = $this->getRequest()->getParam('flockler_posts');
        if(!is_array($posts)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('flockler_flockler')->__('Please select post(s).'));
        } else {
            try {
                $postModel = Mage::getModel('flockler/post');
                foreach ($posts as $post) {
                    $postModel->load($post)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('flockler_flockler')->__('Total of %d post(s) were deleted.', count($posts))
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
