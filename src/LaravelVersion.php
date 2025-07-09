<?php

declare(strict_types=1);

namespace GiacomoMasseroni\LaravelModelsGenerator;

class LaravelVersion
{
    public int $major;

    public int $minor;

    public int $patch;

    public function __construct(
    ) {
        $version = explode('.', app()->version());

        $this->major = (int) $version[0];
        $this->minor = (int) $version[1];
        $this->patch = (int) $version[2];
    }

    public function check(int $major, int $minor = 0, int $patch = 0): bool
    {
        if ($this->major > $major) {
            return true;
        }

        if ($this->major < $major) {
            return false;
        }

        if ($this->minor > $minor) {
            return true;
        }

        if ($this->minor < $minor) {
            return false;
        }

        return $this->patch >= $patch;
    }
}
