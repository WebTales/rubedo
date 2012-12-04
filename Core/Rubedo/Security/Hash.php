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
class Hash implements IHash
{

    /**
     * Contain the current algo for the hash
     */
    private $_algo = 'sha512';

    /**
     * Hash the string given in parameter
     *
     * @param $string is the string destined to be hashed
     * @param $salt is the string hashed with the string
     *
     * @return $hash The string hashed
     */
    public function hashString($string, $salt) {
        if (gettype($string) !== 'string') {
            throw new \Rubedo\Exceptions\Hash('$string should be a string');
        }

        $hash = hash($this->_algo, $salt . $string);

        return $hash;
    }

    /**
     * Hash a password
     *
     * @param $password password
     * @param $salt is the string hashed with the password
     *
     * @return $hash password hashed
     */
    public function derivatePassword($password, $salt) {
        if (gettype($password) !== 'string') {
            throw new \Rubedo\Exceptions\Hash('$password should be a string');
        }

        for ($i = 0; $i < 10; $i++) {
            $password = hash($this->_algo, $salt . $password);
        }

        return $password;
    }

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
    public function checkPassword($hash, $password, $salt) {
        if (gettype($password) !== 'string') {
            throw new \Rubedo\Exceptions\Hash('$password should be a string');
        }

        $password = $this->derivatePassword($password, $salt);

        if ($password === $hash) {
            return true;
        } else {
            return false;
        }
    }

    public function generateRandomString($length = 10) {
        // Create a random string for the salt
        $characters = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmonopqrstuvwxyz123456789');
        $nbChar = count($characters);
        //shuffle($caracters);
        for ($i = 0; $i < $length; $i++) {
            $resultArray[] = $characters[rand(0, $nbChar - 1)];
        }
        //$caracters = array_slice($caracters, 0, $length);
        $salt = implode('', $resultArray);

        return $salt;
    }

}
