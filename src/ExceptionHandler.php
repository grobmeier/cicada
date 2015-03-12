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


use Symfony\Component\HttpFoundation\Request;
class ExceptionHandler
{
    private $callbacks = [];

    /**
     * Adds an exception callback.
     *
     * The exception callback should be a callable
     * function which has one argument, and that argument must have a type hint
     * of an exception class. It should return a
     * `Symfony\Component\HttpFoundation\Response` object.
     *
     * For example:
     *
     * ```
     * $handler->add(function(SomeException $ex) {
     *      return new Response("Something broke", Response::HTTP_INTERNAL_SERVER_ERROR);
     * });
     * ```
     *
     * It's possible to have multiple handlers and they will be checked in the
     * order they were added. So be careful to put more specific exceptions
     * before more generic ones (i.e. \Exception should come last).
     */
    public function add(callable $callback)
    {
        $reflection = new \ReflectionFunction($callback);

        $params = $reflection->getParameters();
        if (empty($params)) {
            throw new \InvalidArgumentException(
                "Invalid exception callback: Has no arguments. Expected at least one."
            );
        }

        // Read the type hinted class for the first parameter
        $class = $params[0]->getClass();

        if ($class === null) {
            throw new \InvalidArgumentException(
                "Invalid exception callback: The first argument must have a class type hint."
            );
        }

        $this->callbacks[$class->name] = $callback;
    }

    /**
     * Calls the first callback which matches the given exception (is of the
     * same class or parent).
     *
     * @param  Exception $ex The exception to handle.
     * @return Response|null Returns a response returned by the handler, or null
     * if no callbacks matched the exception.
     */
    public function handle(\Exception $ex, Request $request)
    {
        foreach ($this->callbacks as $exClass => $callback) {
            if ($ex instanceof $exClass) {
                $invoker = new Invoker();
                return $invoker->invoke($callback, [], [$ex, $request]);
            }
        }
    }

    /** Returns the handlers array */
    public function getCallbacks()
    {
        return $this->callbacks;
    }
}
