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
namespace Rubedo\Exceptions;

Use Rubedo\Services\Manager;

/**
 * Abstract exception : handle message translation
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
abstract class AbstractException extends \Exception
{
    protected static $doNotTranslate = false;

    public function __construct ($message = null, $code = null) {
        $extraParams=array();
        $recievedArgs=func_get_args();
        if (isset($recievedArgs[2])){
        	$extraParams=array_slice($recievedArgs,2);
        }
        $message = $this->_translate($message, $code, $extraParams);
        parent::__construct($message);        
    }
    
    
    
    /**
     * @param boolean $doNotTranslate
     */
    public static function setDoNotTranslate ($doNotTranslate)
    {
        AbstractException::$doNotTranslate = $doNotTranslate;
    }

	protected function _translate($message, $code,$extraParams=array()){
        
    	//convert message to proper language using $code and $langage, use $message directly if nothing more appropriate coud be found
    	if(!static::$doNotTranslate && $code){
    	    $message = Manager::getService('Translate')->translate($code,$message);
    	}
        
    	//apply params to message if there are any
    	if (count($extraParams)>0){
    	    $message = call_user_func_array('sprintf',array_merge(array($message),$extraParams));
    	}
        return $message;
    }
}
