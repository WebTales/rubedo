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
namespace Rubedo\Blocks\Controller;

use \Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;
use Zend\Debug\Debug;
use Zend\Json\Json;
use WebTales\MongoFilters\Filter;

/**
 * Controller providing CRUD API for the MailingList JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *         
 */
class UnsubscribeController extends AbstractController
{

    protected $_defaultTemplate = 'unsubscribe';

    public function indexAction ()
    {
        $output = $this->params()->fromQuery();
        if ($this->getRequest()->isPost()){
            $email = $this->params()->fromPost("email");
        
            if ((!isset($email))||(empty($email))){
                $output['signupMessage']="Email invalide";
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $result=Manager::getService("MailingList")->unsubscribeFromAll($email);
            if (!$result['success']){
                $output['signupMessage']=$result['msg'];
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/emailconfirmerror.html.twig");
                return $this->_sendResponse($output, $template);
            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/unsubscribeok.html.twig");
                return $this->_sendResponse($output, $template);
            }
            
        }
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }


}