<?php

namespace Cicada;

class Configuration {
    private static $instance;

    private $map = array();

    private function __construct() {
    }

    public function add($key, $value) {
        $this->map[$key] = $value;
    }

    public function get($key) {
        return $this->map[$key];
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Configuration();
        }
        return self::$instance;
    }
}