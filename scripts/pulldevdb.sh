#!/bin/bash

source './.env'

file=dbdumps/dev-$(date +"%Y%m%d%H%M").sql
mysqldump \
  --host ${DEV_DB_HOST} \
  --port ${DEV_DB_PORT} \
  -u ${DEV_DB_USER} \
  --set-gtid-purged=OFF \
  --password="${DEV_DB_PASSWORD}" \
  ${DEV_DB_DATABASE} > ${file}

if [ "${?}" -eq 0 ]; then
    echo "restoring local"
  mysql \
    --host ${DB_HOST} \
    -u${DB_USERNAME} \
    -p${DB_PASSWORD} \
    ${DB_DATABASE} < ${file}
else
  echo "Error backing up mysql"
  exit 255
fi
