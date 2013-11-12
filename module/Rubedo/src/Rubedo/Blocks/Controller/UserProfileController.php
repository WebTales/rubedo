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
class UserProfileController extends AbstractController
{

    public function indexAction ()
    {
        //$blockConfig = $this->params()->fromQuery('block-config', array());
        $output = $this->params()->fromQuery();
        $currentUser=Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser){
            $output['errorMessage']="You need to be logged-in to view your profile";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/userProfile/error.html.twig");
            return $this->_sendResponse($output, $template);
        }

        $user=$currentUser;
        $userType=Manager::getService("UserTypes")->findById($user['typeId']);
        $output['user']=$user;
        $output['fieldTypes']=$userType['fields'];
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/userProfile.html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
