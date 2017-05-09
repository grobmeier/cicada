<?php
/*
 *  Copyright 2013-2015 Christian Grobmeier, Ivan Habunek
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

/**
 * Provides a set of callbacks to be called after the request was dispatched.
 */
trait FinishTrait
{
    private $finish = [];

    public function finish($callback)
    {
        $this->finish[] = $callback;
    }

    private function invokeFinish(array $namedParams, array $classParams)
    {
        $invoker = new Invoker();
        foreach ($this->finish as $function) {
            $invoker->invoke($function, $namedParams, $classParams);
        }
    }
}
