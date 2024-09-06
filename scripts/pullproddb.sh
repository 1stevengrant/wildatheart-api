#!/bin/bash

source './.env'

file=dbdumps/prod-$(date +"%Y%m%d%H%M").sql
mysqldump \
  --host "${PRODUCTION_DB_HOST}" \
  --port "${PRODUCTION_DB_PORT}" \
  -u "${PRODUCTION_DB_USERNAME}" \
  --set-gtid-purged=OFF \
  --password="${PRODUCTION_DB_PASSWORD}" \
  "${PRODUCTION_DB_DATABASE}" > "${file}"

if [ "${?}" -eq 0 ]; then
    echo "restoring local"
  mysql \
    --host "${DB_HOST}" \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    "${DB_DATABASE}" < ${file}
else
  echo "Error backing up mysql"
  exit 255
fi
