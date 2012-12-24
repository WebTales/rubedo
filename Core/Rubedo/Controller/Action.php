<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace Rubedo\Controller;

/**
 * Rquest object Use to handle block contents as MVC part
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Action
{
    /**
     * mock class if needed
     */
    protected static $_mock = null;

    /**
     * Reset the mockObject array for isolation purpose
     */
    public function resetMocks() {
        self::$_mock = null;
    }

    /**
     * Set a mock object for testing purpose
     *
     * @param object $obj mock object substituted
     */
    public function setMock($obj) {
        self::$_mock = $obj;
    }

    /**
     * @var string
     */
    public $defaultModule;

    /**
     * @var \Zend_Controller_Dispatcher_Interface
     */
    public $dispatcher;

    /**
     * @var \Zend_Controller_Request_Abstract
     */
    public $request;

    /**
     * @var \Zend_Controller_Response_Abstract
     */
    public $response;

	/**
	 * Getter static to request instance of this plug to block MVC
	 * 
	 * @return Action
	 */
	public static function getInstance(){
		if(self::$_mock instanceof self){
			return self::$_mock;
		}else{
			return new static;
		}
	}

    /**
     * Constructor
     *
     * Grab local copies of various MVC objects
     *
     * @return void
     */
    private function __construct() {
        $front = \Zend_Controller_Front::getInstance();
        $modules = $front->getControllerDirectory();

        $request = $front->getRequest();

        $this->request = clone $request;
        $this->response = new Response();
        $this->dispatcher = clone $front->getDispatcher();
        $this->defaultModule = $front->getDefaultModule();
    }

    /**
     * Reset object states
     *
     * @return void
     */
    public function resetObjects() {
        $params = $this->request->getUserParams();
        foreach (array_keys($params) as $key) {
            $this->request->setParam($key, null);
        }

        $this->response->clearBody();
        $this->response->clearHeaders()->clearRawHeaders();
    }

    /**
     * Retrieve rendered contents of a controller action
     *
     * If the action results in a forward or redirect, returns empty string.
     *
     * @param  string $action
     * @param  string $controller
     * @param  string $module Defaults to default module
     * @param  array $params
     * @return string
     */
    public function action($action, $controller, $module = null, array $params = array()) {
        $this->resetObjects();
        if (null === $module) {
            $module = $this->defaultModule;
        }

        // clone the view object to prevent over-writing of view variables
        $viewRendererObj = \Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        \Zend_Controller_Action_HelperBroker::addHelper(clone $viewRendererObj);

        $this->request->setParams($params)->setModuleName($module)->setControllerName($controller)->setActionName($action)->setDispatched(true);

        $this->dispatcher->dispatch($this->request, $this->response);

        // reset the viewRenderer object to it's original state
        \Zend_Controller_Action_HelperBroker::addHelper($viewRendererObj);

        if (!$this->request->isDispatched() || $this->response->isRedirect()) {
            // forwards and redirects render nothing
            return '';
        }

        $return = $this->response;
        return $return;
    }

}
