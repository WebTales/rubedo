Rubedo
======

An open source PHP CMS, based on Zend Framework &amp; MongoDB

Copyright (c) 2012, WebTales (http://www.webtales.fr/).
All rights reserved.
licensing@webtales.fr

Open Source License
------------------------------------------------------------------------------------------
Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 

http://www.gnu.org/licenses/gpl.html


Installation
------------------------------------------------------------------------------------------
### PreRequisites
* A full PHP 5.3+ stack (i.e. http://www.zend.com/products/server/)
* Composer (http://getcomposer.org)
* MongoDB (http://www.mongodb.org)
* ElasticSearch (http://www.elasticsearch.org)

### Install Steps
* Download Source form gitHub (https://github.com/WebTales/rubedo/tags)
* Extract them on your server
* Definie a vHost with the *public* as documentRoot
* Add an AllowOverride All on this documentRoot
* Inside the documentRoot, run `composer install -o`
* Run `../vendor/bin/phing set-rights` inside the *install* dir
* Run `composer install` inside the *public* dir
* Access the */install* URL and run the config wizard
