Here you can find Rubedo Extensions.

A Rubedo Extension is structured as a ZF2 module.

Add extension by setting them inside /composer.extension.json using composer syntax with "vendor-dir": "extensions".
See composer.extension.json.dist as example.

to run extensions install or update :
For windows :
set COMPOSER=composer.extension.json 
php composer.phar (install | update)

For unix
COMPOSER=composer.extension.json php composer.phar (install | update)
