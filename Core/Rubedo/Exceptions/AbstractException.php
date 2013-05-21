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

    public function __construct ($message = null, $code = null, $previous = null) {
        $language = 'fr';
        $extraParams=array();
        $recievedArgs=func_get_args();
        if (isset($recievedArgs[3])){
        	$extraParams=array_slice($recievedArgs,3);
        }
        $message = $this->_translate($message, $code, $language, $extraParams);
        parent::__construct($message, $code, $previous);        
    }
    
    protected function _translate($message, $code, $language='en',$extraParams=array()){
    	//convert message to proper language using $code and $langage, use $message directly if nothing more appropriate coud be found
    	
    	//apply params to message if there are any
    	if (count($extraParams)>0){
    		$message=sprintf($message,$extraParams);
    	}
        return $message;
    }
}
