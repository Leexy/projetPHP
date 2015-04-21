#!/usr/bin/env bash

set -e
set -u

HOST=battleship@cguille.net
WORKDIR=$(mktemp -d)

git clone git@github.com:Leexy/projetPHP.git "${WORKDIR}"

ssh "${HOST}" mkdir -p releases
RELEASE_DIR=$(ssh "${HOST}" mktemp -d -p releases "$(date +%F_%H-%M-%S)_XXXXXXXXXX")

scp -r "${WORKDIR}"/* "battleship@cguille.net:${RELEASE_DIR}"
ssh "${HOST}" << SCRIPT
cd "${RELEASE_DIR}" &&
php composer.phar install &&
cd
chown -R battleship:www-data "${RELEASE_DIR}" &&
chmod g+x "${RELEASE_DIR}" &&
chmod g+r -R "${RELEASE_DIR}" &&
ln -nfs "${RELEASE_DIR}" www
find releases -maxdepth 1 -mtime +7 -exec rm -rf {} +
SCRIPT

rm -rf "${WORKDIR}"
