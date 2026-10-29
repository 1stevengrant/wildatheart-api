# CI/CD Pipeline Implementation Plan

## Context

Production runs on Laravel Cloud, which creates preview environments per branch. The project has 2 workflows (tests, sync-types). `tests.yml` runs Pest and the production parity tests on PRs, and deploys `prod` to Cloud once they pass. There are still **no style, static analysis or type checks** and **no record of what was deployed when**. This plan adds those quality gates, a deploy tag after each successful deployment, and a PR template.

## Overview

| File | Action | Purpose |
|------|--------|---------|
| `.github/workflows/pr.yml` | Create | PR quality gates |
| `.github/workflows/tests.yml` | Done | Deploys `prod` to Cloud after the tests pass (tagging still to add) |
| `.github/pull_request_template.md` | Create | Standardized PR descriptions |
| `phpstan.neon` | Create | PHPStan/Larastan config |
| `phpstan-baseline.neon` | Generate | Baseline for existing violations |
| `composer.json` | Modify | Add larastan dev dependency |

---

## 1. Install Larastan

```bash
composer require --dev larastan/larastan:^3.0
```

> **Note:** Larastan v3 is required for PHPStan 2.x (ships with PHP 8.4 support). The plan originally called for `^2.0` but that's incompatible with the current PHP/Laravel versions.

## 2. PHPStan Configuration

### `phpstan.neon`

```neon
includes:
    - vendor/larastan/larastan/extension.neon
    - phpstan-baseline.neon

parameters:
    level: 3
    paths:
        - app
        - config
        - database
        - routes
    excludePaths:
        - bootstrap/cache
        - storage
        - vendor
```

### Generate Baseline

```bash
vendor/bin/phpstan analyse --generate-baseline --no-progress
```

This creates `phpstan-baseline.neon` containing all existing violations (~512 errors at level 3). The baseline means the PR workflow only fails on **new** issues.

### Verify

```bash
vendor/bin/phpstan analyse --no-progress
# Should output: [OK] No errors
```

---

## 3. PR Quality Gates Workflow

### `.github/workflows/pr.yml`

```yaml
name: PR Quality Gates

on:
  pull_request:
    branches: [prod]

jobs:
  quality:
    name: Quality Checks
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: wahlocal_test
          MYSQL_ROOT_PASSWORD: root
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.4"
          extensions: mbstring, xml, ctype, json, curl, pdo, pdo_mysql, dom, libxml
          coverage: none

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install PHP dependencies
        run: composer install --no-interaction --prefer-dist --no-scripts

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: "22"
          cache: "npm"

      - name: Install Node dependencies
        run: npm ci

      - name: Prepare environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Pint (code style)
        run: vendor/bin/pint --test

      - name: PHPStan (static analysis)
        run: vendor/bin/phpstan analyse --no-progress --error-format=github

      - name: TypeScript (type check)
        run: npm run types

      - name: Generated types freshness
        run: |
          php artisan typescript:transform
          if ! git diff --exit-code resources/js/types/generated.d.ts; then
            echo "::error::Generated TypeScript types are out of date. Run 'php artisan typescript:transform' and commit the changes."
            exit 1
          fi

      - name: Run database migrations
        run: php artisan migrate --force
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: wahlocal_test
          DB_USERNAME: root
          DB_PASSWORD: root

      - name: Seed database
        run: php artisan db:seed --force
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: wahlocal_test
          DB_USERNAME: root
          DB_PASSWORD: root

      - name: Run tests (Pest)
        run: composer run test:fast
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: wahlocal_test
          DB_USERNAME: root
          DB_PASSWORD: root
```

---

## 4. Production Deploys

**Done.** Production no longer uses Cloud's push to deploy. The `deploy` job in `.github/workflows/tests.yml` runs after the tests pass on `prod`, calls the Cloud deploy hook with the tested commit, and (with `LARAVEL_CLOUD_TOKEN` set) waits for that deployment to succeed. See [deployment](deployment.md) for the settings it relies on.

Still to do from the original plan: tag each successful deploy. Add a step to the end of that job, after the wait step:

```yaml
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Create deploy tag
        run: |
          DATE=$(date -u +%Y-%m-%d)
          EXISTING=$(git tag -l "deploy-${DATE}-*" | wc -l | tr -d ' ')
          NEXT=$((EXISTING + 1))
          TAG="deploy-${DATE}-${NEXT}"

          git tag "$TAG"
          git push origin "$TAG"

          echo "Tagged: $TAG"
```

The job needs `permissions: contents: write` to push the tag, and the tag only means "deployed" when the wait step ran, so add it once `LARAVEL_CLOUD_TOKEN` is set.

---

## 5. PR Template

### `.github/pull_request_template.md`

```markdown
## What
<!-- One line describing the change -->

## How to test
<!-- Steps for reviewer/PM -->

## Checklist
- [ ] Migration is safe to run on production (or N/A)
- [ ] Pint passes (`vendor/bin/pint`)
- [ ] No debug code (`dd()`, `dump()`, `console.log()`)
```

---

## 6. GitHub Secrets Required

| Secret | Source | Notes |
|--------|--------|-------|
| `LARAVEL_CLOUD_DEPLOY_HOOK` | Cloud production environment, **Settings > Deployments > Deploy hook** | **New, must add**. Used by the deploy job in `tests.yml` |
| `LARAVEL_CLOUD_TOKEN` | Cloud **Organization settings > API tokens** | **New, optional**. Lets the deploy job wait for the deployment to finish |
| `REMOTE_DATABASE_HOST`, `REMOTE_DATABASE_USERNAME`, `REMOTE_DATABASE_NAME`, `REMOTE_DATABASE_PASSWORD` | `cloud database-cluster:list --json --show-sensitive -n` | Already exist, but **must be updated** to the Cloud database. `tests.yml` uses them for the parity tests |

The Forge secrets (`FORGE_API_TOKEN`, `FORGE_SERVER_ID`, `FORGE_SSH_PRIVATE_KEY` and the Forge trigger URLs) are no longer used by `tests.yml` or this plan. Staging has been retired, so `FORGE_API_TOKEN`, `FORGE_SERVER_ID` and `FORGE_STAGING_TRIGGER_URL` can go too.

## 7. GitHub Settings (Manual, After Merge)

- **Branch protection on `prod`**: Require 1 approval + require `quality` status check to pass + no direct pushes
- **Staging branch**: retired, along with the local-staging site

## 8. Existing Workflows

- `tests.yml`: keeps running Pest and the production parity tests. The parity tests sync production with mydumper over the Cloud public endpoint (no SSH), so the runner installs mydumper first.
- `sync-types.yml`: keep as is. The PR workflow adds a "types freshness" check separately.
- `test-staging.yml`: removed with staging. Its Playwright integration tests (from `wildatheart.org-astro`) have no target until they are pointed at a Cloud preview environment or production.

## 9. Verification Steps

1. Install larastan, generate baseline, verify `phpstan analyse` passes
2. Create a feature branch, push a PR to `prod`
3. Verify all PR checks run and pass (target: < 10 minutes)
4. Merge to `prod`, verify the deploy job only runs after Pest passes and that Cloud deploys that commit
5. Check GitHub tags for `deploy-YYYY-MM-DD-1` format
