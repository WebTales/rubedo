IF NOT EXIST "composer.phar" (
    SET bits_job=tigbits%RANDOM%
    bitsadmin.exe /CREATE /DOWNLOAD %bits_job%
    bitsadmin.exe /ADDFILE %bits_job% "http://getcomposer.org/installer" "installer.php"
    bitsadmin.exe /RESUME %bits_job%
    php installer.php
    DEL installer.php
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