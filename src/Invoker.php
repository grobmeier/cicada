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
namespace Cicada;

class Invoker
{
    /**
     * Invokes a method or anonymous function and returns the result.
     *
     * @param  string|callable $callable
     * @param  array           $argsByName
     * @param  array           $argsByClass
     *
     * @return mixed
     *
     * @throws \Exception when the callable isn't callable
     */
    public function invoke($callable, array $argsByName = [], array $argsByClass = [])
    {
        $argsByClass = $this->reindexArgsByClass($argsByClass);

        if (is_string($callable) && strpos($callable, '::') !== false) {
            return $this->invokeClassCallable($callable, $argsByName, $argsByClass);
        }

        if (is_callable($callable)) {
            return $this->invokeCallable($callable, $argsByName, $argsByClass);
        }

        throw new \InvalidArgumentException("Given argument is not callable.");
    }

    /**
     * Parses a string like "SomeClass::someMethod" and returns a corresponding
     * callable array for method someMethod on a new instance of SomeClass.
     */
    private function invokeClassCallable($callable, $argsByName, $argsByClass)
    {
        list($class, $method) = explode('::', $callable);

        if (!class_exists($class)) {
            throw new \Exception("Class $class does not exist.");
        }

        $object = new $class();

        if (!method_exists($object, $method)) {
            throw new \Exception("Method $class::$method does not exist.");
        }

        $reflection = new \ReflectionMethod($class, $method);
        $params = $reflection->getParameters();

        $invokeParams = $this->mapParameters($params, $argsByName, $argsByClass);

        return call_user_func_array([$object, $method], $invokeParams);
    }

    private function invokeCallable($callable, $argsByName, $argsByClass)
    {
        $reflection = new \ReflectionFunction($callable);
        $params = $reflection->getParameters();

        $invokeParams = $this->mapParameters($params, $argsByName, $argsByClass);

        return call_user_func_array($callable, $invokeParams);
    }

    private function mapParameters(array $params, $argsByName, $argsByClass)
    {
        // Array of params in order in which they should be passed to the function
        $invokeParams = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $class = $param->getClass();

            // First try to match by class, then by name
            if (isset($class) && isset($argsByClass[$class->name])) {
                $invokeParams[] = $argsByClass[$class->name];
            } elseif (isset($argsByName[$name])) {
                $invokeParams[] = $argsByName[$name];
            } else {
                $invokeParams[] = null;
            }
        }

        return $invokeParams;
    }

    private function reindexArgsByClass($args)
    {
        $reindexed = [];
        foreach ($args as $arg) {
            $class = get_class($arg);
            $reindexed[$class] = $arg;
        }

        return $reindexed;
    }
}
