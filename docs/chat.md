# TypeScript Type Sync Workflow - Implementation Notes

**Date:** 2026-01-14
**Branch:** `feature/statamic-to-typescript`

## Objective

Sync generated TypeScript types (`generated.d.ts` and `statamic.d.ts`) from the Laravel API repo to the Astro frontend repo (`wild-at-heart/wildatheart.org-astro`) via GitHub Actions.

## Type Generation Pipeline

The project uses a two-stage type generation system:

1. **Stage 1**: `php artisan statamic:generate-types` - Converts Statamic YAML blueprints → PHP DTOs
2. **Stage 2**: `php artisan typescript:transform` - Converts PHP DTOs → TypeScript definitions

**Output files:**
- `resources/js/types/generated.d.ts` - Types from PHP DTOs (Spatie Laravel Data)
- `resources/js/types/statamic.d.ts` - Types from Statamic blueprints

## Workflow Created

File: `.github/workflows/sync-types.yml`

**Configuration:**
| Setting | Value |
|---------|-------|
| Target repo | `wild-at-heart/wildatheart.org-astro` |
| Target path | `src/types/` |
| Trigger branches | `main`, `prod` |
| PR base branch | `develop` |

**Key workflow decisions:**
- Uses `--no-scripts` with composer install (avoids Redis dependency from `statamic:install`)
- Skips database migrations (type generation only reads YAML files and PHP classes)
- Creates PRs in Astro repo when types change

## Issues Encountered

### 1. Redis Connection Refused
**Error:** `statamic:install` post-autoload script requires Redis
**Fix:** Added `--no-scripts` to `composer install`

### 2. SQLite Migration Conflict
**Error:** Index already exists during migration
**Fix:** Removed database setup entirely - not needed for type generation

### 3. Classic PAT Forbidden
**Error:** `wild-at-heart` forbids access via classic PAT
**Status:** Organization requires fine-grained PAT or GitHub App

## Authentication: GitHub App (Chosen Approach)

Classic PATs are forbidden by `wild-at-heart` org. Using GitHub App instead.

### Step 1: Create GitHub App
1. Go to `github.com/organizations/wild-at-heart/settings/apps`
2. Click "New GitHub App"
3. Settings:
   - **Name**: `WAH Type Sync`
   - **Homepage URL**: `https://github.com/wild-at-heart/local.wildatheart.org`
   - **Webhook**: Uncheck "Active"
4. Repository permissions:
   - Contents: Read & Write
   - Pull requests: Read & Write
   - Metadata: Read-only (auto-selected)
5. Select "Only on this account"
6. Note the **App ID** after creation

### Step 2: Generate Private Key
1. On app settings page → "Private keys" section
2. Click "Generate a private key"
3. Save the downloaded `.pem` file

### Step 3: Install on Repositories
1. App settings → "Install App" → Click "Install"
2. Select "Only select repositories"
3. Choose: `local.wildatheart.org` and `wildatheart.org-astro`

### Step 4: Add Secrets
Go to `github.com/wild-at-heart/local.wildatheart.org/settings/secrets/actions`

| Secret | Value |
|--------|-------|
| `GH_APP_ID` | App ID from step 1 |
| `GH_APP_PRIVATE_KEY` | Full contents of .pem file |

### Step 5: Test
1. Push to `feature/statamic-to-typescript` branch
2. Check Actions tab for workflow success

## Next Steps

- [ ] Create GitHub App in wild-at-heart org
- [ ] Generate private key
- [ ] Install app on both repos
- [ ] Add `GH_APP_ID` secret
- [ ] Add `GH_APP_PRIVATE_KEY` secret
- [ ] Test workflow end-to-end
- [ ] Remove `feature/statamic-to-typescript` from trigger branches after testing

## Commits Made

1. `5093733d` - Add GitHub Action to sync TypeScript types to Astro repo
2. `6072430a` - Add feature branch to sync-types workflow for testing
3. `02cdb81e` - Fix: skip composer scripts to avoid Redis dependency
4. `a900e2fc` - Fix: remove database/migrations (not needed for type generation)
