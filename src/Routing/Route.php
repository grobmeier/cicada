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
    )
    {
        $this->path = $path;
        $this->callback = $callback;
        $this->method = $method;
        $this->before = $before;
        $this->after = $after;
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
     * @param $app
     * @param $request
     * @param $arguments
     *
     * @return Response
     */
    public function run(Application $app, Request $request, array $arguments)
    {
        // Call before
        foreach ($this->before as $before) {
            $result = $before($app, $request);
            if (isset($result)) {
                return $result;
            }
        }

        $invoker = new Invoker();
        $response = $invoker->invoke($this->callback, $arguments, [$app, $request]);

        // If callback returns a string, use it to construct a Response
        if (is_string($response)) {
            $response = new Response($response, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }

        // Call after
        foreach ($this->after as $after) {
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

    // -- Private methods ------------------------------------------------------

    private function processPath($path, $asserts)
    {
        // Replace placeholders in curly braces with named regex groups
        $callback = function($matches) use ($asserts) {
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
        $pattern = str_replace('/','\\/', $pattern);

        // Add start and and delimiters
        return "/^$pattern$/";
    }
}
