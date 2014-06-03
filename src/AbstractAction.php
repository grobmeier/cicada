<?php
/*
 *  Copyright 2013-2014 Christian Grobmeier, Ivan Habunek
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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abastract implementation of a controller which can processes a Request and
 * provide a response.
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

    /**
     * Validates request fields.
     */
    protected function validate(Request $request)
    {

    }

    /**
     * Does the actual work.
     *
     * @param  Application $app     Application object.
     * @param  Request     $request Request to process.
     * @return Response The resulting response.
     */
    abstract protected function doExecute(Application $app, Request $request);

    private function checkFields(Request $request)
    {
        /* TODO: check fields in request against whitelists. */
    }
}
