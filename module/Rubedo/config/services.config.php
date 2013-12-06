<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

/**
 * Define all services
 */
return array(
    'Acl' => 'Rubedo\\Security\\Acl',
    'AppExtension' => 'Rubedo\\Backoffice\\Service\\AppExtension',
    'ApplicationLogger' => 'Rubedo\\Log\\ApplicationLogger',
    'ApplicationLog' => 'Rubedo\\Collection\\ApplicationLog',
    'Authentication' => 'Rubedo\\User\\Authentication',
    'Blocks' => 'Rubedo\\Collection\\Blocks',
    'Cache' => 'Rubedo\\Collection\\Cache',
    'ContentTypes' => 'Rubedo\\Collection\\ContentTypes',
    'Contents' => 'Rubedo\\Collection\\Contents',
    'CurrentLocalization' => 'Rubedo\\Internationalization\\Current',
    'CurrentTime' => 'Rubedo\\Time\\CurrentTime',
    'CurrentUser' => 'Rubedo\\User\\CurrentUser',
    'CustomThemes' => 'Rubedo\\Collection\\CustomThemes',
    'Dam' => 'Rubedo\\Collection\\Dam',
    'DamTypes' => 'Rubedo\\Collection\\DamTypes',
    'Date' => 'Rubedo\\Time\\Date',
    'Delegations' => 'Rubedo\\Collection\\Delegations',
    'Directories' => 'Rubedo\\Collection\\Directories',
    'ElasticDataIndex' => 'Rubedo\\Elastic\\DataIndex',
    'ElasticDataSearch' => 'Rubedo\\Elastic\\DataSearch',
    'FieldTypes' => 'Rubedo\\Collection\\FieldTypes',
    'Files' => 'Rubedo\\Collection\\Files',
    'FrontOfficeTemplates' => 'Rubedo\\Templates\\FrontOfficeTemplates',
    'Groups' => 'Rubedo\\Collection\\Groups',
    'Hash' => 'Rubedo\\Security\\Hash',
    'HtmlCleaner' => 'Rubedo\\Security\\HtmlPurifier',
    'Icons' => 'Rubedo\\Collection\\Icons',
    'Images' => 'Rubedo\\Collection\\Images',
    'Languages' => 'Rubedo\\Collection\\Languages',
    'Logger' => 'Rubedo\\Log\\Logger',
    'Localisation' => 'Rubedo\\Collection\\Localisation',
    'Mailer' => 'Rubedo\\Mail\\Mailer',
    'MailingList' => 'Rubedo\\Collection\\MailingList',
    'Newsletter' => 'Rubedo\\Mail\\Newsletter',
    'Masks' => 'Rubedo\\Collection\\Masks',
    'MongoDataAccess' => 'Rubedo\\Mongo\\DataAccess',
    'MongoFileAccess' => 'Rubedo\\Mongo\\FileAccess',
    'MongoWorkflowDataAccess' => 'Rubedo\\Mongo\\WorkflowDataAccess',
    'NestedContents' => 'Rubedo\\Collection\\NestedContents',
    'Notification' => 'Rubedo\\Mail\\Notification',
    'PageContent' => 'Rubedo\\Content\\Page',
    'Pages' => 'Rubedo\\Collection\\Pages',
    'PersonalPrefs' => 'Rubedo\\Collection\\PersonalPrefs',
    'Queries' => 'Rubedo\\Collection\\Queries',
    'ReusableElements' => 'Rubedo\\Collection\\ReusableElements',
    'Recaptcha' => 'Rubedo\\Security\\Recaptcha',
    'RawRenderer' => 'Rubedo\\Templates\\Raw\\RawRenderer',
    'RubedoVersion' => 'Rubedo\\Collection\\RubedoVersion',
    'SearchLogger' => 'Rubedo\\Log\\SearchLogger',
    'SecurityLogger' => 'Rubedo\\Log\\SecurityLogger',
    'Session' => 'Rubedo\\User\\Session',
    'SessionData' => 'Rubedo\\Collection\\SessionData',
    'Sites' => 'Rubedo\\Collection\\Sites',
    'Taxonomy' => 'Rubedo\\Collection\\Taxonomy',
    'TaxonomyTerms' => 'Rubedo\\Collection\\TaxonomyTerms',
    'Themes' => 'Rubedo\\Collection\\Themes',
    'TinyUrl' => 'Rubedo\\Collection\\TinyUrl',
    'Translate' => 'Rubedo\\Internationalization\\Translate',
    'TwigRenderer' => 'Rubedo\\Templates\\Twig\\TwigRenderer',
    'Url' => 'Rubedo\\Router\\Url',
    'UrlCache' => 'Rubedo\\Collection\\UrlCache',
    'Users' => 'Rubedo\\Collection\\Users',
    'Versioning' => 'Rubedo\\Collection\\Versioning',
    'Wallpapers' => 'Rubedo\\Collection\\Wallpapers',
    'Workspaces' => 'Rubedo\\Collection\\Workspaces',
    'UserTypes' => 'Rubedo\\Collection\\UserTypes',
    'Emails' => 'Rubedo\\Collection\\Emails',
    'EmailTemplates' => 'Rubedo\\Collection\\EmailTemplates'
);