<?php
use Cicada\Responses\Response;
use Cicada\Routing\Router;

define('CLASS_DIR', 'lib/');
set_include_path(get_include_path().PATH_SEPARATOR.CLASS_DIR);
spl_autoload_register();

require 'vendor/autoload.php';

include_once 'config.php';
include_once 'logging.php';

include_once 'lib/Cicada/Functions.php';

Logger::configure($loggingConfiguration);

$logger = Logger::getLogger("main");
$logger->info("Starting Cicada");

foreach ($config['routes'] as $routeFile) {
    include_once($routeFile);
}

try {
    $response = Router::getInstance()->route($_GET['url']);
    /** @var Response $executed */
    $executed = $response();
    $headers = $executed->headers();
    if ($headers != null) {
        foreach ($headers as $header) {
             header($header);
        }
    }
    echo $executed->serialize();
} catch (UnexpectedValueException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getTraceAsString();
}

