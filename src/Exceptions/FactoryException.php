<?php

namespace Thettler\LaravelFactoryClasses\Exceptions;

class FactoryException extends \Exception
{
    public static function message(string $message): void
    {
        throw new static($message);
    }
}
