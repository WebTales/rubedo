#!/bin/bash
if [ ! -d /var/sources ]; then
    mkdir -p /var/sources
fi
if [ "${VERSION}" != "**None**" ] && [ "${GITHUB_APIKEY}" != "**None**" ]; then
    git clone -b "$VERSION" https://github.com/WebTales/rubedo.git /var/sources
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/var/sources
    cd /var/sources
    php composer.phar config -g github-oauth.github.com "$GITHUB_APIKEY"
    ./rubedo.sh
fi
exec "$@"