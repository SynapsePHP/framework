<?php

namespace Synapse\Attributes;

#[\Attribute]
class Route
{
    private object $controller;
    private string|array $url      = '';
    private string $method         = '';
    private string $language       = 'en';
    private string $action         = '';
    private array $arguments       = [];
    private string $currentURL     = '';
    private bool $isSecure         = false;
    private string $secureRedirect = '';

    public function __construct(string|array $url, string $method = 'GET', string $language = 'en')
    {
        $this->url      = $url;
        $this->method   = $method;
        $this->language = $language;
    }

    /**
     *
     * Set the language for the current route
     *
     * @param string $language
     *
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     *
     * Set the currentURL to the given url
     *
     * @param string $url
     *
     */
    public function setCurrentURL(string $url): void
    {
        $this->currentURL = $url;
    }

    /**
     *
     * Is the page protected?
     *
     * @param bool $secure
     *
     */
    public function setIsSecure(bool $secure): void
    {
        $this->isSecure = $secure;
    }

    /**
     *
     * Set the redirect url to use when security check fails
     *
     * @param string $redirect
     *
     */
    public function setSecureRedirect(string $redirect = ''): void
    {
        $this->secureRedirect = $redirect;
    }

    /**
     *
     * Set the route's handling elements
     *
     * @param object $controller
     * @param string $action
     * @param mixed $arguments
     *
     */
    public function setExecutionHandlers(object $controller, string $action, mixed $arguments): void
    {
        $this->controller = $controller;
        $this->action     = $action;
        $this->arguments  = $arguments;
    }

    /**
     *
     * Magic get for private variables
     *
     * @param string $variable
     * @return void
     *
     */
    public function __get(string $variable)
    {
        if (isset($this->{$variable})) {
            return $this->{$variable};
        }
    }

    /**
     *
     * Execute the current route's handler
     *
     * @return mixed
     *
     */
    public function execute(): mixed
    {
        return call_user_func_array([$this->controller, $this->action], $this->arguments);
    }
}