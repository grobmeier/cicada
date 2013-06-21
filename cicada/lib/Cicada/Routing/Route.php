<?php

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