<?php

namespace Synapse\Exceptions;

use Throwable;

class Route extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function output(): void
    {
        $html = file_get_contents(dirname(__DIR__) . '/Docs/exception.html');
        $html = str_replace(['[CODE]', '[TITLE]', '[MESSAGE]'], [$this->code, 'Route Error', $this->message], $html);

        echo $html;
        die();
    }
}