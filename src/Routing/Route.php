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
use Cicada\Invoker;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Route
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';

    /** Array of known HTTP methods. */
    private $methods = [
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PUT,
        self::HTTP_DELETE,
        self::HTTP_HEAD,
    ];

    /** The route's path, e.g. `/user/{id}/posts`. */
    private $path;

    /** The regex pattern compiled from the route path. */
    private $pattern;

    /** Array of regex pattern mapped to request arguments. */
    private $asserts = [];

    /** Callback to be executed on matched route. */
    private $callback;

    /** HTTP method to match. */
    private $method;

    /** The prefix to put before the route. */
    private $prefix = '';

    /** Array of callbacks to call before the request. */
    private $before = [];

    /** Array of callbacks to call after the request. */
    private $after = [];

    public function __construct(
        $path = '/',
        $callback = null,
        $method = null,
        $before = [],
        $after = []
    ) {
        $this->path = $path;
        $this->callback = $callback;
        $this->before = $before;
        $this->after = $after;

        if (isset($method)) {
            $this->method($method);
        }
    }

    /**
     * Checks whether this route matches the given url.
     */
    public function matches($url)
    {
        $pattern = $this->getRegexPattern();

        if (preg_match($pattern, $url, $matches)) {

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
     * Processes the Request and returns a Response.
     *
     * @throws UnexpectedValueException If the route callback returns a value
     *         which is not a string or Response object.
     */
    public function run(Application $app, Request $request, array $arguments = [])
    {
        $response = $this->processRequest($app, $request, $arguments);

        // If callback does not return a Response, try to create one. This
        // throws an exception if $response cannot be converted to a Response.
        if (!($response instanceof Response)) {
            $response = new Response($response, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }

        // Callbacks to execute after the route
        $this->invokeAfter($app, $request, $arguments, $response);

        return $response;
    }

    private function processRequest(Application $app, Request $request, array $arguments = [])
    {
        // Callbacks to execute before the route
        $response = $this->invokeBefore($app, $request, $arguments);

        // If they return a response, it stops route execution
        if (isset($response)) {
            return $response;
        }

        // Invoke the route callback
        $invoker = new Invoker();
        return $invoker->invoke($this->callback, $arguments, [$app, $request]);
    }

    // -- Builder methods ------------------------------------------------------

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

    /** Sets the route's HTTP method. */
    public function method($method)
    {
        if (!in_array($method, $this->methods)) {
            throw new \InvalidArgumentException("Unknown HTTP method: $method");
        }

        $this->method = $method;

        return $this;
    }

    /** Sets the route's path. */
    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    /** Sets the route's callback. */
    public function callback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /** Sets the route's prefix. */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /** Set a regex pattern for a field. */
    public function assert($field, $pattern)
    {
        $this->asserts[$field] = $pattern;

        return $this;
    }

    // -- Accessor methods ------------------------------------------------------

    public function getCallback()
    {
        return $this->callback;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getRegexPattern()
    {
        if (!isset($this->pattern)) {
            $this->pattern = $this->processPath($this->path, $this->asserts);
        }

        return $this->pattern;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getBefore()
    {
        return $this->before;
    }

    public function getAfter()
    {
        return $this->after;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getAsserts()
    {
        return $this->asserts;
    }

    // -- Private methods ------------------------------------------------------

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

    private function processPath($path, $asserts)
    {
        // Replace placeholders in curly braces with named regex groups
        $callback = function ($matches) use ($asserts) {
            $name = $matches[1];
            $pattern = isset($asserts[$name]) ? $asserts[$name] : ".+";

            return "(?<$name>$pattern)";
        };

        $pattern = preg_replace_callback('/{([^}]+)}/', $callback, $path);

        // Prepend the prefix
        $pattern = $this->prefix . $pattern;

        // Avoid double slashes
        $pattern = preg_replace('/\/+/', '/', $pattern);

        // Escape slashes, used as delimiter in regex
        $pattern = str_replace('/', '\\/', $pattern);

        // Add start and and delimiters
        return "/^$pattern$/";
    }
}
