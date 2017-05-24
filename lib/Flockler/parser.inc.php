<?php

class Flockler_Stream_Post_Parser {

    private $val;
    private $is_summary;
    private $generated_summary;

    public function __construct($str, $summary = false) {
        if ($summary === false) {
            $this->val = $str;
            $this->is_summary = false;
        } else {
            $this->is_summary = true;
            if (empty($summary)) {
                $this->val = $str;
            } else {
                $this->val = $summary;
            }
        }

        return $this;
    }

    public function parseURL() {
        $this->val = preg_replace_callback('/\b[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+(?=([\s\b<]|$))/', array($this, 'makeURLLink'), $this->val);
        return $this;
    }

    public function parseTUsername() {
        $this->val = preg_replace_callback('/@[A-Za-z0-9_]+/', array($this, 'makeTwitterUserLink'), $this->val);
      return $this;
    }

    public function parseTHashtag() {
        $this->val = preg_replace_callback('/#[\w\d_]+/u', array($this, 'makeTwitterHashtagLink'), $this->val);
        return $this;
    }

    public function parseIUsername() {
        $this->val = preg_replace_callback('/@[A-Za-z0-9_\.]+/', array($this, 'makeInstagramUserLink'), $this->val);
        return $this;
    }

    public function parseIHashtag() {
        $this->val = preg_replace_callback('/#[^#\s\.\-]+/u', array($this, 'makeInstagramHashtagLink'), $this->val);
        return $this;
    }

    public function parseFBHashtag() {
        $this->val = preg_replace_callback('/#[\w\d_]+/u', array($this, 'makeFacebookHashtagLink'), $this->val);
        return $this;
    }

    public function parseNewLines() {
        $this->val = preg_replace('/\n/', '<br />', $this->val);
          return $this;
    }

    public function useThumbnail() {
        $thumb_pattern = "//flockler.com/thumbs/$1/$2_s400x0_q70_noupscale.$3";
        $this->val = preg_replace('/\/\/flockler\.com\/thumbs\/(\d+)\/([^\/\.]+)_s\d+x\d+\w*\.(\w+)$/', $thumb_pattern, $this->val);
        $this->val = preg_replace('/\/\/flockler\.com\/files\/(\d+)\/([^\/\.]+)\.(\w+)$/', $thumb_pattern, $this->val);
          return $this;
    }

    public function translate($context, $args = array()) {
        // Simple substitution translation for now
        foreach ($args as $key => $value) {
            $this->val = implode($value, explode($key,$this->val));
        };

        return $this;
    }

