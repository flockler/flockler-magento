<?php

class Flockler_Flockler_Block_Adminhtml_Flockler_Posts extends Mage_adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'flockler_flockler';
        $this->_controller = 'adminhtml_flockler_posts';
        $this->_headerText = Mage::helper('flockler_flockler')->__('Flockler Posts');

        parent::__construct();
        $this->_removeButton('add');
    }

}
