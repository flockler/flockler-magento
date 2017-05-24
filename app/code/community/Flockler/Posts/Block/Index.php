<?php

require_once(Mage::getBaseDir('lib') . '/Flockler/parser.inc.php');

class Flockler_Posts_Block_Index extends Mage_Core_Block_Template {
    private $COUNT = 20;

    public function posts() {
        $page = (int)Mage::app()->getRequest()->getParam('page');
        $sectionId = Mage::getStoreConfig('flockler_settings/blog/section_id');

        if (empty($page)) {
            $page = 1;
        }

        $collection = Mage::getModel('flockler/post')->getCollection()
            ->setPageSize($this->COUNT)
            ->setCurPage($page)
            ->setOrder('published_at', 'DESC');

        if (!empty($sectionId)) {
            $collection->addFieldToFilter('section_id', $sectionId);
        }

        return $collection;
    }

    public function hasMore() {
        $totalCount = $this->posts()->getSize();
        return $totalCount > $this->COUNT;
    }

    public function pages() {
        $count = Mage::getModel('flockler/post')->getCollection()
            ->getSize();

        return array_fill(0, ceil($count / $this->COUNT), array());
    }

    public function blogBaseUrl() {
        return Mage::getUrl("blog");
    }

    public function localPermalink($post) {
        return Mage::getUrl("blog/index/show", array('id' => $post['flockler_post_id']));
    }

    public function attachments($post) {
        return json_decode($post->getAttachments(), true);
    }

    public function linkifyFacebook($html) {
        return $this->linkify(html);
    }

    public function linkifyInstagram($html) {
        // hashtags
        $newHtml = preg_replace("/#([A-Za-z0-9_]*)/", "<a href=\"https://www.instagram.com/explore/tags/$1/\" target=\"_blank\">#$1</a>", $html);

        // @foo
        $newHtml = preg_replace("/@([A-Za-z0-9\/\._]*)/", "<a href=\"https://www.instagram.com/$1\">@$1</a>", $newHtml);

        return $this->linkify($newHtml);
    }

    public function linkifyTweet($html) {
        // hashtags
        $newHtml = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a href=\"http://twitter.com/search?q=$1\" target=\"_blank\">#$1</a>", $html);

        // @foo
        $newHtml = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a href=\"http://www.twitter.com/$1\">@$1</a>", $newHtml);

        return $this->linkify($newHtml);
    }

    public function linkify($html) {
        return preg_replace("/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/", "<a href=\"$1\" target=\"_blank\">$1</a>", $html);
    }

    public function snippet($post, $limit = 140) {
        if ($post['summary']) {
            return $post['summary'];
        } else {
            $permalink = $this->localPermalink($post);
            $snippet = $post['body'];

            // Add space before each < so content is not "bunched up" after strip_tags
            $snippet = str_replace('<', ' <', $snippet);
            $snippet = strip_tags($snippet);
            $snippet = str_replace('  ', ' ', $snippet);
            $snippet = htmlspecialchars_decode($snippet);
            $snippet = trim($snippet, " \t\n\r\0\x0B\xc2\xa0\xA0");

            if (mb_strlen($snippet) > $limit) {
                $snippet = mb_substr($snippet, 0, $limit);
                $snippet = trim($snippet, " \t\n\r\0\x0B\xc2\xa0\xA0");
                $loadMoreText = Mage::helper('flockler_flockler')->__('Read more');
                $snippet .= "&hellip; <a href=\"$permalink\">$loadMoreText</a>";
            }

            return $snippet;
        }
    }
}
