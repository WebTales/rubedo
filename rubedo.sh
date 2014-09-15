#!/bin/bash

#Try if CMDS exist
command -v php > /dev/null || { echo "php command not found."; exit 1; }
HASCURL=1;
command -v curl > /dev/null || HASCURL=0;

if [ -z "$1" ]
    then
        DEVMODE="--no-dev"
    else
        DEVMODE=$1;
fi

# Get last composer
if [ -f composer.phar ]
    then
        php composer.phar self-update
    else
        if [[ HASCURL == 1 ]]
            then
                curl -sS https://getcomposer.org/installer | php
            else
                php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
        fi
fi

# Install or update with composer
if [ -f composer.lock ]
    then
        php composer.phar $DEVMODE update -o;
        COMPOSER=composer.front.json php composer.phar $DEVMODE update;
    else
        php composer.phar $DEVMODE install -o;
        COMPOSER=composer.front.json php composer.phar $DEVMODE install;
fi

# Install or update extensions
if [ -f composer.extensions.json ]
    then
        if [ -f composer.extensions.lock ]
            then
                COMPOSER=composer.extensions.json php composer.phar $DEVMODE update -o;
            else
                COMPOSER=composer.extensions.json php composer.phar $DEVMODE install -o;
        fi
fi

# Set Rights
vendor/bin/phing set-rights;