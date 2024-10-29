# this is commented out but it's here for reference if the deploy script needs to be updated
#cd /home/forge/local.wildatheart.org
#git stash
#git pull origin $FORGE_SITE_BRANCH

# this is what will execute on the forge server
if [[ $FORGE_DEPLOY_MESSAGE =~ "[BOT]" ]]; then
    echo "AUTO-COMMITTED ON PRODUCTION. NOTHING TO DEPLOY."
    exit 0
fi

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

$FORGE_PHP artisan cache:clear

$FORGE_PHP artisan config:clear
$FORGE_PHP artisan config:cache

$FORGE_PHP artisan view:clear
$FORGE_PHP artisan view:cache

$FORGE_PHP artisan event:clear
$FORGE_PHP artisan event:cache

$FORGE_PHP artisan route:clear
$FORGE_PHP artisan route:cache

$FORGE_PHP artisan horizon:terminate
$FORGE_PHP artisan queue:restart
$FORGE_PHP artisan migrate --force

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

npm install
npm run build



