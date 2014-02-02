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
namespace Cicada\Responses;

class JsonResponse extends AbstractResponse {
    private $data;

    private $prefix;
    private $suffix;

    function __construct($data, $prefix = ")]}',", $suffix = "") {
        $this->data = $data;

        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->addHeader('Content-type: application/json');
    }

    public function serialize() {
        $prefix = "";
        $suffix = "";

        if ($this->prefix != null && $this->prefix != "") {
           $prefix = $this->prefix.PHP_EOL;
        }

        if ($this->suffix != null && $this->suffix != "") {
            $suffix = $this->suffix.PHP_EOL;
        }

        return $prefix.json_encode($this->data).$suffix;
    }
}