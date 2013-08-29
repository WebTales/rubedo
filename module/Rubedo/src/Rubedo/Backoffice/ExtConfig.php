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
namespace Rubedo\Backoffice;

class ExtConfig
{

    /**
     * ExtJs configuration
     *
     * @var array
     */
    protected static $config = array();

    /**
     *
     * @return the $config
     */
    public static function getConfig()
    {
        return ExtConfig::$config;
    }

    /**
     *
     * @param multitype: $config            
     */
    public static function setConfig($config)
    {
        ExtConfig::$config = $config;
    }
}
