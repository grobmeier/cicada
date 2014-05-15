Cicada
======

A micro framework for

Installation
------------

Add Cicada as a requirement for your project via Composer:

```
composer require "grobmeier/cicada=@stable"
```

Usage
-----

Minimal application:

```
require '../vendor/autoload.php';

use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

// Add a route
$app->get('/hello/{name}', function (Application $app, Request $request, $name) {
    return Response("Hello $name");
});

$app->run();
```

This application has one route which will match GET requests starting with
`/hello/` and will forward the matched `{name}` into the callback function as
`$name`.

The callback function should return a
[Response](http://symfony.com/doc/current/components/http_foundation/introduction.html#response)
object. If it returns a string, it will implicitly be converted into a Response.
