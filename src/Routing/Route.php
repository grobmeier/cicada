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
namespace Cicada\Routing;

use Cicada\Application;
use Cicada\RequestProcessorTrait;
use Cicada\Invoker;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Route
{
    use RequestProcessorTrait;

    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_HEAD = 'HEAD';
    const HTTP_OPTIONS = 'OPTIONS';
    const HTTP_PATCH = 'PATCH';

    /** Array of known HTTP methods. */
    private $methods = [
        self::HTTP_GET,
        self::HTTP_POST,
        self::HTTP_PUT,
        self::HTTP_DELETE,
        self::HTTP_HEAD,
        self::HTTP_OPTIONS,
        self::HTTP_PATCH,
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

    /** Route name, optional. */
    private $name;

    public function __construct(
        $path = '/',
        $callback = null,
        $method = null,
        $before = [],
        $after = [],
        $name = null
    ) {
        $this->path = $path;
        $this->callback = $callback;
        $this->before = $before;
        $this->after = $after;
        $this->name = $name;

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
        return $this->processRequest($app, $request, $this->callback, $arguments);
    }

    /**
     * Returns the route's path, with {placeholders} substituted with values
     * from $params.
     *
     * For example, if `$this->path = "/hello/{name}"`, and
     * `$params = ["name" => "ivan"]`, this method will return `/hello/ivan`.
     *
     * @param  array $params Associative array holding parameters to substitute.
     *
     * @return string The route's path.
     *
     * @throws \Exception If any of the params is missing.
     * @throws \Exception If any of the params does not pass assert validation.
     */
    public function getRealPath($params)
    {
        $route = $this->getName();
        if (empty($route)) {
            $route = $this->path;
        }

        $path = $this->prefix . $this->path;

        // Locate placeholders in curly braces
        $count = preg_match_all('/{([^}]+)}/', $path, $matches);

        foreach ($matches[1] as $name) {

            // Parameter must be given
            if (!isset($params[$name])) {
                throw new \Exception("Missing parameter \"$name\" for route \"$route\".");
            }

            $value = $params[$name];

            // If an assert exists, the parameter must match it
            if (isset($this->asserts[$name])) {
                $pattern = $this->asserts[$name];
                if (!preg_match("/^" . $pattern . "$/", $value)) {
                    throw new \Exception("Route parameter \"$name\" must match pattern \"$pattern\", given \"$value\".");
                }
            }

            $path = str_replace('{' . $name . '}', $value, $path);
        }

        return $path;
    }

    // -- Builder methods ------------------------------------------------------

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

    /** Set the route name. */
    public function name($name)
    {
        $this->name = $name;

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
            $this->pattern = $this->compileRegex();
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

    public function getName()
    {
        return $this->name;
    }

    // -- Private methods ------------------------------------------------------

    /**
     * Compiles a regex pattern which matches this route.
     */
    private function compileRegex()
    {
        // Prepend the prefix
        $path = $this->prefix . $this->path;

        $asserts = $this->asserts;

        // Replace placeholders in curly braces with named regex groups
        $callback = function ($matches) use ($asserts) {
            $name = $matches[1];
            $pattern = isset($asserts[$name]) ? $asserts[$name] : ".+";

            return "(?<$name>$pattern)";
        };

        $pattern = preg_replace_callback('/{([^}]+)}/', $callback, $path);

        // Avoid double slashes
        $pattern = preg_replace('/\/+/', '/', $pattern);

        // Escape slashes, used as delimiter in regex
        $pattern = str_replace('/', '\\/', $pattern);

        // Add start and and delimiters
        return "/^$pattern$/";
    }
}
