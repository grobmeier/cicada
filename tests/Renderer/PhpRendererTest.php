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

class PhpResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $response = new PhpRenderer("test.php");
        $response->setTemplateFolder('./tests/resources/phptemplates/');
        $output = $response->render();

        $this->assertEquals('Hello World', $output);
    }

    public function testValues()
    {
        $response = new PhpRenderer('test-with-values.php', './tests/resources/phptemplates/');
        $response->setValues([
            'given_name' => 'John',
            'name' => 'Doe',
        ]);
        $output = $response->render();

        $this->assertEquals('Hello: John Doe', $output);
    }

    public function testDecorator()
    {
        $response = new PhpRenderer('test-with-values.php', './tests/resources/phptemplates/');
        $response->setValues([
            'given_name' => 'John',
            'name' => 'Doe',
        ]);
        $response->setDecorator('decorator.php', 'mycontentfield');
        $output = $response->render();

        $this->assertEquals('<div>Hello: John Doe</div>', $output);
    }
}
 