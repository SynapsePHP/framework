<?php

namespace Synapse;

use Synapse\Attributes\Route;

class ApplicationController
{
    protected Route $currentRoute;

    final public function setCurrentRoute(Route $route): void
    {
        $this->currentRoute = $route;
    }
}