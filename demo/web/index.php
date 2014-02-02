<?php
/*  Copyright 2013-2014 Christian Grobmeier
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

use Cicada\Configuration;
use Cicada\Routing\NoRouteException;
use Cicada\Routing\Router;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// define('CLASS_DIR', '../lib/');
define('APP_DIR', '../app/');

// set_include_path(get_include_path().PATH_SEPARATOR.CLASS_DIR);
// spl_autoload_register();

require __DIR__ . '/../../vendor/autoload.php';

require APP_DIR.'config.php';

// Init logging
Logger::configure(include APP_DIR.'logging.php');
$logger = Logger::getLogger("main");
$logger->info("Starting Cicada");

// Include routes from configuration
$config = Configuration::getInstance();
foreach ($config->get('routes') as $routeFile) {
    include_once($routeFile);
}

try {
    $request = Request::createFromGlobals();
    $route = Router::getInstance()->route($request);

    /** @var Response $response */
    $response = $route();
    $response->prepare($request);
    $response->send();

} catch (UnexpectedValueException $e) {
    echo $e->getMessage();
} catch (NoRouteException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getTraceAsString();
}
