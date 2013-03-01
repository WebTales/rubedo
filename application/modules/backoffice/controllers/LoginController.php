<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

/**
 * Login Default Controller
 * 
 * Invoked when calling /backoffice/login URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_LoginController extends Zend_Controller_Action
{

	/**
     * Variable for Authentication service
	 * 
	 * @param 	Rubedo\Interfaces\User\IAuthentication
     */
    protected $_auth;
	
	/**
	 * Init the authentication service
	 */
    public function init() {
        $this->_auth = Rubedo\Services\Manager::getService('Authentication');
        $this->getHelper('Layout')->disableLayout();
        
    }
	
	/**
	 * Redirect the user to the backoffice if he's connected
	 */
    public function indexAction ()
    {
        if($this->_auth->getIdentity()){
			$this->_helper->redirector->gotoUrl("/backoffice/");
		}
		
		$extjsOptions = Zend_Registry::get('extjs');
		
		if(isset($extjsOptions['network']) && $extjsOptions['network']=='cdn'){
		    $this->view->extJsPath = 'http://cdn.sencha.com/ext-'.$extjsOptions['version'].'-gpl';
		}else{
		    $this->view->extJsPath = $this->view->baseUrl().'/components/sencha/extjs';
		}
		
		
		if(!isset($extjsOptions['debug']) ||$extjsOptions['debug']==true){
		    $this->view->extJsScript = 'ext-all-debug.js';
		}else{
		    $this->view->extJsScript = 'ext-all.js';
		}
		
		$this->getHelper('Layout')
		->disableLayout();
    }
	
}

