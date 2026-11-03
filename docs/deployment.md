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
-   to try a branch on real infrastructure first, use its Laravel Cloud preview environment (there is no separate staging site any more)

## Feature branch previews

Laravel Cloud creates a preview environment for every PR into `prod`, so there is no PR title convention to trigger one. See the [preview environments](https://cloud.laravel.com/docs/preview-environments) docs for how they are configured. Each preview gets its own database, compute and bucket, scales to zero when idle, and is deleted when the PR is merged or closed.

A preview's database starts empty, but the frontend cannot build without content, so the **Preview environment** workflow (`.github/workflows/preview.yml`) runs on every PR into `prod`. Once Cloud has deployed the PR's commit it:

1. Copies production into the preview database with `db:production-sync`, the first time only. Content tables come across whole. User, event and other personal data tables keep their structure but no rows.
2. Runs the PR's migrations and `StagingFixturesSeeder`, so a few test hosts and events exist.
3. Starts the frontend CI in `wildatheart.org-astro`, which builds the branch named in the `FRONTEND_PREVIEW_REF` variable (normally the current release branch) against the preview under the alias `api-<branch>`, and comments both URLs on the PR.

A frontend PR can also use the preview, by naming the branch in an `API-Preview:` line in its description or by sharing the ClickUp task ID in its branch name. See "Pairing with an API preview" in that repo's `docs/deployment.md`.

This relies on the following being set up:

| Where | Setting |
|---|---|
| Laravel Cloud, production environment, **Settings > Preview environments** | An automation with **Delete on merge or close** on, new resources for compute, database and bucket, and `APP_ENV=staging` so the fixtures seeder runs. Previews do not inherit production's variables, so set the rest there too. Use `MAIL_MAILER=log`, and do not link secrets for anything that writes to a real system (the Auth0 Management client in particular) |
| GitHub secret `LARAVEL_CLOUD_TOKEN` | Must be able to read environments and deployments, update database clusters and run commands, not just view production deployments |
| GitHub secrets `REMOTE_DATABASE_*` | The production database credentials the parity tests also use. Production's public endpoint must be on, because the runner connects from outside Cloud |
| GitHub App (`GH_APP_ID`, `GH_APP_PRIVATE_KEY`) | The types sync app, which also needs **Actions: Read & Write** on `wildatheart.org-astro`, accepted on the org installation |
| GitHub variable `FRONTEND_PREVIEW_REF` (optional) | The `wildatheart.org-astro` branch to build, normally the current release branch (for example `releases/event-hosts`). Defaults to `develop`. That branch's `ci.yml` must have the `workflow_dispatch` trigger |
| GitHub variable `FRONTEND_PREVIEW_HOST` (optional) | The staging Worker's host, `staging-astro.wild-at-heart-org.workers.dev`, so the PR comment links the frontend directly |

The import opens the preview database's public endpoint while it runs and closes it again afterwards.

## Queues

Jobs run on Laravel Cloud managed queues (`default` and `mail`), not Horizon. Failed jobs are inspected and retried from the Queues dashboard under the environment's Monitoring tab, because `queue:failed` and `queue:retry` do not work with managed queues.
