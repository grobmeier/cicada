<?php
namespace Cicada\Responses;

interface Response {

    public function headers();

    public function serialize();
}