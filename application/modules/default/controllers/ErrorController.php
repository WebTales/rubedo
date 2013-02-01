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

/**
 * Front Office Error Controller
 *
 * Invoked when somthing failed and is catchable a the last moment
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ErrorController extends Zend_Controller_Action
{

    /**
     * Main Action of this controller
     *
     * Return an error message and can expose the failure description
     */
    public function errorAction ()
    {
        $errors = $this->_getParam('error_handler');
        
        if (! $errors || ! $errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        if ($errors->type == Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER) {
            $exceptionType = get_class($errors->exception);
            
            switch ($exceptionType) {
                case 'Rubedo\\Exceptions\\Access':
                    $errors->type = 'access';
                    break;
                case 'Rubedo\\Exceptions\\User':
                    $errors->type = 'user';
                    break;
                case 'Rubedo\\Exceptions\\Server':
                    $errors->type = 'server';
                    break;
                case 'Rubedo\\Exceptions\\NotFound':
                    $errors->type = 'notFound';
                    break;
                default:
                    break;
            }
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            case 'notFound':
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            case 'access':
                $this->getResponse()->setHttpResponseCode(403);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Forbidden';
                break;
            case 'user':
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'User error';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $returnArray = array();
            $returnArray['success'] = false;
            $returnArray['msg'] = $errors->exception->getMessage();
            $this->_helper->json($returnArray);
        }
        
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request = $errors->request;
    }
}

