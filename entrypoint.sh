#!/bin/bash
if [ ! -d /var/www/html/rubedo ]; then
    mkdir -p /var/www/html/rubedo
fi
if [ "${VERSION}" != "**None**" ] && [ "${GITHUB_APIKEY}" != "**None**" ]; then
    git clone -b "$VERSION" https://github.com/WebTales/rubedo.git /var/www/html/rubedo
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/var/www/html/rubedo
    cd /var/www/html/rubedo/
    php composer.phar config -g github-oauth.github.com "$GITHUB_APIKEY"
    ./rubedo.sh
fi
exec "$@"