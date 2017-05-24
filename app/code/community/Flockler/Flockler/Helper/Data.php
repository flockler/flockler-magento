<?php

class Flockler_Flockler_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getBlogUrl()
    {
        return $this->_getUrl('blog');
    }

    public function convertTagsToArray($tagString) {
        if ($tagString == "") { return array(); } // Empty string returns empty array
        $tagString = str_replace(', ', ',', $tagString);
        $tagString = str_replace(' ,', ',', $tagString);
        $tagArray = explode(",", $tagString);
        return $tagArray;
    }
}
