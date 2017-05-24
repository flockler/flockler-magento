<?php

require_once(Mage::getBaseDir('lib') . '/Flockler/parser.inc.php');

define('FLOCKLER_DESIGN_PATH', Mage::getBaseDir('design'));
define('FLOCKLER_TEMPLATES', FLOCKLER_DESIGN_PATH . "/frontend/base/default/template/flockler/flockler");
define('FLOCKLER_WALL_TEMPLATES', FLOCKLER_TEMPLATES . "/includes");
define('FLOCKLER_POST_TYPE_NAME', 'flockler_post');

class Flockler_Flockler_Model_Post extends Mage_Core_Model_Abstract {
    private $DEFAULT_COUNT = 20;

    protected function _construct() {
        $this->_init('flockler/post');
    }

    public function setOrigData($key = '', $index = null) {
        if (is_string($this['attachments'])) {
            $this['attachments'] = json_decode($this['attachments'], true);
        }

        return parent::setOrigData($key, $index);
    }

    public function getPosts($opts) {
        $count = (!empty((int)$opts['count']) ? (int)$opts['count'] : $this->DEFAULT_COUNT);
        $collection = $this->getCollection()
            ->setPageSize($count)
            ->setOrder('published_at', 'DESC');

        if (!empty($opts['page'])) {
            $collection->setCurPage($opts['page']);
        }

        if (!empty($opts['sectionId'])) {
            $collection->addFieldToFilter('section_id', $opts['sectionId']);
        }

        if (!empty($opts['siteId'])) {
            $collection->addFieldToFilter('site_id', $opts['siteId']);
        }

        if (!empty($opts['hookId'])) {
            $collection->addFieldToFilter('hook_id', $opts['hookId']);
        }

        if (!empty($opts['tags'])) {
            $likeCondition = array();
            foreach ($opts['tags'] as $tag) {
                $likeCondition = array_merge($likeCondition, array(
                    array('like' => $tag),                // First and only tag
                    array('like' => $tag . ',%'),         // First tag
                    array('like' => '%, ' . $tag . ',%'), // Tag in the middle
                    array('like' => '%,' . $tag . ',%'),  // Tag in the middle without whitespace
                    array('like' => '%, ' . $tag),        // Last tag
                    array('like' => '%,' . $tag),         // Last tag without whitespace
                ));
            }
            $collection->addFieldToFilter('tags', $likeCondition);
        }

        return $collection;
    }

    public function convertPostToHTML($item, $width_attr) {

        // Detect width_attr type: pixels, percents or null
        $width = null;
        if (preg_match('/^\d+$/', $width_attr)) {
            $width = $width_attr . 'px';
        } else if (preg_match('/^\d+(\.\d+)?%$/', $width_attr)) {
            $width = $width_attr;
        } else if ($width_attr === '') {
            $width = '346';
        }
        $itemWidth = "style='width: " . $width . "; min-width: 300px'";

        $templateType = $item['type'];

        if ($item['type'] == 'link' && empty($item['attachments']) && empty($item['attachments']['link'])) {
            $templateType = 'link_old';
        }

        ob_start();
        require(FLOCKLER_WALL_TEMPLATES . '/' . $templateType . '.phtml');
        $postHtml = ob_get_contents();
        ob_end_clean();

        return $postHtml;
    }

    public function get_parser($text, $summary = false) {
        return new Flockler_Stream_Post_Parser($text, $summary);
    }

    public function getRowTags() {
        $tags = $this->getData('tags');
        return str_replace(',', ', ', $tags);
    }
}
