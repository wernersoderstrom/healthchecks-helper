<?php

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Scheduling\Schedule;
use Wernersoderstrom\HealthchecksHelper\HealthchecksHelper;

const UUID = '5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b';

beforeEach(function () {
    $this->pings = [];

    $stack = HandlerStack::create(new MockHandler(array_fill(0, 5, new Response)));
    $stack->push(Middleware::history($this->pings));

    $this->app->instance(ClientInterface::class, new Client(['handler' => $stack]));
});

function runScheduledEvent($event): void
{
    $event->run(app());
}

function pingedUrls(array $pings): array
{
    return array_map(fn ($ping) => (string) $ping['request']->getUri(), $pings);
}

it('pings start and success when the job succeeds', function () {
    $event = app(Schedule::class)->call(fn () => null)->healthchecks(UUID);

    runScheduledEvent($event);

    expect(pingedUrls($this->pings))->toBe([
        'https://hc-ping.com/'.UUID.'/start',
        'https://hc-ping.com/'.UUID,
    ]);
});

it('pings start and fail when the job fails', function () {
    $event = app(Schedule::class)->call(fn () => throw new RuntimeException('Boom'))->healthchecks(UUID);

    try {
        runScheduledEvent($event);
    } catch (RuntimeException) {
    }

    expect(pingedUrls($this->pings))->toBe([
        'https://hc-ping.com/'.UUID.'/start',
        'https://hc-ping.com/'.UUID.'/fail',
    ]);
});

it('uses the ping key for slugs', function () {
    config()->set('healthchecks-helper.ping_key', 'my-ping-key');
    $this->app->forgetInstance(HealthchecksHelper::class);

    runScheduledEvent(app(Schedule::class)->call(fn () => null)->healthchecks('nightly-backup'));

    expect(pingedUrls($this->pings))->toBe([
        'https://hc-ping.com/my-ping-key/nightly-backup/start',
        'https://hc-ping.com/my-ping-key/nightly-backup',
    ]);
});

it('uses a custom ping url', function () {
    config()->set('healthchecks-helper.ping_url', 'https://healthchecks.example.com/ping/');
    $this->app->forgetInstance(HealthchecksHelper::class);

    runScheduledEvent(app(Schedule::class)->call(fn () => null)->healthchecks(UUID));

    expect(pingedUrls($this->pings))->toBe([
        'https://healthchecks.example.com/ping/'.UUID.'/start',
        'https://healthchecks.example.com/ping/'.UUID,
    ]);
});

it('throws when a slug is used without a ping key', function () {
    app(Schedule::class)->call(fn () => null)->healthchecks('nightly-backup');
})->throws(InvalidArgumentException::class);

it('does not ping when disabled', function () {
    config()->set('healthchecks-helper.enabled', false);

    runScheduledEvent(app(Schedule::class)->call(fn () => null)->healthchecks(UUID));

    expect($this->pings)->toBeEmpty();
});
