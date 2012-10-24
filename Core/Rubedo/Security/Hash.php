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
namespace Rubedo\Security;

use Rubedo\Interfaces\Security\IHash;

/**
 * Current Hash Service
 *
 * Hash a string with a salt
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Hash implements IHash {

    private $_algo = 'sha512';

    /**
     * Hash the string given in parameter
     *
     * @param $string is the string destined to be hashed
     *
     * @return $hash The string hashed
     */
    public function hashString($string) {
        $hash = hash($this->_algo, $string);

        return $hash;
    }

    /**
     * Hash a password
     *
     * @param $pwd password
     * @param $salt is the string hashed with the password
     *
     * @return $hash password hashed
     */
    public function derivatePassword($pwd, $salt) {

        for ($i = 0; $i < 10; $i++) {
            $pwd = hash($this->_algo, $salt . $pwd);
        }

        return $pwd;
    }

    /**
     * Compare the password already hashed with a string hashed in the function
     * If they are equals, the function return true
     *
     * @param $hash is the string already hashed
     * @param $pwd password to hash
     * @param $salt is the string hashed with the password
     *
     * @return bool
     */
    public function checkPassword($hash, $pwd, $salt) {
        for ($i = 0; $i < 10; $i++) {
            $pwd = hash($this->_algo, $salt . $pwd);
        }

        if ($pwd === $hash) {
            return true;
        } else {
            return false;
        }
    }

}
