# Testing

This application uses the [PEST testing framework](https://pestphp.com/).

## Running tests

```bash
composer test
```

### Available commands

| Command | Description |
|---|---|
| `composer test` | Parallel tests (8 processes), excludes comparison tests |
| `composer test:sequential` | Non-parallel tests, excludes comparison tests |
| `composer test:fast` | Parallel tests without coverage, excludes comparison tests |
| `composer test:comparison` | Pre-rollout comparison tests (requires `php artisan db:production-sync`) |
| `composer test:unit` | Unit tests only |
| `composer test:feature` | Feature tests only (4 parallel processes) |

## Comparison tests

Comparison tests (`--group=comparison`) make real HTTP requests to compare local vs production API responses. They require a production database sync and are excluded from the default test suite. Run them separately before deploying upgrades:

```bash
php artisan db:production-sync
composer test:comparison
```

## Test database cleanup

Parallel test runs create temporary databases (`wahlocal_test_test_1`, `wahlocal_test_test_2`, etc.). These are automatically cleaned up after each parallel test command finishes. To clean them up manually:

```bash
php artisan test:cleanup-databases
```

## Configuration

- **Test database**: `wahlocal_test`
- **Framework**: PEST with Laravel plugin
- **Memory**: 256MB+ recommended for Herd users
- **Environment**: Copy `.env.testing.example` to `.env.testing` and fill in the missing variables
