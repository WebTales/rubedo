#!/bin/bash
php composer.phar self-update
php composer.phar update -o;
COMPOSER=composer.front.json php composer.phar update;
vendor/bin/phing set-rights;
