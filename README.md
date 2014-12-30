Rubedo
======

An open source PHP CMS, based on Zend Framework &amp; NoSQL MongoDB and Elasticsearch.
### http://www.rubedo-project.org/
    DATA DRIVEN CONTENT AND COMMERCE

Rubedo is a unique big data platform that go beyond traditional content management and commerce
* Multisite management / Website factory
* Behavioral targeting
* Full text and faceted search functionalities
* Responsive Web design
* Multilingual
* Integrated solution


Open Source License
------------------------------------------------------------------------------------------
Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
http://www.gnu.org/licenses/gpl.html
Copyright (c) 2014, WebTales (http://www.webtales.fr/). All rights reserved.
licensing@webtales.fr

Documentation and Help
------------------------------------------------------------------------------------------
* Rubedo Documentation Center http://docs.rubedo-project.org/en/homepage
* Translation are available on https://crowdin.com/project/rubedo
* Forum http://forum.rubedo-project.org/
* Hosting http://www.rubedocloud.com/en/ 
* Newsletter http://newsletter-rubedo.webtales.fr/en/home



Installation
------------------------------------------------------------------------------------------
### PreRequisites
* A full PHP 5.3.6+ stack (i.e. http://www.zend.com/products/server/)
* MongoDB (http://www.mongodb.org) 2.4.x (issues reported on 2.6, will be fixed in the next version)
* PHP MongoDB Driver >= 1.3.0
* intl PHP extension (http://www.php.net/manual/intro.intl.php) which you should use anyway
* ElasticSearch (http://www.elasticsearch.org), latest version compatible with Elastica PHP client, 1.2.1 at this moment (https://github.com/ruflin/Elastica/)
* Mapper Attachments Type for ElasticSearch (https://github.com/elasticsearch/elasticsearch-mapper-attachments) 
* ICU Analysis plugin for ElasticSearch (https://github.com/elasticsearch/elasticsearch-analysis-icu)

### Already packaged Rubedo
* Prebuilt releases of Rubedo are available on releases page (https://github.com/WebTales/rubedo/releases)
* Install preRequisites (Apache,PHP,DB,Search Engine)
* Define a simple vHost with the *public* directory as documentRoot
* Add an AllowOverride All on this documentRoot
* Access the documentRoot URL automatically run the config wizard

### From Source Install Steps
* Download Source from gitHub (https://github.com/WebTales/rubedo/tags)
* Extract them on your server
* Define a simple vHost with the *public* directory as documentRoot
* Add an AllowOverride All on this documentRoot
* If on Unix server : Inside project root, run `./rubedo.sh`
* If on Windows server : Inside project root, run `rubedo`
* Access the documentRoot URL automatically run the config wizard

### For Developpers
* You'll need Git!
* Clone form gitHub to your server `git clone git://github.com/WebTales/rubedo.git`
* Inside project root, choose the branch you want to use (current or next) : `git checkout next`
* Do as in normal install process



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

