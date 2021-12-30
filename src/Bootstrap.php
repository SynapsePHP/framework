<?php

namespace Synapse;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use ReflectionException;

class Bootstrap
{
    public static function init(string $root): void
    {
        // Load .env file
        try {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->load();
        } catch (InvalidPathException $e) {
            // Default values
            print_r($e);
            $dotenv = Dotenv::createImmutable($root . '/config/defaults', 'env');
            $dotenv->load();
        }

        $settings = [];
        include_once $root . '/config/config.php';
        $_ENV['settings'] = (!empty($settings[$_ENV['SYNAPSE_ENV']])) ? $settings[$_ENV['SYNAPSE_ENV']] : $settings[$_ENV['development']];

        self::setupCORS($settings);

        // Show or hide php errors
        if ($settings[$_ENV['SYNAPSE_ENV']]['devMode']) {
            ini_set('display_errors', true);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', false);
            error_reporting(E_ERROR);
        }

        try {
            Router::run();
        } catch (ReflectionException $e) {
            echo $e->getMessage();
        }
    }

    /**
     *
     * Setup CORS for the platform and handle OPTIONS requests
     *
     * @param array $settings
     *
     */
    private static function setupCORS(array $settings): void
    {
        $conf = $settings[$_ENV['SYNAPSE_ENV']]['cors'];

        if (empty($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }

        if ($conf['use']) {
            $origins = implode(',', $conf['origin']);
            $creds   = ($conf['allowCredentials']) ? 'true' : 'false';

            header("Access-Control-Allow-Origin: {$origins}");
            header("Access-Control-Allow-Credentials: {$creds}");
            header("Access-Control-Max-Age: {$conf['maxAge']}");

            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                $methods = implode(",", $conf['methods']);
                $headers = implode(",", $conf['headers']);

                header("Access-Control-Allow-Methods: {$methods}");
                header("Access-Control-Allow-Headers: {$headers}");
                header('Content-Length: 0');
                header('Content-Type: text/plain');
                die(); // Options just needs headers, the rest is not required. Stop now!
            }
        }
    }
}