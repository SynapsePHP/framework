<?php

namespace Synapse;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use ReflectionException;

class Bootstrap
{
    public static function init(string $root)
    {
        // Load .env file
        try {
            $dotenv = Dotenv::createImmutable($root . '/.env');
            $dotenv->load();
        } catch (InvalidPathException $e) {
            // Default values
            $dotenv = Dotenv::createImmutable($root . '/config/defaults', 'env');
            $dotenv->load();
        }

        try {
            Router::run();
        } catch (ReflectionException $e) {
            echo $e->getMessage();
        }
    }
}