<?php
// Cicada Configuration
use Cicada\Auth\FileUserProvider;

$config['routes'][] = 'app/routes.php';

$config['userProvider'] = new FileUserProvider('app/users.php', 'app/roles.php');