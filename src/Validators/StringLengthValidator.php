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
namespace Cicada\Validators;

class StringLengthValidator implements Validator
{
    private $max;
    private $min;

    public function __construct($max, $min = 0)
    {
        $this->max = $max;
        $this->min = $min;
    }

    public function validate($value)
    {
        if ($value == null) {
            return;
        }
        $length = strlen($value);

        if ($length < $this->min) {
            throw new \UnexpectedValueException("String length too short. Minimum allowed: $this->min.");
        }

        if ($length > $this->max) {
            throw new \UnexpectedValueException("String length too long. Maximum allowed: $this->max.");
        }
    }
}
