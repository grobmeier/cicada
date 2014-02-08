<pre><?php
/*  Copyright 2013-2014 Christian Grobmeier
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */

use Cicada\Application;
use Cicada\Routing\ProtectorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

require __DIR__ . '/../../vendor/autoload.php';

$app = new Application();

///
/// Inline callbacks
///

$app->get('^/$', function(Application $app, Request $request) {
    return new Response('Home sweet home.');
});

$app->get('^/hello$', function(Application $app, Request $request) {
    return new RedirectResponse('/hello/world');
});

$app->get('^/hello/world$', function(Application $app, Request $request) {
    return new Response("Hello World!");
});

$app->get('^/hello/(?<name>\\w+)$', function(Application $app, Request $request, $name) {
    return new Response("Hello $name!");
});


///
/// Method callbacks
///

class MyController
{
    public function hello()
    {
        return new Response("Hello from controller");
    }
}

$app->get('^/controller', "MyController::hello");

///
/// Protection
///

$protector = function(Application $app, Request $request) {
    $secret = $request->query->get('secret');
    if ($secret !== 'foo') {
        return new Response("You didn't say the magic word!", Response::HTTP_FORBIDDEN);
    }
};

$app->get('/protected', function(Application $app, Request $request) {
    return new Response('You\'re in. Congrats.');
})
->allowGetField('secret')
->before($protector);

///
/// Run the app
///

$app->run();
