<?php

namespace GiacomoMasseroni\LaravelModelsGenerator\Contracts;

interface DriverConnectorInterface
{
    public function connectionParams(): array;
}
