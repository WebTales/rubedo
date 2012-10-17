<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
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
     * @var array array service name => interface name
     */
    protected static $_interfaceArray = array(
    'MongoDataAccess' 	=> 	'Rubedo\\Interfaces\\Mongo\\IDataAccess',
    'ElasticDataSearch' => 	'Rubedo\\Interfaces\\Elastic\\IDataSearch', 
	'Acl'				=>	'Rubedo\\Interfaces\\Acl\\IAcl',
	'CurrentUser'		=>	'Rubedo\\Interfaces\\User\\ICurrentUser',
	'CurrentTime'		=>	'Rubedo\\Interfaces\\Time\\ICurrentTime',	
	);

    /**
     * Public static method to add new service to the application
     *
     * @param string $serviceName Name of the service in service manager and application.ini
     * @param string $interfaceName contract of the service
     */
    final public static function addInterface($serviceName, $interfaceName)
    {
        static::$_interfaceArray[$serviceName] = $interfaceName;
    }

    /**
     * Public static method which return the interface the given service should implement
     * @param string $serviceName Name of the service in service manager and application.ini
     * @return string contract of the service
     */
    final public static function getInterface($serviceName)
    {
        if (isset(static::$_interfaceArray[$serviceName])) {
            return static::$_interfaceArray[$serviceName];
        } else {
            return false;
        }

    }

    /**
     * Class property which contains concerns list
     * @var array list of concerns
     */
    protected static $_concernArray = array();

	/**
     * Public static method to clear interface list
     */
    final public static function clearInterfaces()
    {
        static::$_interfaceArray = array();
    }

    /**
     * Public static method to clear concerns during service method call
     */
    final public static function clearConcerns()
    {
        static::$_concernArray = array();
    }

    /**
     * Public static method to add new concern during service method call
     *
     * @param string $concernName Class name of the concern
     */
    final public static function addConcern($concernName)
    {
        static::$_concernArray[] = $concernName;
    }

    /**
     * Public static method which return concerns array
     * @return array list of concerns
     */
    final public static function getConcerns()
    {
        return static::$_concernArray;
    }

}