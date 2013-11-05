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

use Rubedo\Services\Manager;
use Zend\Debug\Debug;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class SignUpController extends AbstractController
{

    public function indexAction ()
    {
        if ($this->getRequest()->isPost()){
            $params = $this->params()->fromPost();
            $output = $this->params()->fromQuery();
            if ((!isset($params['name']))||(!isset($params['email']))||(!isset($params['userTypeId']))){
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $userType=Manager::getService('UserTypes')->findById($params['userTypeId']);
            if ($userType['signUpType']=="none") {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            }
            $newUser=array();
            $newUser['name']=$params['name'];
            $newUser['email']=$params['email'];
            $newUser['login']=$params['email'];
            $newUser['typeId']=$params['userTypeId'];
            $newUser['defaultGroup']=$userType['defaultGroup'];
            $newUser['groups']=array($userType['defaultGroup']);
            $newUser['taxonomy']=array();
            unset($params['name']);
            unset($params['email']);
            unset($params['userTypeId']);
            $newUser['fields']=$params;
            if ($userType['signUpType']=="open") {
                $newUser['status']="approved";
            } else if ($userType['signUpType']=="moderated") {
                $newUser['status']="pending";
            }
            
            $createdUser=Manager::getService('Users')->create($newUser);
            if (!$createdUser['success']) {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/fail.html.twig");
                return $this->_sendResponse($output, $template);
            } else {
                $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signup/done.html.twig");
                return $this->_sendResponse($output, $template);
            }
        }
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        if (! isset($blockConfig['userType'])) {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['userTypeId']=$blockConfig['userType'];
        $userType=Manager::getService('UserTypes')->findById($blockConfig['userType']);
        if ($userType['signUpType']=="none") {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['fields']=$userType['fields'];
        if ((isset($blockConfig['introduction'])) && ($blockConfig['introduction'] != "")) {
            $content = Manager::getService('Contents')->findById($blockConfig["introduction"], true, false);
            $output['contentId'] = $blockConfig["introduction"];
            $output['text'] = $content["fields"]["body"];
            $output["locale"] = isset($content["locale"]) ? $content["locale"] : null;
        }
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/signUp.html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
    
   
}
