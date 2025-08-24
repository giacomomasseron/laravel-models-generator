<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Exceptions;

use Throwable;

class DatabaseDriverNotFound extends LaravelModelsGeneratorException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
