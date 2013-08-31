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
class MailingListController extends AbstractController
{

    protected $_defaultTemplate = 'mailinglist';

    public function indexAction ()
    {
        $blockConfig = $this->getRequest()->getParam('block-config');
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        
        $output = $this->getAllParams();
        $output['blockConfig'] = $blockConfig;
        
        $css = array();
        $js = array(
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/mailingList.js")
        );
        
        return $this->_sendResponse($output, $template, $css, $js);
    }

    /**
     * Allow to add an email into a mailing list
     *
     * @return json
     */
    public function xhrAddEmailAction ()
    {
        // Default mailing list
        $mailingListId = $this->getParam("mailing-list-id");
        if (! $mailingListId) {
            throw new \Rubedo\Exceptions\User("No newsletter associeted to this form.", "Exception18");
        }
        
        // Declare email validator
        $emailValidator = new \Zend\Validator\EmailAddress();
        
        // MailingList service
        $mailingListService = \Rubedo\Services\Manager::getService("MailingList");
        
        // Get email
        $email = $this->getParam("email");
        
        // Validate email
        if ($emailValidator->isValid($email)) {
            // Register user
            $suscribeResult = $mailingListService->subscribe($mailingListId, $email);
            return new JsonModel($suscribeResult);
        } else {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Adresse e-mail invalide"
            ));
        }
    }
}