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
namespace Rubedo\Security;

/**
 * Service to handle allowed and disallowed HTML contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class HtmlPurifier extends HtmlCleaner
{

    protected static $_purifier;

    /**
     * Clean a raw content to become a valid HTML content without threats
     *
     * @param string $html            
     * @return string
     */
    public function clean ($html)
    {
        if (empty($html)) {
            return $html;
        }
        
        if (! class_exists('\HTMLPurifier_Config')) {
            return parent::clean($html);
        }
        if (! isset(self::$_purifier)) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('Cache.SerializerPath', APPLICATION_PATH . "/../cache/htmlpurifier");
            $config->set('Attr.AllowedFrameTargets', array(
                "_blank",
                "_self",
                "_parent",
                "_top"
            ));
            self::$_purifier = new \HTMLPurifier($config);
        }
        $html = self::$_purifier->purify($html);
        
        return $html;
    }
}
