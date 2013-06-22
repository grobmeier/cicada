<?php
// Cicada Configuration
use Cicada\Auth\FileUserProvider;
use Cicada\Configuration;

$configuration = Configuration::getInstance();

$configuration->add('routes', array('app/routes.php'));
$configuration->add('userProvider', new FileUserProvider('app/roles.php', 'app/users.php'));

