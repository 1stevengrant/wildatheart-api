# Deployment

The application runs on [Laravel Cloud](https://cloud.laravel.com). Production deploys from GitHub Actions, only after the tests pass.

## How production deploys

1. A PR merges into `prod`.
2. The **Tests** workflow (`.github/workflows/tests.yml`) runs Pest on that commit.
3. If the tests pass, its **Deploy to Laravel Cloud** job calls the production deploy hook with that exact commit (`?commit_hash=`), so a commit is only deployed once it has been tested.
4. When the `LARAVEL_CLOUD_TOKEN` secret is set, the job then waits for Cloud to finish deploying that commit and fails if the deployment fails or is cancelled.

This relies on the following being set up:

| Where | Setting |
|---|---|
| Laravel Cloud, production environment, **Settings > Deployments** | **Push to deploy** turned off, otherwise every push deploys before the tests run. **Deploy hook** turned on |
| GitHub secret `LARAVEL_CLOUD_DEPLOY_HOOK` | The deploy hook URL. Anyone with it can deploy production, so treat it like a password |
| GitHub secret `LARAVEL_CLOUD_TOKEN` (optional) | A Cloud API token from **Organization settings > API tokens**, scoped to viewing deployments on the production environment |
| GitHub branch protection on `prod` | Require a pull request and the **Pest** check, so nothing reaches `prod` untested |

Deploys run one at a time (the job uses a `production-deploy` concurrency group), so two quick merges deploy in order.

## Workflow

The deployment workflow is as follows:

-   feature branch is pull requested against `prod`
-   the PR needs passing tests and approval before it can be merged
-   to try a branch on real infrastructure first, use its Laravel Cloud preview environment, or merge it into `staging`

## Staging

The `staging` branch deploys to the Laravel Cloud `staging` environment, which serves `local-staging.wildatheart.org`. It holds work that isn't released yet, and the Astro site's preview builds read its API. Push to deploy is on, so every push or merge to `staging` deploys straight away, without the test gate production has. Merge `prod` into `staging` to bring it up to date. Never reset `staging` to `prod`, because that would drop the unreleased work.

Staging has its own `staging` schema on the same MySQL cluster as production, and its own cache. Its users, events and Fire schedules are its own and survive deploys. Deploys don't copy production's database. The only production data staging takes is CMS content, which `php artisan staging:sync-content` copies (see `docs/database.md` on the `staging` branch). `php artisan staging:seed-fixtures` creates or resets the `[TEST]` events and contacts. Run it by hand, not as a deploy command, because every run resets the test Fires and it fails when one of its four host users is missing.

## Feature branch previews

Laravel Cloud creates preview environments for branches, so there is no GitHub Action or PR title convention to trigger one. See the [preview environments](https://cloud.laravel.com/docs/preview-environments) docs for how they are configured.

## Queues

Jobs run on Laravel Cloud managed queues (`default` and `mail`), not Horizon. Failed jobs are inspected and retried from the Queues dashboard under the environment's Monitoring tab, because `queue:failed` and `queue:retry` do not work with managed queues.
