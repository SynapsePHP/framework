<?php

namespace Synapse\Attributes;

#[\Attribute]
class Controller
{
    protected Route $currentRoute;

    public function __construct(array $urls = [])
    {
        // Ignore the urls, not needed
    }
}