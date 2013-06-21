<?php

namespace Cicada\Auth;


class Role {
    private $name;

    function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }
}