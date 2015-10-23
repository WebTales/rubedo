#!/bin/bash
if [ ! -d /var/sources ]; then
    mkdir -p /var/sources
fi
if [ "${VERSION}" != "**None**" ] && [ "${GITHUB_APIKEY}" != "**None**" ]; then
    rm -rf /var/sources
    git clone -b "$VERSION" https://github.com/WebTales/rubedo.git /var/sources
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/var/sources
    cd /var/sources
    php composer.phar config -g github-oauth.github.com "$GITHUB_APIKEY"
    ./rubedo.sh
    del_folder=($(find public/ -type d -name doc*; find public/ -type d -name test*;find vendor/ -type d -name doc*; find vendor/ -type d -name test*))
    echo "Find and delete docs and tests folders :"
    for i in "${del_folder[@]}"
    do
        echo "    - Deleting $i"
        rm -rf $i
    done
    echo "Folder cleaned !"
fi
exec "$@"