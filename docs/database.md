# Database

To get a working database, there's an Artisan command that will grab a copy of the production database and import it into your local database.

First you must copy the `.env.example` file to `.env` and fill in the following variables.

```
REMOTE_USE_SSH=true
REMOTE_SSH_USERNAME=forge
REMOTE_DATABASE_HOST=<server_ip>
REMOTE_DATABASE_USERNAME=forge
REMOTE_DATABASE_NAME=<database_name>
```

Then you can run the following command:

```bash
php artisan db:production-sync
```
