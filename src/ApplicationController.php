<?php

namespace Synapse;

use Synapse\Attributes\Route;

class ApplicationController
{
    protected Route $currentRoute;

    final public function setCurrentRoute(Route $route)
    {
        $this->currentRoute = $route;
    }
}