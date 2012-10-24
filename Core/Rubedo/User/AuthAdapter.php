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
namespace Rubedo\User;

/**
 * Adapter to check authentication against mongoDB user collection
 *
 * Authenticate user and get information about him
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class AuthAdapter implements \Zend_Auth_Adapter_Interface {
	
	/**
	 * submited password
	 * @var string
	 */
	private $_password = null;
	
	/**
	 * submited login
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
 	public function authenticate() {
 		$dataService = \Rubedo\Services\Manager::getService('MongoDataAccess');
		$dataService->init('Users');
		
		$hashService = \Rubedo\Services\Manager::getService('Hash');
		
		$dataService->addFilter(array('login'=>$this->_login));		
		$resultIdentities = $dataService->read();
		
		if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = \Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->_authenticateResultInfo['code'] = \Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }
		
		$user = array_shift($resultIdentities);
		$salt = $user['salt'];
		$targetHash = $user['password'];
		unset($user['password']);
		
		if($hashService->checkPassword($targerHash,$this->_password,$salt)){
			$this->_authenticateResultInfo['code'] = \Zend_Auth_Result::SUCCESS;
        	$this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
			$this->_authenticateResultInfo['identity'] = $user;
        	return $this->_authenticateCreateAuthResult();
		}else{
			$this->_authenticateResultInfo['code'] = \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
		}
		
	}
 
 	/**
	 * Initialise class with login and password
	 * 
	 * @param $name
	 * @param $password
	 */
 	public function __construct($name,$password){
 		if(!is_string($name)){
 			throw new \Rubedo\Exceptions\Authentication('$name should be a string', 1);
 		}
		if(!is_string($password)){
 			throw new \Rubedo\Exceptions\Authentication('$password should be a string', 1);
 		}
		
		$this->_login = $name;
		$this->_password = $password;
 	}
 
 
 	/**
     * _authenticateCreateAuthResult() - Creates a Zend_Auth_Result object from
     * the information that has been collected during the authenticate() attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new \Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
            );
    }
}
