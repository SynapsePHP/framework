<?php

namespace Synapse;

use ReflectionException;
use Synapse\Attributes\Controller;
use Synapse\Attributes\Route;
use Synapse\Attributes\SecureRoute;

class Router
{
    private static array $controllers      = [];
    private static array $routes           = [];
    private static string $errorController = '';

    /**
     *
     * Register a controller for custom paths (ex: /blog url base within the blog controller)
     *
     * @param string $class
     *
     */
    public static function registerController(string $class): void
    {
        static::$controllers[] = $class;
    }

    /**
     *
     * Register what controller will handle the errors (403, 404, 500, etc.)
     *
     * @param string $class
     *
     */
    public static function registerErrorController(string $class): void
    {
        static::$errorController = $class;
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
            $ctrlName   = $reflectedClass->getName();
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

                                if (empty($baseURLs)) {
                                    $exception = new Exceptions\Route(
                                        "Your controller {$ctrlName} must define the entry point using the Controller Attribute.",
                                        500
                                    );

                                    $exception->output();
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
            if (static::$errorController !== '') {
                $instance = new static::$errorController();

                if (!method_exists($instance, 'displayError')) {
                    $exception = new Exceptions\Route(
                        'The registered error controller does not have the required "displayError" method to handle the error. Please insure it exists.',
                        500
                    );

                    $exception->output();
                }

                $isJSON = static::isJSONRequest();
                $return = $instance->displayError(404, $isJSON);

                // Send JSON if the response is not a scalar value
                if (is_object($return) || is_array($return)) {
                    header('Content-Type: application/json');
                    echo json_encode($return, JSON_THROW_ON_ERROR);
                } else {
                    echo $return;
                }
            } else {
                // Default if nothing registered
                http_response_code(404);
                echo "<h1>URL Not Found</h1>";
                die();
            }
        }
    }

    private static function isJSONRequest(): bool
    {
        $headers = getallheaders();

        foreach ($headers as $name => $value) {
            if (($name === 'Content-Type') && $value === 'application/json') {
                return true;
            }
        }

        return false;
    }
}