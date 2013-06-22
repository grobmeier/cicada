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

use Cicada\Validators\Validator;

class Route {
    private $route;
    private $action;
    private $matches;

    private $allowedPostFields = array();

    use Url;

    function __construct($route, $action) {
        $this->action = $action;
        $this->route = $route;
    }

    public function validatePost() {
        $keys = array_keys($_POST);

        foreach ($keys as $key) {
            $found = false;
            foreach ($this->allowedPostFields as $allowed) {
                if ($allowed->fieldName == $key) {
                    $found = true;

                    if (isset($allowed->validators)) {
                        /** @var $validator Validator */
                        foreach ($allowed->validators as $validator) {
                            $validator->validate($_POST[$key], $key);
                        }
                    }

                    break;
                }
            }
            if (!$found) {
                throw new \UnexpectedValueException("Field $key not allowed.");
            }
        }
    }


    public function allowField($fieldName, $validators = null) {
        $allowed = new \stdClass();
        $allowed->fieldName = $fieldName;
        $allowed->validators = $validators;
        array_push($this->allowedPostFields, $allowed);
        return $this;
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