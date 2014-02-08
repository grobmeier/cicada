<?php

namespace Cicada\Routing;

use Symfony\Component\HttpFoundation\Request;

interface ProtectorInterface
{
    public function protect(Request $request);
}
