<?php
define('CLASS_DIR', 'lib/');
set_include_path(get_include_path().PATH_SEPARATOR.CLASS_DIR);
spl_autoload_register();

require 'vendor/autoload.php';


use Cicada\Test;

include_once 'logging.php';

Logger::configure($loggingConfiguration);
$logger = Logger::getLogger("main");
$logger->info("Starting Cicada");

echo get_include_path();
echo "Hello<br/>";

echo $_GET['url'];

if(isset($_GET['ups'])) echo $_GET['ups'];

$t = new Test();
$t->pr();