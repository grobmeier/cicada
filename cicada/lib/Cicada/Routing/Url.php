<?php
/*
 *  Copyright 2013 Christian Grobmeier
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
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