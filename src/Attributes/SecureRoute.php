<?php

namespace Synapse\Attributes;

#[\Attribute]
class SecureRoute extends Route
{
    private string $redirect = '';

    public function __construct(string|array $url, string $redirect = '', string $method = 'GET', string $language = 'en')
    {
        parent::__construct($url, $method, $language);
        $this->redirect = $redirect;
    }

    public function verify()
    {
        // TODO: Implement this feature

        if ($this->redirect !== '') {
            header('location: ' . $this->redirect);
            exit();
        }
    }
}