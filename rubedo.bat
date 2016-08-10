@echo off
IF NOT EXIST "composer.phar" (
    php -r "eval('?>' . file_get_contents('https://getcomposer.org/installer'));"
) ELSE (
    php composer.phar self-update
)

IF EXIST "composer.lock" (
    set COMPOSER=composer.json
    php composer.phar update
) ELSE (
    set COMPOSER=composer.json
    php composer.phar install
)