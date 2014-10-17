Cicada
======

A micro framework for creating traditional or REST-like web applications.

[![Latest Stable Version](https://poser.pugx.org/cicada/cicada/v/stable.png)](https://packagist.org/packages/cicada/cicada) [![Total Downloads](https://poser.pugx.org/cicada/cicada/downloads.png)](https://packagist.org/packages/cicada/cicada) [![Build Status](https://travis-ci.org/cicada/cicada.png)](https://travis-ci.org/cicada/cicada) [![Coverage Status](https://coveralls.io/repos/cicada/cicada/badge.png)](https://coveralls.io/r/cicada/cicada) [![License](https://poser.pugx.org/cicada/cicada/license.png)](https://packagist.org/packages/cicada/cicada)

Installation
------------

Add Cicada as a requirement for your project via Composer:

```
composer require "cicada/cicada=@stable"
```

Usage
-----

Minimal application:

```php
require '../vendor/autoload.php';

use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

// Add a route
$app->get('/hello/{name}', function (Application $app, Request $request, $name) {
    return new Response("Hello $name");
});

$app->run();
```

This application has one route which will match GET requests starting with
`/hello/` and will forward the matched `{name}` into the callback function as
`$name`.

The callback function should return a
[Response](http://symfony.com/doc/current/components/http_foundation/introduction.html#response)
object. If it returns a string, it will implicitly be converted into a Response.


Handling exceptions
-------------------

It is possible that route callbacks throw an exception. By default, Cicada will
in this case return a HTTP 500 response (Internal Server Error). However, it
is possible to add exception handlers which will intercept specific exceptions
and return an appropriate response.

For example, if you want to catch a custom NotImplementedException and return a
custom error message:

```php
$app->exception(function (NotImplementedException $ex) {
    $msg = "Dreadfully sorry, old chap, but tis' not implemented yet.";
    return new Response($msg, Response::HTTP_INTERNAL_SERVER_ERROR);
});
```

The callback function passed to `$app->exception()` must have a single argument
and that argument must have a class type hint which denotes the exception class
which it will handle.

It's possible to specify multiple exception handlers and they will be tried in
order in which they were specified:

```php
$app->exception(function (SomeException $ex) {
    return new Response("Arrrghhhhh", Response::HTTP_INTERNAL_SERVER_ERROR);
});


$app->exception(function (OtherException $ex) {
    return new Response("FFFFUUUUUUU...", Response::HTTP_INTERNAL_SERVER_ERROR);
});

// If all else fails, this will catch any exceptions
$app->exception(function (Exception $ex) {
    $msg ="Something went wrong. The incident has been logged and our code monkeys are on it.";
    return new Response($msg, Response::HTTP_INTERNAL_SERVER_ERROR);
});
```
