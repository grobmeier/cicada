<?php

namespace Cicada\Routing;

class Route {
    private $route;
    private $action;

    private $matches;

    function __construct($route, $action) {
        $this->action = $action;
        $this->route = $route;
    }

    public function matches($url) {
        $this->matches = array();
        $result = preg_match_all($this->route, $url, $this->matches, PREG_SET_ORDER);
        return ($result != 0 && $result != false);
    }

    /**
     * @return mixed
     */
    public function getMatches() {
        return $this->matches[0];
    }

    public function getAction() {
        return $this->action;
    }
}