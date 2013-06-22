<?php

namespace Cicada;

class Session {
    private static $instance;

    private function __construct() {
        session_start();
    }

    public function add($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function destroy() {
        session_unset();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Session();
        }
        return self::$instance;
    }
}