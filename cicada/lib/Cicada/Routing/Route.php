<?php

namespace Cicada\Routing;

class Route {
    private $route;
    private $action;
    private $matches;

    use Url;

    function __construct($route, $action) {
        $this->action = $action;
        $this->route = $route;
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

    public function getRoute() {
        return $this->route;
    }

    public function setMatches($matches) {
        $this->matches = $matches;
    }
}