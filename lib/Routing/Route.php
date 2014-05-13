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
namespace Cicada\Routing;

use Cicada\Application;
use Cicada\Validators\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Route
{
    /** Pattern which matches this route. */
    private $pattern;

    /**
     * Callback to be executed on matched route.
     * @var callable
     */
    private $callback;

    /** HTTP method to match. */
    private $method = 'GET';

    /** Array of callbacks to call before the request. */
    private $before = [];

    /** Array of callbacks to call after the request. */
    private $after = [];


    function __construct($pattern, $callback, $method = 'GET')
    {
        $this->pattern = '/^' . str_replace('/', '\\/', $pattern) . '$/';
        $this->callback = $callback;
        $this->method = $method;
    }

    /**
     * Checks whether this route matches the given url.
     */
    public function matches($url)
    {
        if (preg_match($this->pattern, $url, $matches)) {

            // Remove entries with int keys to filter out only named matches
            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    unset($matches[$key]);
                }
            }
            return $matches;
        }

        return false;
    }

    /**
     * Processes the Request and returns an Response.
     *
     * @return Response
     */
    public function run(Application $app, Request $request, array $arguments)
    {
        // Validate the request
        $this->validate($request);

        // Call before
        foreach($this->before as $before) {
            $result = $before($app, $request);
            if (isset($result)) {
                return $result;
            }
        }

        $callback = $this->processCallback($this->callback);

        // Add application and request as first two arguments
        array_unshift($arguments, $app, $request);

        // Execute the callback
        $response = call_user_func_array($callback, $arguments);

        // If callback returns a string, use it to construct a Response
        if (is_string($response)) {
            $response = new Response($response, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
        }

        // Call after
        foreach($this->after as $after) {
            $after($app, $request, $response);
        }

        return $response;
    }

    // -- Builder methods ------------------------------------------------------

    /** Adds a callback to execute before the request. */
    public function before($callback)
    {
        $this->before[] = $callback;
        return $this;
    }

    /** Adds a callback to execute after the request. */
    public function after($callback)
    {
        $this->after[] = $callback;
        return $this;
    }

    // -- Accesor methods ------------------------------------------------------

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getMethod()
    {
        return $this->method;
    }

    // -- Private methods ------------------------------------------------------

    /**
     * Parses the given callback and returns a callable.
     *
     * @param  string|callable $callback
     * @return callable
     */
    private function processCallback($callback)
    {
        if (is_string($callback) && strpos('::', $callback) !== false) {
            $callback = $this->parseClassCallback($callback);
        }

        if (!is_callable($callback)) {
            return new Response("Invalid callback", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $callback;
    }

    /**
     * Parses a string like "SomeClass::someMethod" and returns a corresponding
     * callable array for method someMehod on a new instance of SomeClass.
     */
    private function parseClassCallback($callback)
    {
        list($class, $method) = explode('::', $callback);

        if (!class_exists($class)) {
            throw new \Exception("Class $class does not exist.");
        }

        $object = new $class();

        if (!method_exists($object, $method)) {
            throw new \Exception("Method $class::$method does not exist.");
        }

        return [$object, $method];
    }

    private function validate(Request $request)
    {
        $this->validateMethod($request);
    }

    private function validateMethod(Request $request)
    {
        $method = $request->getMethod();
        if ($method !== $this->method) {
            throw new \UnexpectedValueException("Method: $method not allowed for this request.");
        }
    }
}
