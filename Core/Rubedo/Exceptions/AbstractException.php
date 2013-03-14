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
        $message = $this->_translate($message, $language);
        parent::__construct($message, $code, $previous);
        
    }
    
    protected function _translate($message,$language='en'){
        return $message;
    }
}
