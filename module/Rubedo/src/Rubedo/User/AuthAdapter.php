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
namespace Rubedo\User;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
/**
 * Adapter to check authentication against mongoDB user collection
 *
 * Authenticate user and get information about him
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class AuthAdapter implements AdapterInterface
{

    /**
     * submited password
     *
     * @var string
     */
    private $_password = null;

    /**
     * submited login
     *
     * @var string
     */
    private $_login = null;

    /**
     * $_authenticateResultInfo
     *
     * @var array
     */
    protected $_authenticateResultInfo = null;

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate ()
    {
        $dataService = Manager::getService('MongoDataAccess');
        $dataService->init('Users');
        $dataService->addToFieldList(array(
            'login',
            'password',
            'salt',
            'startValidity',
            'endValidity'
        ));
        
        $hashService = Manager::getService('Hash');
        
        $loginCond = array(
            array(
                'login' => $this->_login
            ),
            array(
                'email' => $this->_login
            )
        );
        
        $loginCond = Filter::factory('Or');
        
        $loginFilter = Filter::factory('Value')->setName('login')->setValue($this->_login);
        $loginCond->addFilter($loginFilter);
        
        $emailFilter = Filter::factory('Value')->setName('email')->setValue($this->_login);
        $loginCond->addFilter($emailFilter);
        
        $dataService->addFilter($loginCond);
        $resultIdentitiesArray = $dataService->read();
        $resultIdentities = $resultIdentitiesArray['data'];
        if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->_authenticateResultInfo['code'] = Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }
        
        $user = array_shift($resultIdentities);
        $salt = $user['salt'];
        $targetHash = $user['password'];
        unset($user['password']);
        
        $valid = $hashService->checkPassword($targetHash, $this->_password, $salt);
        
        $currentTime = Manager::getService('CurrentTime')->getCurrentTime();
        if ($valid && isset($user['startValidity']) && ! empty($user['startValidity'])) {
            $valid = $valid && ($user['startValidity'] <= $currentTime);
            if (! $valid) {
                $this->_authenticateResultInfo['messages'][] = 'User account is not yet active';
            }
        }
        
        if ($valid && isset($user['endValidity']) && ! empty($user['endValidity'])) {
            $valid = $valid && ($user['endValidity'] > $currentTime);
            if (! $valid) {
                $this->_authenticateResultInfo['messages'][] = 'User account is no longer active';
            }
        }
        
        if ($valid) {
            $this->_authenticateResultInfo['code'] = Result::SUCCESS;
            $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
            $this->_authenticateResultInfo['identity'] = $user;
            return $this->_authenticateCreateAuthResult();
        } else {
            $this->_authenticateResultInfo['code'] = Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }
    }

    /**
     * Initialise class with login and password
     *
     * @param
     *            $name
     * @param
     *            $password
     */
    public function __construct ($name, $password)
    {
        if (! is_string($name)) {
            throw new \Rubedo\Exceptions\Server('$name should be a string', "Exception40", '$name');
        }
        if (! is_string($password)) {
            throw new \Rubedo\Exceptions\Server('$password should be a string', "Exception40", '$password');
        }
        $this->_authenticateResultInfo['identity'] = null;
        $this->_login = $name;
        $this->_password = $password;
    }

    /**
     * _authenticateCreateAuthResult() - Creates a Zend_Auth_Result object from
     * the information that has been collected during the authenticate()
     * attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult ()
    {
        return new Result($this->_authenticateResultInfo['code'], $this->_authenticateResultInfo['identity'], $this->_authenticateResultInfo['messages']);
    }
}
