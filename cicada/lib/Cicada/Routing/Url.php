<?php
namespace Cicada\Routing;

trait Url {
    public function matches($url) {
        $matches = array();
        $result = preg_match_all($this->getRoute(), $url, $matches, PREG_SET_ORDER);
        $this->setMatches($matches);
        return ($result != 0 && $result != false);
    }

    public abstract function getRoute();
    public abstract function setMatches($matches);
}