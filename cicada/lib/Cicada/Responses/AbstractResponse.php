<?php

namespace Cicada\Responses;

abstract class AbstractResponse implements Response {

    protected $headers = array();

    public function headers() {
        return $this->headers;
    }

    public function addHeader($header) {
        array_push($this->header, $header);
    }

    public abstract function serialize();
}