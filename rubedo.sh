#!/bin/bash
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
cd $DIR

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

# Get latest composer
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
        php composer.phar $DEVMODE update;
    else
        php composer.phar $DEVMODE install;
fi