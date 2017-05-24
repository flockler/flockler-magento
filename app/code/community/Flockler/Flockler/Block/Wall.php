<?php

class Flockler_Flockler_Block_Wall extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{

    const FLOCKLER_WALL_DEFAULT_COUNT = 8;
    const FLOCKLER_WALL_DEFAULT_WIDTH = '346';
    const FLOCKLER_WALL_DEFAULT_SHOW_LOAD_MORE = true;

    public $_count = self::FLOCKLER_WALL_DEFAULT_COUNT;
    public $_width = self::FLOCKLER_WALL_DEFAULT_WIDTH;
    public $_showLoadMore = self::FLOCKLER_WALL_DEFAULT_SHOW_LOAD_MORE;

    public function setShowLoadMore($boolean) {
        $this->_showLoadMore = $boolean;
    }

    protected function _toHtml() {
        $product = Mage::registry('product');
        $category = Mage::registry('current_category');
        $criteria = array();

        if ($this->isWidget()) {
            // Define wall layout settings (not defined in layout.xml)
            if (!is_null($this->getData('item_count'))) {
                $this->_count = (int)$this->getData('item_count');
            }
            if (!is_null($this->getData('item_width'))) {
                $this->_width = $this->getData('item_width');
            }
            if ($this->getData('show_load_more') == "false") {
                $this->_showLoadMore = false;
            }

            $this->addCriteria($criteria, $this, '');
        } else if ($product) {
            // Filter by product
            $this->addCriteria($criteria, $product);

            // Use category wall if no product wall defined
            if (is_null($criteria['sectionId']) && is_null($criteria['siteId']) && empty($criteria['tags'])) {
                $productCategories = $this->loadProductCategories($product);
                foreach ($productCategories as $category) {
                    $this->addCriteria($criteria, $category);
                    $this->addCategoryNameTagToCriteria($criteria, $category);
                }
            }
        } else if ($category) {
            // Filter by category
            $this->addCriteria($criteria, $category);
            $this->addCategoryNameTagToCriteria($criteria, $category);
        }

        $criteria['count'] = $this->_count;

        $posts = Mage::getModel('flockler/post')->getPosts($criteria);

        // Pass parameters from wall-settings to JS-Wall
        $count = $this->_count;
        $width = $this->_width;
        $showLoadMore = (bool)$this->_showLoadMore;
        $site = $criteria['siteId'];
        $section = $criteria['sectionId'];
        $tags = implode($criteria['tags'], ",");
        $url = Mage::getUrl("flockler/index/api");

        // Convert posts to wallHTML
        $wallHTML = "";
        foreach ($posts->toArray()['items'] as $post) {
            $wallHTML .= Mage::getModel('flockler/post')->convertPostToHTML($post, $width);
        }
        $loadMoreText = Mage::helper('flockler_flockler')->__('Load more');
        $loadMoreButton = ($posts->getSize() > $count && $showLoadMore) ? '<button id="loadmore" class="flockler-wall__load-more-btn">'.$loadMoreText.'</button>' : '';

        return
<<< EOS
              <div class="flockler-wall-widget-container">
                  <div class="flockler-wall"
                        data-flockler-item-count="{$count}"
                        data-flockler-site-id="{$site}"
                        data-flockler-section-id="{$section}"
                        data-flockler-tags="{$tags}"
                        data-flockler-url="{$url}"
                        data-flockler-item-width={$width}>
                    <div class="flockler-wall__items">
                      {$wallHTML}
                    </div>
                    <div class="flockler-wall__load-more">
                        {$loadMoreButton}
                    </div>
                  </div>
              </div>
EOS;

    }

    public function isWidget() {
        $siteIsNull = is_null($this->getData('site_id'));
        $sectionIsNull = is_null($this->getData('section_id'));
        $tagsIsNull = is_null($this->getData('tags_id'));
        $isNotWidget = $siteIsNull && $sectionIsNull && $tagsIsNull;
        return !$isNotWidget;
    }

    private function addCategoryNameTagToCriteria($criteria, $category) {
        // Use category as tag if not explicitly disabled
        if ($category->getData('flockler_disable_name_tag') != 1) {
            array_push($criteria['tags'], strtolower($category->getName()));
        }
        return $criteria;
    }

    private function addCriteria(&$criteria, $item, $prefix = 'flockler_') {
        $criteria['sectionId'] = $item->getData($prefix .'section_id');
        $criteria['siteId'] = $item->getData($prefix .'site_id');

        $tags = $item->getData($prefix .'tags');

        if (!empty($tags)) {
            $criteria['tags'] = $this->convertTagsToArray($tags);
        }
    }

    private function convertTagsToArray($tagString) {
        return Mage::helper('flockler_flockler')->convertTagsToArray($tagString);
    }

    private function loadProductCategories($product) {
        $categories = array();
        $categoryCollection = $product->getCategoryCollection();
        foreach ($categoryCollection as $categoryModel) {
            $category = Mage::getModel('catalog/category')->load($categoryModel->getId());
            array_push($categories, $category);
        }
        return $categories;
    }

}
