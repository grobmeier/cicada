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

/**
 * TODO: Finish the work
 */
abstract class AbstractAction
{
    /**
     * An array with names of allowed POST fields.
     *
     * Acts like a whitelist. If set, execute() will throw an exception if any
     * other fields are encountered in POST.
     *
     * @var array
     */
    protected $postFields;

    /**
     * An array with names of allowed GET fields.
     *
     * Acts like a whitelist. If set, execute() will throw an exception if any
     * other fields are encountered in GET.
     *
     * @var array
     */
    protected $getFields;

    public function execute(Application $app, Request $request)
    {
        $this->checkFields($request);
        $this->validate($request);
        $this->doExecute($app, $request);
    }

    protected function validate(Application $app, Request $request)
    {

    }

    protected abstract function doExecute(Application $app, Request $request);

    private function checkFields(Request $request)
    {
        /* TODO: check fields in request against whitelists. */
    }
}
