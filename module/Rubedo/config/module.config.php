<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
defined('APPLICATION_PATH') || define('APPLICATION_PATH', '../../');

$serviceArray = array(
    'MongoDataAccess' => array(
        'class' => 'Rubedo\\Mongo\\DataAccess'
    ),
    'MongoWorkflowDataAccess' => array(
        'class' => 'Rubedo\\Mongo\\WorkflowDataAccess'
    ),
    'MongoFileAccess' => array(
        'class' => 'Rubedo\\Mongo\\FileAccess'
    ),
    'ElasticDataSearch' => array(
        'class' => 'Rubedo\\Elastic\\DataSearch'
    ),
    'ElasticDataIndex' => array(
        'class' => 'Rubedo\\Elastic\\DataIndex'
    ),
    'CurrentUser' => array(
        'class' => 'Rubedo\\User\\CurrentUser'
    ),
    'Session' => array(
        'class' => 'Rubedo\\User\\Session'
    ),
    'Authentication' => array(
        'class' => 'Rubedo\\User\\Authentication'
    ),
    'CurrentTime' => array(
        'class' => 'Rubedo\\Time\\CurrentTime'
    ),
    'Date' => array(
        'class' => 'Rubedo\\Time\\Date'
    ),
    'Url' => array(
        'class' => 'Rubedo\\Router\\Url'
    ),
    'FrontOfficeTemplates' => array(
        'class' => 'Rubedo\\Templates\\FrontOfficeTemplates'
    ),
    'Acl' => array(
        'class' => 'Rubedo\\Security\\Acl'
    ),
    'Hash' => array(
        'class' => 'Rubedo\\Security\\Hash'
    ),
    'HtmlCleaner' => array(
        'class' => 'Rubedo\\Security\\HtmlPurifier'
    ),
    'PageContent' => array(
        'class' => 'Rubedo\\Content\\Page'
    ),
    'Users' => array(
        'class' => 'Rubedo\\Collection\\Users'
    ),
    'UrlCache' => array(
        'class' => 'Rubedo\\Collection\\UrlCache'
    ),
    'Masks' => array(
        'class' => 'Rubedo\\Collection\\Masks'
    ),
    'ReusableElements' => array(
        'class' => 'Rubedo\\Collection\\ReusableElements'
    ),
    'Contents' => array(
        'class' => 'Rubedo\\Collection\\Contents'
    ),
    'ContentTypes' => array(
        'class' => 'Rubedo\\Collection\\ContentTypes'
    ),
    'Delegations' => array(
        'class' => 'Rubedo\\Collection\\Delegations'
    ),
    'Forms' => array(
        'class' => 'Rubedo\\Collection\\Forms'
    ),
    'FormsResponses' => array(
        'class' => 'Rubedo\\Collection\\FormsResponses'
    ),
    'FieldTypes' => array(
        'class' => 'Rubedo\\Collection\\FieldTypes'
    ),
    'Groups' => array(
        'class' => 'Rubedo\\Collection\\Groups'
    ),
    'Icons' => array(
        'class' => 'Rubedo\\Collection\\Icons'
    ),
    'PersonalPrefs' => array(
        'class' => 'Rubedo\\Collection\\PersonalPrefs'
    ),
    'Sites' => array(
        'class' => 'Rubedo\\Collection\\Sites'
    ),
    'Taxonomy' => array(
        'class' => 'Rubedo\\Collection\\Taxonomy'
    ),
    'TaxonomyTerms' => array(
        'class' => 'Rubedo\\Collection\\TaxonomyTerms'
    ),
    'Themes' => array(
        'class' => 'Rubedo\\Collection\\Themes'
    ),
    'Wallpapers' => array(
        'class' => 'Rubedo\\Collection\\Wallpapers'
    ),
    'NestedContents' => array(
        'class' => 'Rubedo\\Collection\\NestedContents'
    ),
    'Pages' => array(
        'class' => 'Rubedo\\Collection\\Pages'
    ),
    'Versioning' => array(
        'class' => 'Rubedo\\Collection\\Versioning'
    ),
    'Images' => array(
        'class' => 'Rubedo\\Collection\\Images'
    ),
    'Files' => array(
        'class' => 'Rubedo\\Collection\\Files'
    ),
    'Cache' => array(
        'class' => 'Rubedo\\Collection\\Cache'
    ),
    'Queries' => array(
        'class' => 'Rubedo\\Collection\\Queries'
    ),
    'Dam' => array(
        'class' => 'Rubedo\\Collection\\Dam'
    ),
    'DamTypes' => array(
        'class' => 'Rubedo\\Collection\\DamTypes'
    ),
    'Workspaces' => array(
        'class' => 'Rubedo\\Collection\\Workspaces'
    ),
    'Mailer' => array(
        'class' => 'Rubedo\\Mail\\Mailer'
    )
);

$serviceMapArray = array();

foreach ($serviceArray as $key => $value) {
    $serviceMapArray[$key] = $value['class'];
    $serviceSharedMapArray[$key] = false;
}
return array(
    'datastream' => array(
        'mongo' => array(
            'server' => 'localhost',
            'port' => '27017',
            'db' => 'rubedo-webinar',
            'login' => '',
            'password' => ''
        )
    ),
    'service_manager' => array(
        'invokables' => $serviceMapArray,
        'shared' => $serviceSharedMapArray
    ),
    'services' => array(
        'logLevel' => '3',
        'enableCache' => '0'
    ),
);
