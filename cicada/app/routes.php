<?php

use Cicada\Responses\EchoResponse;

get('/\/hello\/world/', function() {
    return new EchoResponse("Hello World");
});