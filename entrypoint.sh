#!/bin/bash
if [ ! -d /var/sources/3.2.x ]; then
    mkdir -p /var/sources/3.2.x
fi
if [ "${VERSION}" != "**None**" ] && [ "${GITHUB_APIKEY}" != "**None**" ]; then
    git clone -b "$VERSION" https://github.com/WebTales/rubedo.git /var/sources/3.2.x
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/var/sources/3.2.x
    cd /var/sources/3.2.x
    php composer.phar config -g github-oauth.github.com "$GITHUB_APIKEY"
    ./rubedo.sh
fi
exec "$@"