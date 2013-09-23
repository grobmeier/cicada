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
    private $allowedGetFields = array();
    private $allowedMethod = 'GET';

    use Url;

    function __construct($route, $action, $allowedMethod = 'GET') {
        $this->action = $action;
        $this->route = $route;
        $this->allowedMethod = $allowedMethod;
    }

    private function validate($in, $allowedFields) {
        $keys = array_keys($in);

        foreach ($keys as $key) {
            $found = false;
            foreach ($allowedFields as $allowed) {
                if ($allowed->fieldName == $key) {
                    $found = true;

                    if (isset($allowed->validators)) {
                        /** @var $validator Validator */
                        foreach ($allowed->validators as $validator) {
                            $validator->validate($in[$key], $key);
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

    public function validateMethod() {
        if ($_SERVER['REQUEST_METHOD'] !== $this->allowedMethod) {
            throw new \UnexpectedValueException('Method: '.$_SERVER['REQUEST_METHOD'].' not allowed for this request.');
        }
    }

    public function validateGet() {
        $this->validate($_GET, $this->allowedGetFields);
    }

    public function validatePost() {
        $this->validate($_POST, $this->allowedPostFields);
    }

    private function wrapField($fieldName, $validators = null) {
        $allowed = new \stdClass();
        $allowed->fieldName = $fieldName;
        $allowed->validators = $validators;
        return $allowed;
    }

    public function allowGetField($fieldName, $validators = null) {
        array_push($this->allowedGetFields, $this->wrapField($fieldName, $validators));
        return $this;
    }

    public function allowPostField($fieldName, $validators = null) {
        array_push($this->allowedPostFields, $this->wrapField($fieldName, $validators));
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