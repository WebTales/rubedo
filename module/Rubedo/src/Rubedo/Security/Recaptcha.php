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

use Rubedo\Interfaces\Security\IHash;
use Rubedo\Services\Manager;

/**
 * service to retrieve recaptcha key
 *
 * Hash a string with a salt
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Recaptcha
{
    protected static $key;
    
    public function getKeyPair($siteId = null){
        if(!$siteId){
            return $this->getGlobalKey();
        }
        $site = Manager::getService('Sites')->findById($siteId);
        if(!site || !isset($site['recaptcha']) || !is_array($site['recaptcha'])){
            return $this->getGlobalKey();
        }else{
            return $site['recaptcha'];
        }
    }
    
    protected function getGlobalKey(){
        if(!isset(self::$key)){
            $config = Manager::getService('Application')->getConfig();
            if(isset($config['rubedo_config']['recaptcha']) && !empty($config['rubedo_config']['recaptcha']['public_key']) && !empty($config['rubedo_config']['recaptcha']['private_key'])){
                self::$key =  $config['rubedo_config']['recaptcha'];
            }else{
                self::$key = null;
            }
        }
        return self::$key;
    }
    
    
}
