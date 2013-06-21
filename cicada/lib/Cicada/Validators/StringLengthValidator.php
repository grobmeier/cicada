<?php

namespace Cicada\Validators;

class StringLengthValidator implements Validator {

    private $max;
    private $min;

    function __construct($max, $min = 0) {
        $this->max = $max;
        $this->min = $min;
    }

    public function validate($value, $name = "[unknown]") {
        if ($value == null) {
            return;
        }
        $length = strlen($value);
        if (!($length >= $this->min && $length <= $this->max)) {
            throw new \UnexpectedValueException("Value not allowed for: $name");
        };
    }
}