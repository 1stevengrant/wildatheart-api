cd /home/forge/local-staging.wildatheart.org
git stash
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader


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


$FORGE_PHP artisan db:wipe
$FORGE_PHP artisan db:production-sync

$FORGE_PHP artisan migrate --force

if $FORGE_PHP artisan list --format=txt | grep -q 'staging:seed-fixtures'; then
    $FORGE_PHP artisan staging:seed-fixtures --no-interaction
fi

$FORGE_PHP artisan optimize:clear
$FORGE_PHP artisan optimize

$FORGE_PHP artisan horizon:terminate
$FORGE_PHP artisan queue:restart

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

npm ci
npm run build
