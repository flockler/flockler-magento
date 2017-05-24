<?php

class Flockler_Posts_IndexController extends Mage_Core_Controller_Front_Action {
    # /blog
    public function indexAction() {
      $this->loadLayout();
      $this->renderLayout();
    }

    # /blog/some-post-blog-post
    public function showAction() {
        $id = $this->getRequest()->getParams('id');
        $post = Mage::getModel('flockler/post')->load($id);
        Mage::register('current_post', $post);

        $this->loadLayout();
        $this->renderLayout();
    }

    private function convertTagsToArray($tagString) {
        return Mage::helper('flockler_flockler')->convertTagsToArray($tagString);
    }
}
