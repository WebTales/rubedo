<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces;

/**
 * Static class which contains the interface/serviceName association and the concerns class list
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class config
{

    /**
     * Class property which contains a hash table service name => interface name
     * 
     * @var array array service name => interface name
     */
    protected static $_interfaceArray = array();

    /**
     * Class property which contains the defautl value for $_interfaceArray
     * 
     * @var array array service name => interface name
     */
    protected static $_defaultInterfaceArray = array(
        'MongoDataAccess' => 'Rubedo\\Interfaces\\Mongo\\IDataAccess',
        'MongoWorkflowDataAccess' => 'Rubedo\\Interfaces\\Mongo\\IWorkflowDataAccess',
        'MongoFileAccess' => 'Rubedo\\Interfaces\\Mongo\\IFileAccess',
        'ElasticDataSearch' => 'Rubedo\\Interfaces\\Elastic\\IDataSearch',
        'ElasticDataIndex' => 'Rubedo\\Interfaces\\Elastic\\IDataIndex',
        'Acl' => 'Rubedo\\Interfaces\\Security\\IAcl',
        'Hash' => 'Rubedo\\Interfaces\\Security\\IHash',
        'HtmlCleaner' => 'Rubedo\\Interfaces\\Security\\IHtmlCleaner',
        'CurrentUser' => 'Rubedo\\Interfaces\\User\\ICurrentUser',
        'Session' => 'Rubedo\\Interfaces\\User\\ISession',
        'Authentication' => 'Rubedo\\Interfaces\\User\\IAuthentication',
        'CurrentTime' => 'Rubedo\\Interfaces\\Time\\ICurrentTime',
        'Date' => 'Rubedo\\Interfaces\\Time\\IDate',
        'Url' => 'Rubedo\\Interfaces\\Router\\IUrl',
        'PageContent' => 'Rubedo\\Interfaces\\Content\\IPage',
        'FrontOfficeTemplates' => 'Rubedo\\Interfaces\\Templates\\IFrontOfficeTemplates',
        'Users' => 'Rubedo\\Interfaces\\Collection\\IUsers',
        'UrlCache' => 'Rubedo\\Interfaces\\Collection\\IUrlCache',
        'Masks' => 'Rubedo\\Interfaces\\Collection\\IMasks',
        'ReusableElements' => 'Rubedo\\Interfaces\\Collection\\IReusableElements',
        'Blocks' => 'Rubedo\\Interfaces\\Collection\\IBlocks',
        'Contents' => 'Rubedo\\Interfaces\\Collection\\IContents',
        'ContentTypes' => 'Rubedo\\Interfaces\\Collection\\IContentTypes',
        'Delegations' => 'Rubedo\\Interfaces\\Collection\\IDelegations',
        'Forms' => 'Rubedo\\Interfaces\\Collection\\IForms',
        'FormsResponses' => 'Rubedo\\Interfaces\\Collection\\IFormsResponses',
        'FieldTypes' => 'Rubedo\\Interfaces\\Collection\\IFieldTypes',
        'Groups' => 'Rubedo\\Interfaces\\Collection\\IGroups',
        'Icons' => 'Rubedo\\Interfaces\\Collection\\IIcons',
        'PersonalPrefs' => 'Rubedo\\Interfaces\\Collection\\IPersonalPrefs',
        'Sites' => 'Rubedo\\Interfaces\\Collection\\ISites',
        'Taxonomy' => 'Rubedo\\Interfaces\\Collection\\ITaxonomy',
        'TaxonomyTerms' => 'Rubedo\\Interfaces\\Collection\\ITaxonomyTerms',
        'Themes' => 'Rubedo\\Interfaces\\Collection\\IThemes',
        'TinyUrl' => 'Rubedo\\Interfaces\\Collection\\ITinyUrl',
        'Wallpapers' => 'Rubedo\\Interfaces\\Collection\\IWallpapers',
        'NestedContents' => 'Rubedo\\Interfaces\\Collection\\INestedContents',
        'Pages' => 'Rubedo\\Interfaces\\Collection\\IPages',
        'Versioning' => 'Rubedo\\Interfaces\\Collection\\IVersioning',
        'Images' => 'Rubedo\\Interfaces\\Collection\\IImages',
        'Files' => 'Rubedo\\Interfaces\\Collection\\IFiles',
        'Cache' => 'Rubedo\\Interfaces\\Collection\\ICache',
        'Queries' => 'Rubedo\\Interfaces\\Collection\\IQueries',
        'Dam' => 'Rubedo\\Interfaces\\Collection\\IDam',
        'DamTypes' => 'Rubedo\\Interfaces\\Collection\\IDamTypes',
        'Workspaces' => 'Rubedo\\Interfaces\\Collection\\IWorkspaces',
        'Mailer' => 'Rubedo\\Interfaces\\Mail\\IMailer',
        'Notification' => 'Rubedo\\Interfaces\\Mail\\INotification',
        'MailingList' => 'Rubedo\\Interfaces\\Collection\\IMailingList',
        'Localisation' => 'Rubedo\\Interfaces\\Collection\\ILocalisation',
        'RubedoVersion' => 'Rubedo\\Interfaces\\Collection\\IRubedoVersion',
        'Translate' => 'Rubedo\\Interfaces\\Internationalization\\ITranslate'
    );

    /**
     * Return all collection services
     * 
     * @return multitype:unknown
     */
    public static function getCollectionServices ()
    {
        $collectionServicesArray = array();
        foreach (self::$_interfaceArray as $service => $interface) {
            if (in_array('Rubedo\\Interfaces\\Collection\\IAbstractCollection', class_implements($interface))) {
                $collectionServicesArray[] = $service;
            }
        }
        return $collectionServicesArray;
    }

    /**
     * Public static method to add new service to the application
     *
     * @param string $serviceName
     *            Name of the service in service manager and application.ini
     * @param string $interfaceName
     *            contract of the service
     */
    final public static function addInterface ($serviceName, $interfaceName)
    {
        static::$_interfaceArray[$serviceName] = $interfaceName;
    }

    /**
     * Public static method which return the interface the given service should implement
     * 
     * @param string $serviceName
     *            Name of the service in service manager and application.ini
     * @return string contract of the service
     */
    final public static function getInterface ($serviceName)
    {
        if (isset(static::$_interfaceArray[$serviceName])) {
            return static::$_interfaceArray[$serviceName];
        } else {
            return false;
        }
    }

    /**
     * Class property which contains concerns list
     * 
     * @var array list of concerns
     */
    protected static $_concernArray = array();

    /**
     * Public static method to clear interface list
     */
    final public static function clearInterfaces ()
    {
        static::$_interfaceArray = array();
    }

    /**
     * Public static method to init interface list
     */
    public static function initInterfaces ()
    {
        static::$_interfaceArray = static::$_defaultInterfaceArray;
    }

    /**
     * Public static method to clear concerns during service method call
     */
    final public static function clearConcerns ($serviceName = null)
    {
        if ($serviceName) {
            static::$_concernArray[$serviceName] = array();
        } else {
            static::$_concernArray = array();
        }
    }

    /**
     * Public static method to add new concern during service method call
     *
     * @param string $concernName
     *            Class name of the concern
     */
    final public static function addConcern ($serviceName, $concernName)
    {
        static::$_concernArray[$serviceName][] = $concernName;
    }

    /**
     * Public static method which return concerns array
     * 
     * @return array list of concerns
     */
    final public static function getConcerns ($serviceName = null)
    {
        if ($serviceName) {
            if (isset(static::$_concernArray[$serviceName])) {
                return static::$_concernArray[$serviceName];
            } else {
                return array();
            }
        }
        return static::$_concernArray;
    }
}
