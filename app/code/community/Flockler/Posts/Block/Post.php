<?php

class Flockler_Posts_Block_Post extends Mage_Core_Block_Template
{
    public function renderView()
    {
        $this->assign('post', Mage::registry('current_post'));
        return parent::renderView();
    }
}
