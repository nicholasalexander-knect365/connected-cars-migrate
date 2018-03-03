<?php

/**
 * process a single post
 */
class PostProcess {
    public $post;

    public function setPost($post) {
        $this->post = $post;
    }
    
    public function getPost() {
        return $this->post;
    }

    private function composeUrl($href, $linkText, $target) {
        //var_dump($href, $linkText, $target);
        $urlScheme = parse_url($href, PHP_URL_SCHEME);
        $urlHost = parse_url($href, PHP_URL_HOST);
        $urlPath = parse_url($href, PHP_URL_PATH);
        $urlQuery = parse_url($href, PHP_URL_QUERY);
        $urlFragment = parse_url($href, PHP_URL_FRAGMENT);

        $localUrl = '<a href="' . $urlScheme . '://' . $urlHost . $urlPath . '" ' . $target . '>'. $linkText . '</a>';
        return $localUrl;        
    }

    public function localiseUrls() {

        $urlCount = preg_match_all('/<a href="([^>]*)">([^<]+)<\/a>/', $this->post, $matches);  

        $urls = $matches[0];
        $linkText = $matches[2];
        
        if ($urlCount) {
        
            foreach ($urls as $item => $url) {
                $target = '';
                $parts = explode($url, $this->post);
                $targets = preg_match('/target="([^"]+)"/', $url, $target);
                $hrefs = preg_match('/"([^"]+)"/', $url, $href);

                if (preg_match('/target=/', $href[1])) {
                    $arr = explode('target=', $href[1]);
                    $href[1] = $arr[0];
                }
                if ($targets) {
                    $target = 'target="' . $target[1] . '"';
                }

                if (is_array($target) && count($target===0)) {
                    $target = '';
                } else if (is_array($target)) {
                    $target = $target[1];
                }

                if ($hrefs) {
                    $localUrl = $this->composeUrl(trim($href[1]), trim($linkText[$item]), trim($target));
                    $this->post = $parts[0] . $localUrl . $parts[1];
                }
            }
        }
    }

    public function localiseImages() {

    }
}