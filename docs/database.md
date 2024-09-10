# Database

To get a working database, there's an Artisan command that will grab a copy of the production database and import it into your local database.

First you must copy `scripts/example.env.sh` to `scripts/.env.sh` and fill in the necessary environment variables that have `REPLACE_ME` values.

The `REMOTE_SSH_LOGIN` should be something like `forge@127.0.0.1` where 127.0.0.1 is the public IP address of the server.

Then you can run the following command:

```bash
php artisan app:pull-prod-database
```
