<?php

namespace GiacomoMasseroni\LaravelModelsGenerator\Exceptions;

use Throwable;

class DatabaseDriverNotFound extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
