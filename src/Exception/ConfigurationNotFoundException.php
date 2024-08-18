<?php

namespace Sholokhov\BitrixOption\Exception;

use Throwable;

class ConfigurationNotFoundException extends SystemException
{
    public function __construct(string $message = '', Throwable $provider = null)
    {
        parent::__construct($message, 404, $provider);
    }
}