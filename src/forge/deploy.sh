cd /home/forge/local.wildatheart.org
git stash
git pull origin $FORGE_SITE_BRANCH

if [[ $FORGE_DEPLOY_MESSAGE =~ "[BOT]" ]]; then
    echo "AUTO-COMMITTED ON PRODUCTION. NOTHING TO DEPLOY."
    exit 0
fi

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

$FORGE_PHP artisan optimize:clear
$FORGE_PHP artisan optimize

$FORGE_PHP artisan horizon:terminate
$FORGE_PHP artisan queue:restart
$FORGE_PHP artisan migrate --force

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
nvm install
nvm use

rm -rf node_modules/@statamic/cms
npm install
npm run build
