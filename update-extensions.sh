#!/bin/bash
COMPOSER=composer.extension.json php composer.phar update -o;
vendor/bin/phing set-rights
