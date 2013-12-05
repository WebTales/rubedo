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
interface IHash
{

    /**
     * Hash the string given in parameter
     *
     * @param String $string is
     *            the string destined to be hashed
     * @param String $salt is
     *            the string hashed with the string
     *
     * @return String $hash The string hashed
     */
    public function hashString($string, $salt);

    /**
     * Hash a password
     *
     * @param String $password password
     * @param String $salt is
     *            the string hashed with the password
     *
     * @return String $hash password hashed
     */
    public function derivatePassword($password, $salt);

    /**
     * Compare the password already hashed with a string hashed in the function
     * If they are equals, the function return true
     *
     * @param String $hash is
     *            the string already hashed
     * @param String $password password
     *            to hash
     * @param String $salt is
     *            the string hashed with the password
     *
     * @return bool
     */
    public function checkPassword($hash, $password, $salt);

    /**
     * Generate a string of random chars
     *
     * @param int $length
     * @return mixed
     */
    public function generateRandomString($length = 10);
}
