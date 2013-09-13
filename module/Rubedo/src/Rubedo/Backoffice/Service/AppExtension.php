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
namespace Rubedo\Backoffice\Service;

use Zend\Json\Json;
use Rubedo\Services\Manager;

/**
 * Service to handle Backoffice Application Extensions
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class AppExtension
{

    protected static $config;

    /**
     *
     * @return the $config
     */
    public function getConfig ()
    {
        return AppExtension::$config;
    }

    /**
     *
     * @param field_type $config            
     */
    public static function setConfig ($config)
    {
        AppExtension::$config = $config;
    }

    public function __construct ()
    {
        if (! isset(self::$config)) {
            self::lazyloadConfig();
        }
    }

    /**
     * Return the real path name of the given application
     *
     * @param string $extensionName            
     * @return string
     */
    public function getBasePath ($extensionName)
    {
        $config = $this->getConfig();
        if (! isset($config[$extensionName])) {
            throw new \Rubedo\Exceptions\Server('Unknown application');
        } else {
            return $config[$extensionName]['basePath'];
        }
    }

    public function getGlobalBlocksJson ()
    {
        $globalArray = array();
        foreach ($this->getConfig() as $appConfig) {
            $blockJsonData = file_get_contents($appConfig['definitionFile']);
            $globalArray[] = Json::decode($blockJsonData, Json::TYPE_ARRAY);
        }
        return $globalArray;
    }

    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyloadConfig ()
    {
        $config = Manager::getService('config');
        self::setConfig($config['appExtension']);
    }
}
