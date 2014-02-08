<?php
/*
 *  Copyright 2013 Christian Grobmeier
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
use Cicada\Routing\ProtectorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

get('^/$', function(Request $request) {
    return new RedirectResponse('/hello/world');
});

get('^/hello/world$', function(Request $request) {
    return new Response("Hello World!");
});

get('^/hello/(?<name>[^/]+)$', function($request, $name) {
    return new Response("Hello $name!");
});

class SomeAction {
    public function execute($request, $name) {
        return new Response("Hello from execute, $name.");
    }
    public function foo($request, $name) {
        return new Response("Hello from foo, $name.");
    }
}

get('^/some/exec/(?<name>.+)$', "SomeAction"); // defaults to execute
get('^/some/foo/(?<name>.+)$', "SomeAction::foo");


// **********************************************
// ** Protecting routes                        **
// **********************************************

class MyProtector implements ProtectorInterface
{
    public function protect(Request $request)
    {
        $secret = $request->query->get('secret');
        if ($secret !== "foo") {
            return new Response('You do not have access.', Response::HTTP_FORBIDDEN);
        }
    }
}

get('^/protected$', function(Request $request) {
    return new Response("Welcome. This is protected.");
})
->allowGetField('secret');

protect('^/protected', new MyProtector());
