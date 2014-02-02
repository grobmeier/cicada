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
namespace Cicada;

abstract class Action implements ActionExecutor {
    public abstract function execute();

    public function readAllData() {
        $resource = fopen("php://input", "r");

        $result = '';
        while ($data = fread($resource, 1024)) {
            $result .= $data;
        }

        return $result;
    }

    /**
     * @param callable $function
     * @param int $bufferSize
     */
    public function bufferedRead($function, $bufferSize = 1024) {
        $resource = fopen("php://input", "r");
        while ($data = fread($resource, $bufferSize)) {
            $function($data);
        }
    }

    /**
     * Not required to deliver a response
     */
    public function getResponse() {
        return null;
    }
}