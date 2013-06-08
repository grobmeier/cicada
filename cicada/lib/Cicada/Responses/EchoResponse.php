<?php
namespace Cicada\Responses;

class EchoResponse implements Response {
    private $message;

    function __construct($message) {
        $this->message = $message;
    }

    public function serialize() {
        return $this->message;
    }
}