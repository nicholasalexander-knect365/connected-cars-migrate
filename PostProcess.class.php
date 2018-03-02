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

    public function localiseUrls() {
//var_dump($this->post);
      $urlCount = preg_match_all('/<a href="([^>]*)">([^<]+)<\/a>/', $this->post, $matches);  

//var_dump($matches);

      $urls = $matches[0];
      $linkText = $matches[2];
//var_dump('URLs', $urls, $linkText);
      if ($urlCount) {
        foreach ($urls as $item => $url) {
            $target = '';
            $parts = explode($url, $this->post);
//var_dump('parts',$parts);
            $targets = preg_match('/target="([^"]+)"/', $url, $target);
// var_dump('TARGETs=',$targets);
// var_dump('TARGET=',$target);
            $hrefs = preg_match('/"([^"]+)"/', $url, $href);

            if (preg_match('/target=/', $href[1])) {
                $arr = explode('target=', $href[1]);
                $href[1] = $arr[0];
            }
//var_dump('HREF', $href);
            if ($targets) {
                $target = 'target="' . $target[1] . '"';
            }
            if ($hrefs) {
                $urlScheme = parse_url($href[1], PHP_URL_SCHEME);
                $urlHost = parse_url($href[1], PHP_URL_HOST);
                $urlPath = parse_url($href[1], PHP_URL_PATH);
                $urlQuery = parse_url($href[1], PHP_URL_QUERY);
                $urlFragment = parse_url($href[1], PHP_URL_FRAGMENT);
//var_dump($urlScheme, $urlHost, $urlPath, $urlQuery, $urlFragment);
                // $params = $href['query'];
// var_dump($item);
// var_dump($linkText[$item]);
//var_dump($target);
// var_dump($urlScheme);
// var_dump($urlHost);
// var_dump($urlPath);
                if (is_array($target) && count($target===0)) {
                    $target = '';
                } else {
                    $target = $target[1];
                }
                $localUrl = '<a href="' . $urlScheme . '://' . $urlHost . $urlPath . '" ' . $target . '>'. $linkText[$item] . '</a>';
            
                $this->post = $parts[0] . $localUrl . $parts[1];
            }
        }

//var_dump($this->post);

      }

    }

    public function localiseImages() {

    }
}