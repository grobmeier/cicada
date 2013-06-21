<?php
namespace Cicada\Responses;

class EchoResponse extends AbstractResponse {
    private $message;

    function __construct($message) {
        $this->message = $message;
    }

    public function serialize() {
        return $this->message;
    }
}