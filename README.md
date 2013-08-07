Rubedo
======

An open source PHP CMS, based on Zend Framework &amp; MongoDB : http://www.rubedo-project.org/

Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
* Phing (http://www.phing.info)
* MongoDB (http://www.mongodb.org) >= 2.2
* PHP MongoDB Driver >= 1.3.0
* ElasticSearch (http://www.elasticsearch.org), latest version compatible with Elastica PHP client, 0.90.2 at this moment (https://github.com/ruflin/Elastica/)
* Mapper Attachments Type for ElasticSearch (https://github.com/elasticsearch/elasticsearch-mapper-attachments) 
* ICU Analysis plugin for ElasticSearch (https://github.com/elasticsearch/elasticsearch-analysis-icu)

### Install Steps
* Download Source from gitHub (https://github.com/WebTales/rubedo/tags)
* Extract them on your server
* Define a simple vHost with the *public* directory as documentRoot
* Add an AllowOverride All on this documentRoot
* Inside project root, run `phing`
* Access the */install* URL and run the config wizard

### For Developpers
* You will need versionControl_git `pear install VersionControl_Git-0.4.4`
* Clone form gitHub to your server `git clone git://github.com/WebTales/rubedo.git`
* Define a simple vHost with the *public* directory as documentRoot
* Add an AllowOverride All on this documentRoot
* Inside project root, run `phing install-dev`
* Access the */install* URL and run the config wizard


Setting Up Your VHOST
------------------------------------------------------------------------------------------
The following is a sample VHOST you might want to consider for your project.

	<VirtualHost *:80>
	   DocumentRoot "path_to_project/rubedo/public"
	   ServerName rubedo.local
	
	   <Directory "path_to_project/rubedo/public">
	       Options -Indexes FollowSymLinks
	       AllowOverride All
	       Order allow,deny
	       Allow from all
	   </Directory>
	
	</VirtualHost>

