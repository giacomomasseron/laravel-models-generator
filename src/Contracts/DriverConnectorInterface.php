<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator\Contracts;

interface DriverConnectorInterface
{
    public function connectionParams(): array;
}