    public function makeSummary($permalink = null, $new_window = false) {
        if (extension_loaded('libxml')) {
            $dom = new DOMDocument('1.0', 'UTF-8');

            if (@$dom->loadHTML($this->convertTextForDOMFunctions(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {

                // Create a link element to be injected to the last paragraph of the excerpt
                $readMoreText = Mage::helper('flockler_flockler')->__('Read more');
                $perma_elem = $dom->createElement('a', $readMoreText);
                $perma_elem->setAttribute('class', 'flockler-wall-item__read-more-inline');
                $perma_elem->setAttribute('href', $permalink);
                if ($new_window === true) {
                    $perma_elem->setAttribute('target', '_blank');
                }

                $space_node = $dom->createTextNode(' ');

                $tmp = str_replace("  ", " ", strip_tags($this->val));

                if ( str_word_count($tmp) > 51 ) {
                  // Have to take 51 because the last one contains rest of the string :(
                  $summary_arr = explode(" ", $tmp, 51);
                  $seqStr = array_slice($summary_arr, 0, 49);
                  $seqStr = implode(" ", $seqStr) . '...';
                  $new_summary = $dom->createElement('div', $seqStr);
                } else {
                  $new_summary = $dom->createElement('div', $tmp);
                }

                // Locate all paragraph elements
                $paragraphs = $dom->getElementsByTagName('p');

                if ($this->generated_summary) {
                    // Display only the first paragraph and truncate away the rest. (Parser should be parsing the full body.)
                    if ($paragraphs->length > 1) {
                        // There were more than one paragraph, so rip off the first paragraph,
                        // inject the link and push it into a fresh DOM

                        $summary = $dom->createElement('div');
                        $summary->appendChild($new_summary);
                        $summary->appendChild($space_node);
                        $summary->appendChild($perma_elem);

                        $new_dom = new DOMDocument('1.0', 'UTF-8');
                        $new_node = $new_dom->importNode($summary, true);

                        $new_dom->appendChild($new_node);

                        $dom = $new_dom;
                    } else {
                        // Only one paragraph was found. We don't need a read more link, so nothing needs to be done.
                    }
                } else {
                    // Display all content as well as the read more link. (Parser should be parsing a summary.)
                    $paragraphs->item($paragraphs->length - 1)->appendChild($space_node);
                    $paragraphs->item($paragraphs->length - 1)->appendChild($perma_elem);
                }

                $this->val = $dom->saveHTML();
            }
        } else {
            // Since our dependency was not met, do nothing and display the whole article.
            // libxml is pretty widely used, so this only concerns a few installations that
            // have it explicitly disabled.
        }

        return $this;
    }

    public function done() {
        return $this->val;
    }

    private function makeURLLink($matches) {
        return '<a href="' . $matches[0] . '" target="_blank">' . $matches[0] . '</a>';
    }

    private function makeTwitterUserLink($matches) {
        return '<a href="https://twitter.com/' . str_replace('@','', $matches[0]) . '" target="_blank">' . $matches[0] . '</a>';
    }

    private function makeTwitterHashtagLink($matches) {
        return '<a href="https://twitter.com/search?q=' . str_replace('#','%23', $matches[0]) . '" target="_blank">' . $matches[0] . '</a>';
    }

    private function makeInstagramUserLink($matches) {
        return '<a href="https://instagram.com/' . str_replace('@','', $matches[0]) . '" target="_blank">' . $matches[0] . '</a>';
    }

    private function makeInstagramHashtagLink($matches) {
        return '<a href="https://instagram.com/explore/tags/' . str_replace('#','', $matches[0]) . '" target="_blank">' . $matches[0] . '</a>';
    }

    private function makeFacebookHashtagLink($matches) {
        return '<a href="https://facebook.com/hashtag/' . str_replace('#','', $matches[0]) . '" target="_blank">' . $matches[0] . '</a>';
    }

    private function convertTextForDOMFunctions() {
        if (extension_loaded('mbstring')) {
            return mb_convert_encoding($this->val, 'HTML-ENTITIES', 'UTF-8');
        } else {
            // Dedicated library is not available; attempt to encode UTF-8 to HTML codepoint entities manually.
            return preg_replace_callback('/([\xC0-\xDF][\x80-\xBF]'
                                        . '|[\xE0-\xEF][\x80-\xBF]{2}'
                                        . '|[\xF0-\xF7][\x80-\xBF]{3}'
                                        . '|[\xF8-\xFB][\x80-\xBF]{4})/',
                                         array($this, 'encodeCodepoint'), $this->val);
        }
    }

    private function encodeCodepoint($matches) {
        // based on a user comment on the PHP manual:
        // http://php.net/manual/en/function.ord.php#109812

        $code = ord(substr($matches[0], 0, 1));
        if ($code >= 128) {             //otherwise 0xxxxxxx
            if ($code < 224) {
                $bytesnumber = 2;       //110xxxxx
            } else if ($code < 240) {
                $bytesnumber = 3;       //1110xxxx
            } else if ($code < 248) {
                $bytesnumber = 4;       //11110xxx
            } else {
                // 5- and 6-byte codepoints are not used, so this is invalid
                return '';
            }

            $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
            $offset = 0;
            for ($i = 2; $i <= $bytesnumber; $i++) {
                $offset++;
                $code2 = ord(substr($matches[0], $offset, 1)) - 128;        //10xxxxxx
                $codetemp = $codetemp * 64 + $code2;
            }
            $code = $codetemp;
        } else {
            return $matches[0];
        }

        return '&#' . $code . ';';
    }
}
