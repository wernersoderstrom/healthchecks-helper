<?php

namespace Lasselehtinen\HealthchecksHelper;

use Illuminate\Support\Str;
use InvalidArgumentException;

class HealthchecksHelper
{
    public function __construct(
        protected string $pingUrl,
        protected ?string $pingKey = null,
    ) {}

    /**
     * Get the ping URL for the given check UUID or slug.
     */
    public function url(string $check, string $signal = ''): string
    {
        $url = rtrim($this->pingUrl, '/').'/'.$this->path($check);

        return $signal === '' ? $url : $url.'/'.$signal;
    }

    protected function path(string $check): string
    {
        if (Str::isUuid($check)) {
            return $check;
        }

        if (blank($this->pingKey)) {
            throw new InvalidArgumentException("Pinging check [{$check}] by slug requires a ping key. Set HEALTHCHECKS_PING_KEY or use the check's UUID.");
        }

        return $this->pingKey.'/'.$check;
    }
}
