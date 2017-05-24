<?php

define('FLOCKLER_DB_LOCK_NAME', 'magento_flockler_plugin_lock');

class Flockler_Flockler_WebhookController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        if (!$this->validWebhookId()) {
            $this->setResponse(400, 'invalid Webhook ID');
        } else if (!isset($_SERVER['HTTP_X_FLOCKLER_ENV'])) {
            $this->setResponse(400, 'environment header is missing');
        } elseif (!isset($_SERVER['HTTP_X_FLOCKLER_ACTION'])) {
            $this->setResponse(400, 'action header is missing');
        } elseif (!isset($_SERVER['HTTP_X_FLOCKLER_SIGNATURE'])) {
            $this->setResponse(400, 'signature is missing');
        } elseif (!$this->validSignature()) {
            $this->setResponse(400, 'invalid signature');
        } else {
            $data = $this->getPostPayloadJSON();
            $action = $data['action'];

            $lockStatus = $this->acquireDbLock();
            if ($lockStatus !== "1") {
                // Lock timed out or could not be obtained because of some kind of error
                $this->setResponse(500, 'could not acquire database lock');
            }

            $post = Mage::getModel('flockler/post')->load($data['article']['id'], 'article_id');
            $postExists = !is_null($post->getId());

            if ($action == 'publish' && $postExists) {
            // We might get a publish event for already existing, so let's update it instead!
            $action = 'update';
            } else if ($action == 'update' && !$postExists) {
            // We might get an update event for non-existing, so let's publish it instead!
            $action = 'publish';
            }

            switch ($action) {
                case 'publish':
                    $this->publish($data);
                    break;

                case 'update':
                    $this->update($post, $data);
                    break;

                case 'unpublish':
                    if ($postExists) {
                        $this->delete($post);
                    } else {
                        $this->releaseDbLock();
                        $this->setResponse(400, 'post not found: { article_id: ' . $data['article']['id'] . '}');
                    }
                    break;

                default:
                    $this->releaseDbLock();
                    $this->setResponse(400, 'unknown action');
            }
        }
    }


    /**
     * Validation
     */

    private function validSignature() {
        $request = $this->getRequest();

        // Signature from server
        $server_signature = $_SERVER['HTTP_X_FLOCKLER_SIGNATURE'];

        // Find webhook
        $flocklerHook = $request->getParam('flockler-hook');
        $webhookID = Mage::getUrl("flockler") . "webhook?flockler-hook=" . $flocklerHook;
        $webhook = Mage::getModel('flockler/webhook')->load($webhookID, 'webhookid');
        $url = $webhook->getWebhookid();

        // Signature created from request
        $payload = $request->getRawBody();
        $http_verb = $_SERVER['REQUEST_METHOD'];
        $action = $_SERVER['HTTP_X_FLOCKLER_ACTION'];
        $env = $_SERVER['HTTP_X_FLOCKLER_ENV'];
        $str_to_sign = mb_strtolower(implode('|', array(
          $http_verb,
          $action,
          $env,
          $url,
          $payload
        )));

        // Retrieve webhook secret key
        $secret = $webhook->getSecret();

        $created_signature = base64_encode(hash_hmac('sha256', $str_to_sign, $secret, true));

        return $server_signature == $created_signature;
    }

    private function validWebhookId() {
        $webhookIsValid = false;
        $flocklerHook = $this->getRequest()->getParam('flockler-hook');
        $webhookID = Mage::getUrl("flockler") . "webhook?flockler-hook=" . $flocklerHook;

        $webhooks = Mage::getModel('flockler/webhook')->getCollection();
        foreach ($webhooks as $hook) {
            if ($hook->getWebhookid() == $webhookID) {
                $webhookIsValid = true;
            }
        }

        return $webhookIsValid;
    }



    /**
     * Actions
     */

    private function publish($data) {
        $post = Mage::getModel('flockler/post');
        $post = $this->updatePostData($post, $data);
        $post->save();

        $this->releaseDbLock();
        $this->setResponse(200, 'added ID ' . $post->getId());
        $lockStatus = $this->releaseDbLock();
    }

    private function update($post, $data) {
        $post = $this->updatePostData($post, $data);
        $post->save();
        $this->releaseDbLock();
        $this->setResponse(200, 'updated ID ' . $post->getId());
    }

    private function delete($post) {
        $id = $post->getId();
        $post->delete();
        $this->releaseDbLock();
        $this->setResponse(200, 'deleted ID ' . $post->getId());
    }

    private function updatePostData($post, $data) {
        $post->setArticleId($data['article']['id']);
        $post->setArticleUrl($data['article']['article_url']);
        $post->setAuthor($data['article']['author']);
        $post->setBody($data['article']['body']);
        $post->setCoverPosX($data['article']['cover_pos_x']);
        $post->setCoverPosY($data['article']['cover_pos_y']);
        $post->setCoverUrl($data['article']['cover_url']);
        $post->setDisplayStyle($data['article']['display_style']);
        $post->setPinnedIndex($data['article']['pinned_index']);
        $post->setPublishedAt($data['article']['published_at']);
        $post->setPublishedAtL10n($data['article']['published_at_l10n']);
        $post->setSectionId($data['article']['section_id']);
        $post->setSiteId($data['article']['site_id']);
        $post->setSitePinnedIndex($data['article']['site_pinned_index']);
        $post->setSiteOrderNumber($data['article']['site_order_number']);
        $post->setSourceUrl($data['article']['source_url']);
        $post->setSummary($data['article']['summary']);
        $post->setTags(strtolower(implode(",", $data['article']['tags'])));
        $post->setTitle($data['article']['title']);
        $post->setType($data['article']['type']);
        $post->setUrl($data['article']['url']);
        $post->setAttachments(json_encode($data['article']['attachments']));

        return $post;
    }


    /**
     * Helpers
     */

    private function getPostPayloadJSON() {
        $request = $this->getRequest();
        $payload = $request->getRawBody();
        $payload_json = json_decode($payload, true);
        return $payload_json;
    }

    private function setResponse($code, $msg) {
        $data['status'] = $code;
        $response = $this->getResponse();

        $response->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $response->setHttpResponseCode($code);

        $response->setBody(json_encode(array(
          'code' => $code,
          'message' => $msg
        )));
    }

    private function acquireDbLock() {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $conn->fetchOne("SELECT GET_LOCK('" . FLOCKLER_DB_LOCK_NAME . "', 10)");
    }

    private function releaseDbLock() {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $conn->fetchOne("SELECT RELEASE_LOCK('" . FLOCKLER_DB_LOCK_NAME . "')");
    }
}
