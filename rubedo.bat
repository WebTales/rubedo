IF NOT EXIST "composer.phar" (
    php -r "eval('?>' . file_get_contents('https://getcomposer.org/installer'));"
) ELSE (
    php composer.phar self-update
)

IF EXIST "composer.lock" (
    set COMPOSER=composer.json
    php composer.phar update -o
    set COMPOSER=composer.front.json
    php composer.phar update
) ELSE (
    set COMPOSER=composer.json
    php composer.phar install -o
    set COMPOSER=composer.front.json
    php composer.phar install
)

IF EXIST "composer.extensions.json" (
    IF EXIST "composer.extensions.lock" (
        set COMPOSER=composer.extensions.json
        php composer.phar update -o
    ) ELSE (
        set COMPOSER=composer.extensions.json
        php composer.phar install -o
    )
)

vendor\bin\phing set-rights
pause