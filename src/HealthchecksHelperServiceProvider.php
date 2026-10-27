<?php

namespace Wernersoderstrom\HealthchecksHelper;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\ServiceProvider;

class HealthchecksHelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/healthchecks-helper.php', 'healthchecks-helper');

        $this->app->singleton(HealthchecksHelper::class, fn ($app) => new HealthchecksHelper(
            $app['config']->get('healthchecks-helper.ping_url', 'https://hc-ping.com'),
            $app['config']->get('healthchecks-helper.ping_key'),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/healthchecks-helper.php' => config_path('healthchecks-helper.php'),
            ], 'healthchecks-helper-config');
        }

        Event::macro('healthchecks', function (string $check): Event {
            /** @var Event $this */
            if (! config('healthchecks-helper.enabled')) {
                return $this;
            }

            $healthchecks = app(HealthchecksHelper::class);

            return $this
                ->pingBefore($healthchecks->url($check, 'start'))
                ->pingOnSuccess($healthchecks->url($check))
                ->pingOnFailure($healthchecks->url($check, 'fail'));
        });
    }
}
