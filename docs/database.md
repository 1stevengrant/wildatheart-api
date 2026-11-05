# Database

To get a working database, there's an Artisan command that will grab a copy of the production database and import it into your local database.

The command uses [mydumper](https://github.com/mydumper/mydumper) and its loader, myloader, which dump and load tables in parallel. Install both with Homebrew:

```bash
brew install mydumper
```

Then copy the `.env.example` file to `.env` and fill in the production database credentials. The Laravel Cloud CLI (`composer global require laravel/cloud-cli`) prints them (the `--show-sensitive` flag is needed, otherwise the password comes back masked):

```bash
cloud database-cluster:list --json --show-sensitive -n
```

```
REMOTE_DATABASE_HOST=<cluster public hostname>
REMOTE_DATABASE_USERNAME=<username>
REMOTE_DATABASE_PASSWORD=<password>
REMOTE_DATABASE_NAME=production
REMOTE_DATABASE_EXCLUDE_DATA=activity_log,failed_jobs,issues
REMOTE_DATABASE_SSL_MODE=VERIFY_IDENTITY
REMOTE_DATABASE_SSL_CA=/etc/ssl/cert.pem
```

The cluster's public endpoint has to be enabled in Laravel Cloud for this to connect.

Then you can run the following command:

```bash
php artisan db:production-sync
```

It replaces the tables in your local database with production's. Tables listed in `REMOTE_DATABASE_EXCLUDE_DATA` keep their structure but come across empty.

## Staging content

Staging keeps a copy of production's CMS content. `php artisan staging:sync-content` runs hourly on the staging environment and copies the tables listed in `contentTables` in `config/dbsync.php` from the `production` schema, which sits on the same database cluster.

The list is an allowlist, so users, attendees, events, addresses and form submissions stay out of staging. Check a table for personal data before adding it.

A table is only copied when its row count or latest `updated_at` differs from production's. Any edits made to these tables on staging are replaced at the next copy, and `--force` copies every table regardless.

## Why mydumper

A single `mysqldump` of every table through Laravel Cloud's database proxy fails partway through with `Error 2027: Malformed packet`. Dumping one table at a time over a fresh connection works, and mydumper does that in parallel threads.

mydumper is also built against MariaDB's client library, which still supports the `mysql_native_password` login that Laravel Cloud database users require. MySQL 9 clients removed it, so `mysqldump` from a MySQL 9 install cannot log in.

## Options

| Variable | Default | Purpose |
|---|---|---|
| `REMOTE_DATABASE_THREADS` | `8` | Parallel threads for mydumper and myloader |
| `REMOTE_DATABASE_IGNORE_TABLES` | | Tables to leave out entirely, comma separated |
| `REMOTE_IMPORT_FILE` | `true` | Set to `false` to keep the dump and skip the import |
| `REMOTE_DEFAULT_DIRECTORY` | `dbsync` | Dump directory, relative paths go in the system temp directory |
| `MYDUMPER_PATH` / `MYLOADER_PATH` | `mydumper` / `myloader` | Binary locations when they are not on your `PATH` |

Pass `--directory=/some/path` to write the dump somewhere specific.
