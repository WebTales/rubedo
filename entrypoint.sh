#!/bin/bash
if [ ! -d /var/sources/${VERSION} ]; then
    mkdir -p /var/sources/${VERSION}
fi
if [ "${VERSION}" != "**None**" ] && [ "${GITHUB_APIKEY}" != "**None**" ]; then
    git clone -b "$VERSION" https://github.com/WebTales/rubedo.git /var/sources/${VERSION}
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/var/sources/${VERSION}
    cd /var/sources/${VERSION}
    php composer.phar config -g github-oauth.github.com "$GITHUB_APIKEY"
    ./rubedo.sh
fi
exec "$@"