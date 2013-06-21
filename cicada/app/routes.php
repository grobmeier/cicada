<?php

use Cicada\Responses\EchoResponse;
use Cicada\Responses\PhpResponse;

protect('/\/admin\/.*$/', $config['userProvider'])
    ->allowRoles(array("admin"))
    ->allowUsers(array("anne"));

get('/\/hello\/world$/', function() {
    return new EchoResponse("Hello World");
});

get('/\/hello\/(?<name>.*)\/(?<blub>.*)$/', function($name, $blub) {
    return new EchoResponse("Hello Parameter: " .$name . " + " . $blub);
});

get('/\/hello\/(?<name>.*)$/', function($name) {
    echo $_GET['ups'];
    return new EchoResponse("Hello Parameter: " .$name);
});

get('/\/phptemplate\/(?<name>.*)$/', function($name) {
    return new PhpResponse('helloworld.php', array( 'name' => $name, 'ups' => 'huhu'));
});

get('/\/admin\/dashboard$/', function() {
    return new EchoResponse("You are seeing the dashboard");
});

