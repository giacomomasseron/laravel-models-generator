<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Drivers;

class DriverConnector
{
    public function __construct(
        protected ?string $connection = null,
        protected ?string $schema = null,
        protected ?string $table = null,
    ) {}
}
