<?php

class AccessController extends AbstractController
{

    public function loginAction()
    {

		$filename = '../www/digest/auth.txt';
		$realm = 'admin';
		$username = $this->_getParam('username');
		$password = $this->_getParam('password');
		
		$adapter = new Zend_Auth_Adapter_Digest($filename,
                                        $realm,
                                        $username,
                                        $password);
 
		$result = $adapter->authenticate();
 
		$identity = $result->getIdentity();

		if ($result->isValid()) {
			$storage = new Zend_Auth_Storage_Session();
            $storage->write($identity);
			$retour = array('success'=>true);
		} else {
			$defaultNamespace->loggedIn = false;
			$retour = array('success'=>false);
		}
		
		$this->getResponse()->setBody(Zend_Json::encode($retour));
		
    }

    public function logoutAction()
    {
    	$storage = new Zend_Auth_Storage_Session();
        $storage->clear();
		
		$retour = array('success'=>true);
		$this->getResponse()->setBody(Zend_Json::encode($retour));
    }
	
	public function loggedinAction()
	{
		$storage = new Zend_Auth_Storage_Session();
		
        $data = $storage->read();

        if($data){
            $retour = array('loggedIn'=>true, 'username'=>$data['username']);
        } else {
        	$retour = array('loggedIn'=>false);
        }
		
		$this->getResponse()->setBody(Zend_Json::encode($retour));
	}
	
}

