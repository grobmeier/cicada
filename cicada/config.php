<?php
// Cicada Configuration
use Cicada\Auth\FileUserProvider;

$config['routes'][] = 'app/routes.php';

$config['userProvider'] = new FileUserProvider('app/roles.php', 'app/users.php');