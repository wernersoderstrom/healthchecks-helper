<?php

namespace Wernersoderstrom\HealthchecksHelper\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Wernersoderstrom\HealthchecksHelper\HealthchecksHelperServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            HealthchecksHelperServiceProvider::class,
        ];
    }
}
