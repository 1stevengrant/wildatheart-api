#!/usr/bin/env bash

set -euo pipefail

cd /home/forge/local-staging.wildatheart.org
php artisan staging:seed-fixtures --no-interaction
