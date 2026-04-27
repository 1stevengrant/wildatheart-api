# CI/CD Pipeline Implementation Plan

## Context

The project has 4 existing workflows (preview-provision, preview-teardown, test-staging, sync-types) but **no PR quality checks** and **no automated production deployment**. This plan adds fast PR feedback, automated deploys with tagging, and a PR template.

## Overview

| File | Action | Purpose |
|------|--------|---------|
| `.github/workflows/pr.yml` | Create | PR quality gates |
| `.github/workflows/deploy.yml` | Create | Auto-deploy + tagging on merge to prod |
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

## 4. Production Deploy Workflow

### `.github/workflows/deploy.yml`

Triggers on push to `prod` (i.e., merged PRs). Migrations run inside Forge's deploy script, not via SSH from GitHub Actions.

```yaml
name: Deploy to Production

on:
  push:
    branches: [prod]

jobs:
  deploy:
    name: Deploy & Tag
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Trigger Forge deployment
        run: |
          RESPONSE=$(curl -X POST "${{ secrets.FORGE_PRODUCTION_TRIGGER_URL }}" \
            -H "Authorization: Bearer ${{ secrets.FORGE_API_TOKEN }}" \
            -H "Accept: application/json" \
            -s -w "\n%{http_code}")
          HTTP_STATUS=$(echo "$RESPONSE" | tail -n1)
          BODY=$(echo "$RESPONSE" | sed '$ d')

          echo "Status Code: $HTTP_STATUS"
          echo "API Response: $BODY"

          if [ "$HTTP_STATUS" -ne 200 ]; then
            echo "Error: API request failed with status $HTTP_STATUS"
            echo "Response: $BODY"
            exit 1
          fi

      - name: Wait for deployment to complete
        run: |
          STATUS="pending"
          MAX_ATTEMPTS=120
          ATTEMPT=0

          while [ "$STATUS" != "finished" ]; do
            if [ $ATTEMPT -ge $MAX_ATTEMPTS ]; then
              echo "Error: Deployment timed out after 20 minutes"
              exit 1
            fi

            RESPONSE=$(curl -X GET "https://forge.laravel.com/api/v1/servers/${{ secrets.FORGE_SERVER_ID }}/sites/${{ secrets.FORGE_PRODUCTION_SITE_ID }}/deployment-history" \
              -H "Authorization: Bearer ${{ secrets.FORGE_API_TOKEN }}" \
              -H "Accept: application/json" \
              -s)

            if ! STATUS=$(echo $RESPONSE | jq -r '.deployments[0].status'); then
              echo "Error: Failed to parse status from response"
              echo "Response: $RESPONSE"
              exit 1
            fi

            echo "Current status: $STATUS (attempt $ATTEMPT)"

            if [ "$STATUS" = "failed" ]; then
              echo "Error: Deployment failed"
              exit 1
            fi

            ATTEMPT=$((ATTEMPT + 1))
            sleep 10
          done

          echo "Deployment finished successfully"

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
| `FORGE_PRODUCTION_TRIGGER_URL` | Forge → Sites → Production → Deployment Trigger URL | **New — must add** |
| `FORGE_PRODUCTION_SITE_ID` | Forge → Sites → Production → numeric site ID in URL | **New — must add** |
| `FORGE_API_TOKEN` | Already exists | Used by preview + staging workflows |
| `FORGE_SERVER_ID` | Already exists | Used by preview + staging workflows |

## 7. GitHub Settings (Manual, After Merge)

- **Branch protection on `prod`**: Require 1 approval + require `quality` status check to pass + no direct pushes
- **Staging branch**: Leave as-is for now, deprecate later

## 8. Existing Workflows — No Changes

- `sync-types.yml` — Keep as-is, PR workflow adds a "types freshness" check separately
- `test-staging.yml` — Keep as-is, Playwright E2E tests are too expensive for PR workflow (~20+ min). Future: convert to post-deploy validation when staging is deprecated

## 9. Verification Steps

1. Install larastan, generate baseline, verify `phpstan analyse` passes
2. Create a feature branch, push a PR to `prod`
3. Verify all PR checks run and pass (target: < 10 minutes)
4. Merge to `prod`, verify deploy workflow triggers, creates tag, deploys via Forge
5. Check GitHub tags for `deploy-YYYY-MM-DD-1` format
