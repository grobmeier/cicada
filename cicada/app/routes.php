<?php

use Cicada\Action;
use Cicada\Auth\LoginAction;
use Cicada\Responses\EchoResponse;
use Cicada\Responses\PhpResponse;
use Cicada\Validators\StringLengthValidator;

protect('/\/admin\/.*$/', $config['userProvider'])
    ->allowRoles(array("admin"))
    ->allowUsers(array("anne"))
    ->setOnFail(forward('/login'));

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

get('/\/login$/', function() {
    return new PhpResponse('auth/login.php');
});

get('/\/login\/do$/', function() {
    global $config;

    $username = readPost('username');
    $password = readPost('password');

    $action = new LoginAction($username, $password);
    $action->setUserProvider($config['userProvider']);
    $result = $action->execute();

    if ($result == Action::SUCCESS) {

        // TODO after storing user in session
        //$echo = new EchoResponse();
        // $echo->addHeader("Location: /admin/dashboard");
        return new EchoResponse("OK");

    } else {
        return new PhpResponse('auth/login.php', array("username" => $username));
    }
})
    ->allowField("username", array( new StringLengthValidator(20) ))
    ->allowField("password", array( new StringLengthValidator(20) ));

get('/\/phptemplate\/(?<name>.*)$/', function($name) {
    return new PhpResponse('helloworld.php', array( 'name' => $name, 'ups' => 'huhu'));
});

get('/\/admin\/dashboard$/', function() {
    return new EchoResponse("You are seeing the dashboard");
});

get('/\/admin/', forward('/admin/dashboard'));
get('/\//', forward('/hello/world'));
