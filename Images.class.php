<?php

class Images {
    public $url;
    public $docId;

    public function __construct($docId) {
        $this->docId = $docId;
    }

    public function getUrl() {
        return $this->url;
    }
    public function setUrl($url) {
        $this->url = $url;
    }
}