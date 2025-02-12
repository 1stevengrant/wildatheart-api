# Deployment

With code in GitHub, we make use of GitHub Actions to deploy the application to a Hetzner server configured with Laravel Forge.

## Workflow

The deployment workflow is as follows:
- feature branch is pull requested against `staging` branch
- `staging` branch is pull requested against `prod` branch and needs to wait on passing tests and approval before it can be merged

## Feature branch previews
If you want to preview your feature branch before merging it into `staging`, you can PR against `staging` and add `[wah]` to the title of the PR. This will cause a GitHub Action to deploy the feature branch to a temporary URL on Forge with the URL `https://pr-<prNumber>.gondor.wildatheart.org`.

This will include a copy of the production database, so you can test your feature with real data.
