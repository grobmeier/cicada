<?php

use Cicada\Responses\EchoResponse;

get('/\/hello\/world$/', function() {
    return new EchoResponse("Hello World");
});

get('/\/hello\/(?<name>.*)\/(?<blub>.*)$/', function($name, $blub) {
    return new EchoResponse("Hello Parameter: " .$name . " + " . $blub);
});

get('/\/hello\/(?<name>.*)$/', function($name) {
    return new EchoResponse("Hello Parameter: " .$name);
});



