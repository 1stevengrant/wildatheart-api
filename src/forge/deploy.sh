cd /home/forge/local.wildatheart.org
git stash
git pull origin $FORGE_SITE_BRANCH

if [[ $FORGE_DEPLOY_MESSAGE =~ "[BOT]" ]]; then
    echo "AUTO-COMMITTED ON PRODUCTION. NOTHING TO DEPLOY."
    exit 0
fi


export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"

# Respect .nvmrc if present; fallback to Node 20
if [ -f .nvmrc ]; then
    nvm install
    nvm use
else
    nvm install 20
    nvm use 20
fi

echo "Node: $(node -v), npm: $(npm -v)"


# Dependencies first
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci

# Generate TypeScript types from PHP before building
php artisan statamic:generate-types --skip-pint
php artisan typescript:transform

# Build assets before clearing caches
npm run build

# Database/queue management (safe to run under old opcache)
php artisan migrate --force
php artisan horizon:terminate

# Drop stale caches before rebuilding them on fresh code
php artisan optimize:clear

# Rebuild caches
php artisan optimize
php artisan event:cache

# FPM reload last — this is what actually flushes opcache for running workers
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock
