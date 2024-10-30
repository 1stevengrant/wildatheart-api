
#!/bin/bash

# this is commented out but it's here for reference if the deploy script needs to be updated
#cd /home/forge/local.wildatheart.org
#git stash
#git pull origin $FORGE_SITE_BRANCH

#if [[ $FORGE_DEPLOY_MESSAGE =~ "[BOT]" ]]; then
#    echo "AUTO-COMMITTED ON PRODUCTION. NOTHING TO DEPLOY."
#    exit 0
#fi

# this is what will execute on the forge server

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

php artisan cache:clear

php artisan config:clear
php artisan config:cache

php artisan view:clear
php artisan view:cache

php artisan event:clear
php artisan event:cache

php artisan route:clear
php artisan route:cache

php artisan horizon:terminate
php artisan queue:restart
php artisan migrate --force

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service php reload ) 9>/tmp/fpmlock

npm install
npm run build



