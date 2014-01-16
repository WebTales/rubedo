Here you can find Rubedo Extensions.

A Rubedo Extension is structured as a ZF2 module.

Add extension by setting them inside /composer.extensions.json using composer syntax with "vendor-dir": "extensions".
See composer.extensions.json.dist as example.

to run extensions install or update :
For windows :
set COMPOSER=composer.extensions.json
php composer.phar (install | update)

For unix
COMPOSER=composer.extensions.json php composer.phar (install | update)
