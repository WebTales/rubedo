#!/bin/bash
php composer.phar install -o;
COMPOSER=composer.front.json php composer.phar install;
vendor/bin/phing set-rights;
