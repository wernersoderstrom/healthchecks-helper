<?php

namespace Lasselehtinen\HealthchecksHelper\Tests;

use Lasselehtinen\HealthchecksHelper\HealthchecksHelperServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            HealthchecksHelperServiceProvider::class,
        ];
    }
}
