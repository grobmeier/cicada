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
namespace Cicada\Tests;

use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;

class InvokerTestAction
{
    public function execute1($foo, $bar)
    {
        return func_get_args();
    }

    public function execute2($foo, $bar, Request $request)
    {
        return func_get_args();
    }

    public function execute3(Application $app, Request $request, $foo, $bar)
    {
        return func_get_args();
    }

    public function execute4(Application $bla, Request $tra, $foo, $bar)
    {
        return func_get_args();
    }

    public function execute5(Application $bla, Request $tra, $foo, $bar, $nonexistant)
    {
        return func_get_args();
    }
}
