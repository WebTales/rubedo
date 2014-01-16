#!/bin/bash

#Try if CMDS exist
CMDS="php curl"
for i in $CMDS
do
	command -v $i >/dev/null && continue || { echo "$i command not found."; exit 1; }
done

# Get last composer
if [ -f composer.phar ]
    then
        php composer.phar self-update
    else
        curl -sS https://getcomposer.org/installer | php
fi

# Install or update with composer
if [ -f composer.lock ]
    then
        php composer.phar update -o;
        COMPOSER=composer.front.json php composer.phar update;
    else
        php composer.phar install -o;
        COMPOSER=composer.front.json php composer.phar install;
fi

# Install or update extensions
if [ -f composer.extensions.json ]
    then
        if [ -f composer.extensions.lock ]
            then
                COMPOSER=composer.extension.json php composer.phar install -o;
            else
                COMPOSER=composer.extension.json php composer.phar update -o;
        fi
fi

# Set Rights
vendor/bin/phing set-rights;