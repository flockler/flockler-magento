<?php

class Flockler_Flockler_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
      throw new Exception('404');
    }

    # for Load More in the Wall block/widget
    public function apiAction() {
        $this->getResponse()->setHeader('Content-Type', 'application/json; charset=utf-8', true);

        $url = Mage::getUrl("flockler/index/api");
        $request = $this->getRequest();
        $offset = (int)$request->getParam('offset');
        $count = (int)$request->getParam('count');
        $hookId = (int)$request->getParam('hook_id');
        $sectionId = (int)$request->getParam('section_id');
        $siteId = (int)$request->getParam('site_id');
        $tags = (int)$request->getParam('tags');
        $callback = $request->getParam('callback');
        $width = $request->getParam('width');

        $page = floor($offset / $count) + 1;

        $opts['count'] = $count;
        $opts['page'] = $page;
        $opts['hookId'] = $hookId;
        $opts['sectionId'] = $sectionId;
        $opts['siteId'] = $siteId;
        $opts['tags'] = Mage::helper('flockler_flockler')->convertTagsToArray($tags);

        $collection = Mage::getModel('flockler/post')->getPosts($opts);
        $totalCount = $collection->toArray()['totalRecords'];

        $response['articles'] = array();
        $i = 0;
        foreach ($collection->toArray()['items'] as $post) {
            array_push($response['articles'], Mage::getModel('flockler/post')->convertPostToHTML($post, $width));
            $i++;
            if ($i >= $count) { break; }
        }

        if (($offset + $count) < $totalCount) {
            $width = $this->getRequest()->getParam('width');
            $tags = $this->getRequest()->getParam('tags');

            $response['pagination']['older'] = $url . "?count=".$count .
                "&offset=" . ($offset+$count) .
                "&width=" . $width .
                ($opts['hookId'] == null ? "" : "&hook_id=" . $opts['hookId']) .
                ($opts['sectionId'] == null ? "" : "&section_id=" . $opts['sectionId']) .
                ($opts['siteId'] == null ? "" : "&site_id=" . $opts['siteId']) .
                ($opts['tags'] == null ? "" : "&tags=" . $tags);
        } else {
            $response['pagination']['older'] = null;
        }

        $json = json_encode($response);
        $this->getResponse()->setBody("{$callback}({$json})");
    }

}
