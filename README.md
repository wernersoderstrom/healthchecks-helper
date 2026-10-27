# Healthchecks helper for the Laravel scheduler

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wernersoderstrom/healthchecks-helper.svg?style=flat-square)](https://packagist.org/packages/wernersoderstrom/healthchecks-helper)
[![GitHub Tests Action Status](https://github.com/wernersoderstrom/healthchecks-helper/actions/workflows/run-tests.yml/badge.svg)](https://github.com/wernersoderstrom/healthchecks-helper/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/wernersoderstrom/healthchecks-helper/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/wernersoderstrom/healthchecks-helper/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wernersoderstrom/healthchecks-helper.svg?style=flat-square)](https://packagist.org/packages/wernersoderstrom/healthchecks-helper)

Adds a `healthchecks()` method to scheduled events, so instead of:

```php
Schedule::command('backup:run')
    ->daily()
    ->pingBefore('https://hc-ping.com/5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b/start')
    ->pingOnSuccess('https://hc-ping.com/5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b')
    ->pingOnFailure('https://hc-ping.com/5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b/fail');
```

you can write:

```php
Schedule::command('backup:run')
    ->daily()
    ->healthchecks('5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b');
```

## Installation

You can install the package via composer:

```bash
composer require wernersoderstrom/healthchecks-helper
```

That's all you need if you use check UUIDs and ping the hosted Healthchecks.io service. Pings are enabled by default and go to `https://hc-ping.com`, so you don't need to publish the config file or set any environment variables.

Set the environment variables below, or publish the config file, only when you want to:

- disable pings in some environments (`HEALTHCHECKS_ENABLED=false`)
- ping a self-hosted Healthchecks instance (`HEALTHCHECKS_PING_URL`)
- refer to checks by slug instead of UUID (`HEALTHCHECKS_PING_KEY`)

The environment variables are enough for all of these. Publish the config file only if you'd rather manage the values there:

```bash
php artisan vendor:publish --tag="healthchecks-helper-config"
```

This is the contents of the published config file:

```php
return [
    'enabled' => env('HEALTHCHECKS_ENABLED', true),
    'ping_url' => env('HEALTHCHECKS_PING_URL', 'https://hc-ping.com'),
    'ping_key' => env('HEALTHCHECKS_PING_KEY'),
];
```

## Usage

Pass either the check's UUID or its slug:

```php
// By UUID
Schedule::command('backup:run')->daily()->healthchecks('5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b');

// By slug, which requires HEALTHCHECKS_PING_KEY to be set
Schedule::command('backup:run')->daily()->healthchecks('nightly-backup');
```

### When do I need `HEALTHCHECKS_PING_KEY`?

Only when you refer to checks by slug. Healthchecks supports two ping URL formats:

| Format | URL |
|---|---|
| UUID | `https://hc-ping.com/5b3f6a5e-8c1d-4b8e-9f2a-1c3d4e5f6a7b` |
| Slug | `https://hc-ping.com/<ping-key>/nightly-backup` |

A UUID is unique on its own, so the URL needs nothing else. A slug is only unique within one Healthchecks project, so the URL also needs the project's ping key. You can find it in the Healthchecks dashboard under **Project Settings → Ping Key**.

The package decides which format to use from the value you pass:

- A valid UUID is always pinged directly, and the ping key is ignored.
- Anything else is treated as a slug and combined with `HEALTHCHECKS_PING_KEY`. If the key isn't set, an `InvalidArgumentException` is thrown, so a missing key doesn't go unnoticed.

Slugs make the schedule easier to read and mean you keep one secret in `.env` instead of a UUID per task. They also let staging and production use the same slugs, if each environment has its own Healthchecks project and ping key. Note that anyone with the ping key can ping every check in the project, so keep it out of version control.

If you only use UUIDs, you can leave `HEALTHCHECKS_PING_KEY` unset.

### Pings sent

Each run sends a `/start` ping before the job, then either a success ping or a `/fail` ping depending on the outcome. Healthchecks uses the start ping to measure how long the job takes.

Set `HEALTHCHECKS_ENABLED=false` to stop pings in local or testing environments. If you self-host Healthchecks, point `HEALTHCHECKS_PING_URL` at your instance's ping endpoint, for example `https://healthchecks.example.com/ping`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Lasse Lehtinen](https://github.com/lasselehtinen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
