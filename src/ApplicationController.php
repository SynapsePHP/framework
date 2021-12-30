<?php

namespace Synapse;

use Synapse\Attributes\Route;

class ApplicationController
{
    protected Route $currentRoute;
    protected Security $security;

    public function __construct()
    {
        $this->security = new Security();
    }

    final public function setCurrentRoute(Route $route): void
    {
        $this->currentRoute = $route;
    }
}