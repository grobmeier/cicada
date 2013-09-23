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

use Cicada\ActionExecutor;
use Cicada\Auth\LoginAction;
use Cicada\Responses\EchoResponse;
use Cicada\Session;
use Exception;
use ReflectionClass;
use ReflectionFunction;

class Router {
    private $routeMap = array();
    private $protectors = array();
    private static $instance;

    private function __construct() {
    }

    public function addRoute(Route $route) {
        array_push($this->routeMap, $route);
    }

    public function addProtector(Protector $protector) {
        array_push($this->protectors, $protector);
    }

    public function route($url) {

        /** @var $protector Protector */
        foreach ($this->protectors as $protector) {

            if ($protector->matches($url)) {
                $resultFunction = $this->protect($protector);

                if ($resultFunction != null) {
                    return $resultFunction;
                }
            }
        }

        /** @var $route Route */
        foreach ($this->routeMap as $route) {
            if ($route->matches($url)) {
                $route->validateGet();
                $route->validatePost();

                if (is_string($route->getAction())) {
                    return $this->handleActionExecutorName($route);
                }

                return $this->handleClosure($route);
            }
        }
        throw new Exception("No match for route");
    }

    /**
     * Returns an action function, if the user is not allowed to proceed,
     * or null, if the user is allowed to proceed.
     *
     * @param Protector $protector
     * @return callable|mixed|null
     */
    public function protect(Protector $protector) {
        $user = Session::getInstance()->get(LoginAction::CICADA_USER);

        if ($user != null) {
            if ($protector->isUserAllowed($user)) {
                return null;
            }
        }

        $onFail = $protector->getOnFail();
        if($onFail != null) {
            return $onFail;
        } else {
            return function() {
                $echo = new EchoResponse("Unauthorized");
                $echo->addHeader("HTTP/1.1 401 Unauthorized");
                return $echo;
            };
        }
    }


    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    /**
     * @param $route
     * @return callable
     */
    private function handleActionExecutorName($route) {
        return function () use ($route) {
            $clazz = new ReflectionClass($route->getAction());
            $obj = $clazz->newInstance();
            $execMethod = $clazz->getMethod('execute');

            $matches = $route->getMatches();
            $this->cleanMatches($matches);

            $result = $execMethod->invokeArgs($obj, $matches);

            if (ActionExecutor::SUCCESS == $result) {
                $resMethod = $clazz->getMethod('getResponse');
                return $resMethod->invoke($obj);
            } else {
                return new EchoResponse("An error occurred.");
            }
        };
    }

    /**
     * @param $route
     * @return callable
     */
    private function handleClosure($route) {
        return function () use ($route) {
            $action = $route->getAction();

            $matches = $route->getMatches();
            $this->cleanMatches($matches);

            $function = new ReflectionFunction($action);
            return $function->invokeArgs($matches);
        };
    }

    /**
     * @param $matches
     */
    private function cleanMatches(&$matches) {
        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                unset($matches[$key]);
            }
        }
    }
}