<?php

namespace Synapse;

use ReflectionException;
use Synapse\Attributes\Controller;
use Synapse\Attributes\Route;
use Synapse\Attributes\SecureRoute;

class Router
{
    private static array $controllers = [];
    private static array $routes      = [];

    /**
     *
     * Register a controller for custom paths (ex: /blog url base within the blog controller)
     *
     * @param $class
     *
     */
    public static function registerController($class): void
    {
        static::$controllers[] = $class;
    }

    /**
     *
     * Run the router
     *
     * @throws ReflectionException
     * @throws \JsonException
     *
     */
    public static function run(): void
    {
        $currentURL = explode('?', $_SERVER['REQUEST_URI'])[0];

        // Force removal of trailing slash
        if ($currentURL[strlen($currentURL) - 1] === '/') {
            $currentURL = substr($currentURL, 0, -1);
            header('location: ' . $currentURL);
            exit();
        }

        $routeFound  = false;
        $activeRoute = null;

        // Parse Controllers first
        foreach (static::$controllers as $controller) {
            $reflectedClass = new \ReflectionClass($controller);
            $attrs          = $reflectedClass->getAttributes(Controller::class);

            // Controller instance
            $controller = $reflectedClass->newInstance();

            // Base URL for all routes within the controller
            $baseURLs = null;

            foreach ($attrs as $attribute) {
                if ($attribute->getName() === Controller::class) {
                    $baseURLs = (object)$attribute->getArguments()[0];
                }
            }

            // Parse every URL for the controller
            $methods = $reflectedClass->getMethods();

            foreach ($methods as $method) {
                $attrs = $method->getAttributes();

                if (!empty($attrs)) {
                    foreach ($attrs as $attr) {
                        $name = $attr->getName();

                        if ($name === Route::class || $name === SecureRoute::class) {
                            // Route definition with :id in it
                            foreach ($attr->getArguments() as $argument) {
                                if (stripos($argument, ':id') !== false) {
                                    $url = str_replace(':id', '([0-9a-z]{24})', $argument);
                                } else {
                                    $url = $argument;
                                }

                                // Enable combining :id and :any
                                if (stripos($argument, ':any') !== false) {
                                    $url = str_replace(':any', '([^/]+)', $url);
                                }

                                foreach ($baseURLs as $lang => $burl) {
                                    $_url = str_replace('//', '/', $burl . $url);

                                    if (preg_match('#^' . $_url . '$#', $currentURL, $matched)) {
                                        unset($matched[0]);
                                        $params = array_values($matched);

                                        $activeRoute = $attr->newInstance();
                                        $activeRoute->setCurrentURL($currentURL);
                                        $activeRoute->setLanguage($lang);
                                        $activeRoute->setExecutionHandlers($controller, $method->getName(), $params);
                                        $controller->setCurrentRoute($activeRoute);

                                        if ($name === SecureRoute::class) {
                                            $activeRoute->setIsSecure(true);
                                            $activeRoute->setSecureRedirect($attr->getArguments()[1]);
                                        }

                                        $routeFound = true;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($routeFound) { break; }
            }

            // Stop looking if already found
            if ($routeFound) { break; }
        }

        if ($routeFound && !empty($activeRoute)) {
            // Tell Synapse we are using set language
            // TODO: DO IT WHEN I18n IS READY

            if ($activeRoute->isSecure) {
                $activeRoute->verify();
            }

            // Execute the route
            // TODO: Handle return (if string, output... otherwise JSON ENCODE)
            $return = $activeRoute->execute();

            if (!is_object($return) && !is_array($return)) {
                echo $return;
            } else {
                // Force JSON header and encode the entity
                header('Content-Type: application/json');
                echo json_encode($return, JSON_THROW_ON_ERROR);
            }
        } else {
            // TODO: 404
            echo "NOT FOUND";
        }
    }
}