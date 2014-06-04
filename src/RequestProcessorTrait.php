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

trait RequestProcessorTrait
{
    /** Array of callbacks to call before processing the request. */
    private $before = [];

    /** Array of callbacks to call after processing the request. */
    private $after = [];

    /** Adds a callback to execute before the request. */
    public function before($callback)
    {
        $this->before[] = $callback;

        return $this;
    }

    /** Adds a callback to execute after the request. */
    public function after(callable $callback)
    {
        $this->after[] = $callback;

        return $this;
    }

    public function processRequest(
        Application $app,
        Request $request,
        $callback,
        array $arguments = []
    )
    {
        $response = $this->processRequestBefore($app, $request, $callback, $arguments);

        // If callback does not return a Response, try to create one. This
        // throws an exception if $response cannot be converted to a Response.
        if (!($response instanceof Response)) {
            $response = new Response($response, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }

        // Callbacks to execute after the route
        $this->invokeAfter($app, $request, $arguments, $response);

        return $response;
    }

    private function processRequestBefore(
        Application $app,
        Request $request,
        $callback,
        array $arguments = []
    )
    {
        // Callbacks to execute before the route
        $response = $this->invokeBefore($app, $request, $arguments);

        // If they return a response, it stops route execution
        if (isset($response)) {
            return $response;
        }

        // Invoke the route callback
        $invoker = new Invoker();
        return $invoker->invoke($callback, $arguments, [$app, $request]);
    }

    /**
     * Invokes callbacks from `$this->before`, and if any of them returns a
     * Response stops processing others and returns the given response.
     */
    private function invokeBefore(Application $app, Request $request, array $arguments)
    {
        $invoker = new Invoker();
        foreach ($this->before as $function) {
            $response = $invoker->invoke($function, $arguments, [$app, $request]);
            if ($response !== null) {
                return $response;
            }
        }
    }

    /**
     * Invokes the callbacks from `$this->after`, ignoring any returned values.
     */
    private function invokeAfter(Application $app, Request $request, array $arguments, Response $response)
    {
        $invoker = new Invoker();
        foreach ($this->after as $function) {
            $invoker->invoke($function, $arguments, [$app, $request, $response]);
        }
    }
}
