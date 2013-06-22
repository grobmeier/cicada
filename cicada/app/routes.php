<?php

use Cicada\Action;
use Cicada\Auth\LoginAction;
use Cicada\Auth\LogoutAction;
use Cicada\Configuration;
use Cicada\Responses\EchoResponse;
use Cicada\Responses\PhpResponse;
use Cicada\Validators\StringLengthValidator;

protect('/\/admin\/.*$/', config('userProvider'))
    ->allowRoles(array("admin"))
    ->allowUsers(array("anna"))
    ->setOnFail(forward('/login'));

get('/\/hello\/world$/', function() {
    return new EchoResponse("Hello World");
});

get('/\/hello\/(?<name>.*)\/(?<blub>.*)$/', function($name, $blub) {
    return new EchoResponse("Hello Parameter: " .$name . " + " . $blub);
});

get('/\/hello\/(?<name>.*)$/', function($name) {
    return new EchoResponse("Hello Parameter: " .$name);
});

get('/\/logout$/', function() {
    (new LogoutAction())->execute();
    return new PhpResponse('auth/login.php');
});

get('/\/login$/', function() {
    return new PhpResponse('auth/login.php');
});

get('/\/login\/do$/', function() {
    $username = readPost('username');
    $password = readPost('password');

    $action = new LoginAction($username, $password);
    $action->setUserProvider(config('userProvider'));
    $result = $action->execute();

    if ($result == Action::SUCCESS) {
        $echo = new EchoResponse();
        $echo->addHeader("Location: /admin/dashboard");
        return $echo;
    } else {
        return new PhpResponse('auth/login.php', array("username" => $username));
    }
})
    ->allowField("username", array( new StringLengthValidator(20) ))
    ->allowField("password", array( new StringLengthValidator(20) ));

get('/\/phptemplate\/decorator$/', function() {
    $response = new PhpResponse('helloworld.php', array( 'name' => "myname", 'ups' => 'huhu'));
    $response->setDecorator('base.php');
    return $response;
});

get('/\/phptemplate\/(?<name>.*)$/', function($name) {
    return new PhpResponse('helloworld.php', array( 'name' => $name, 'ups' => 'huhu'));
});

get('/\/admin\/dashboard$/', function() {
    return new EchoResponse('You are seeing the dashboard. <a href="/logout">Logout</a>');
});

get('/\/admin/', forward('/admin/dashboard'));
get('/\//', forward('/hello/world'));
