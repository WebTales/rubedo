<?php
return array(
    'phpSettings' => array(
        'display_startup_errors' => '1',
        'display_errors' => '1'
    ),
    'bootstrap' => array(
        'path' => APPLICATION_PATH.'/Bootstrap.php',
        'class' => 'Bootstrap'
    ),
    'appnamespace' => 'Application',
    'resources' => array(
        'frontController' => array(
            'plugins' => array(
                'main' => 'Application_Plugin_Main'
            ),
            'moduledirectory' => APPLICATION_PATH.'/modules',
            'defaultmodule' => 'default',
            'actionHelperPaths' => array(
                'Controller_Helper' => APPLICATION_PATH.'/modules/default/controllers/helpers'
            ),
            'params' => array(
                'displayExceptions' => '1'
            )
        ),
        'modules' => array(
            0 => ''
        ),
        'layout' => array(
            'layoutPath' => APPLICATION_PATH.'/modules/default/views/layouts/'
        ),
        'session' => array(
            'use_only_cookies' => '1',
            'name' => 'rubedo'
        )
    ),
    'autoloaderNamespaces' => array(
        'rubedo' => 'Rubedo',
        'elastica' => 'Elastica',
        'twig' => 'Twig',
        'phactory' => 'Phactory'
    ),
    'services' => array(
        'logLevel' => '3',
        'enableCache' => '0',
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
        'Blocks' => array(
            'class' => 'Rubedo\\Collection\\Blocks'
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
        'TinyUrl' => array(
            'class' => 'Rubedo\\Collection\\TinyUrl'
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
        ),
        'Notification' => array(
            'class' => 'Rubedo\\Mail\\Notification'
        ),
        'MailingList' => array(
            'class' => 'Rubedo\\Collection\\MailingList'
        ),
        'Localisation' => array(
            'class' => 'Rubedo\\Collection\\Localisation'
        ),
        'RubedoVersion' => array(
            'class' => 'Rubedo\\Collection\\RubedoVersion'
        ),
        'Translate' => array(
            'class' => 'Rubedo\\Internationalization\\Translate'
        )
    ),
    'backoffice' => array(
        'extjs' => array(
            'debug' => '0',
            'network' => 'local',
            'version' => '4.1.1'
        )
    ),
    'authentication' => array(
        'authLifetime' => '3600'
    ),
    'localisationfiles' => array(
        0 => 'public/components/webtales/rubedo-localization/languagekey/Exceptions/generalExceptions.json',
        1 => 'public/components/webtales/rubedo-localization/languagekey/Generic/genericTranslations.json',
        2 => 'public/components/webtales/rubedo-localization/languagekey/BackOffice/FrontEndEdition/frontEndLabels.json'
    )
);
