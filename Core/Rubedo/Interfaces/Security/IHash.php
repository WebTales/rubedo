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

namespace Rubedo\Interfaces\Security;

/**
 * Current Hash Service
 *
 * Hash a string with a salt
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IHash {

    /**
     * Hash the string given in parameter
     *
     * @param $string is the string destined to be hashed
	 * @param $salt is the string hashed with the string
     *
     * @return $hash The string hashed
     */
    public function hashString($string, $salt);

    /**
     * Hash a password
     *
     * @param $password password
     * @param $salt is the string hashed with the password
     *
     * @return $hash password hashed
     */
    public function derivatePassword($password, $salt);

    /**
     * Compare the password already hashed with a string hashed in the function
     * If they are equals, the function return true
     *
     * @param $hash is the string already hashed
     * @param $password password to hash
     * @param $salt is the string hashed with the password
     *
     * @return bool
     */
    public function checkPassword($hash, $password, $salt);

}
