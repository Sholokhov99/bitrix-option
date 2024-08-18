<?php

namespace Sholokhov\BitrixOption\Exception;

use Throwable;

class InvalidValueException extends SystemException
{
    public function __construct(string $message = '', Throwable $provider = null)
    {
        parent::__construct($message, 401, $provider);
    }
}