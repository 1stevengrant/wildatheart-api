#@IgnoreInspection BashAddShebang
# Laravel Defaults
#
# Default settings for Laravel scripts
#

# -- GLOBAL settings --

# The maximum age of db backups in days; backups older than this will be automatically removed
GLOBAL_DB_BACKUPS_MAX_AGE=90

# The database driver for this Laravel install ('mysql' or 'pgsql')
GLOBAL_DB_DRIVER="mysql"

# -- LOCAL settings --

# Local FastCGI Cache path; leave it empty ("") if you're not using FastCGI Cache; paths should always have a trailing /
# The `clear_caches.sh` script will delete everything in this directory when it is executed (say, on deploy)
LOCAL_FASTCGI_CACHE_DIR=""

# Local Redis database ID; leave it empty ("") if you're not using Redis. The `clear_caches.sh` script will purge
# this Redis database when it is executed (say, on deploy)
LOCAL_REDIS_DB_ID=""

# Local database constants; default port for mysql is 3306, default port for postgres is 5432
LOCAL_DB_NAME="REPLACE_ME"
LOCAL_DB_PASSWORD="REPLACE_ME"
LOCAL_DB_USER="REPLACE_ME"
LOCAL_DB_HOST="localhost"
LOCAL_DB_PORT="3306"
LOCAL_DB_SCHEMA="public"

# If you are using mysql 5.6.10 or later and you have `login-path` setup as per:
# https://opensourcedbms.com/dbms/passwordless-authentication-using-mysql_config_editor-with-mysql-5-6/
# you can use it instead of the above LOCAL_DB_* constants; otherwise leave this blank
LOCAL_DB_LOGIN_PATH=""

# The `mysql` and `mysqldump` commands to run locally
LOCAL_MYSQL_CMD="mysql"
LOCAL_MYSQLDUMP_CMD="mysqldump"

# Local backups path; paths should always have a trailing /
LOCAL_BACKUPS_PATH="/tmp/"

# -- REMOTE settings --

# Remote ssh credentials, user@domain.com and Remote SSH Port
REMOTE_SSH_LOGIN="REPLACE_ME"
REMOTE_SSH_PORT="22"

# Remote path constants; paths should always have a trailing /
REMOTE_ROOT_PATH="REPLACE_ME"

# Should we connect to the remote database server via ssh?
REMOTE_DB_USING_SSH="yes"

# Remote database constants; default port for mysql is 3306, default port for postgres is 5432
REMOTE_DB_NAME="REPLACE_ME"
REMOTE_DB_PASSWORD="REPLACE_ME"
REMOTE_DB_USER="REPLACE_ME"
REMOTE_DB_HOST="localhost"
REMOTE_DB_PORT="3306"
REMOTE_DB_SCHEMA="public"

# If you are using mysql 5.6.10 or later and you have `login-path` setup as per:
# https://opensourcedbms.com/dbms/passwordless-authentication-using-mysql_config_editor-with-mysql-5-6/
# you can use it instead of the above REMOTE_DB_* constants; otherwise leave this blank
REMOTE_DB_LOGIN_PATH=""

# The `mysql` and `mysqldump` commands to run remotely
REMOTE_MYSQL_CMD="mysql"
REMOTE_MYSQLDUMP_CMD="mysqldump"

# Remote backups path; paths should always have a trailing /
REMOTE_BACKUPS_PATH="/tmp/"
