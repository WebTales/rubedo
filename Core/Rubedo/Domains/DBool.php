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
namespace Rubedo\Domains;

/**
 * Validator for "bool" Domain
 * 
 * Should be a boolean
 * 
 * @author jbourdin
 *
 */
class DBool implements IDomains
{
    /**
     * Check if a value is valid for the current domain
     * 
     * @param mixed $value
     * @return boolean
     * @see Rubedo\Domains\IDomains::isValid()
     */
    public static function isValid($value){
        if(!is_bool($value)){
            return false;
        }
        return true;
    }
}