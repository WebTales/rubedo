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
class MailingListController extends AbstractController
{

    protected $_defaultTemplate = 'mailinglist';

    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config');
        $mailingListService = Manager::getService("MailingList");
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        
        $output = $this->params()->fromQuery();
        $output['blockConfig'] = $blockConfig;
        $mailingListArray=array();
        foreach ($blockConfig['mailingListId'] as $value){
            $myList=$mailingListService->findById($value);
            if ($myList){
                $mailingListArray[]=array(
                    "label"=>$myList['name'],
                    "value"=>$value
                );
            }
        }
        $output['mailingListArray']=$mailingListArray;
        $filters = Filter::factory();
        $filters->addFilter(Filter::factory('Value')->setName('UTType')
            ->setValue("email"));
        $emailUserType = Manager::getService("UserTypes")->findOne($filters);
        $output['fields']=$emailUserType['fields'];
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/mailingList.js")
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
        $mailingListIdArray = $this->params()->fromPost("mailing-list-id", "[ ]");
        $mailingListIdArray = Json::decode( $mailingListIdArray, Json::TYPE_ARRAY);
        if (empty($mailingListIdArray)) {
            throw new \Rubedo\Exceptions\User("No newsletter associeted to this form.", "Exception18");
        }
        $name = $this->params()->fromPost("name");
        $fieldsArray= $this->params()->fromPost("fields", "[ ]");
        $fieldsArray = Json::decode( $fieldsArray, Json::TYPE_ARRAY);
        
        // Declare email validator
        $emailValidator = new \Zend\Validator\EmailAddress();
        
        // MailingList service
        $mailingListService = Manager::getService("MailingList");
        
        // Get email
        $email = $this->params()->fromPost("email");
        
        // Validate email
        if ($emailValidator->isValid($email)) {
            // Register user
            $response = array(
                "success" => true,
                "msg" => "Inscription réussie"
            );
            foreach ($mailingListIdArray as $mailingListId){
                $suscribeResult = $mailingListService->subscribe($mailingListId, $email, false, $name, $fieldsArray);
                $response['success']=$response['success']&&$suscribeResult['success'];
            }
            return new JsonModel($response);
        } else {
            return new JsonModel(array(
                "success" => false,
                "msg" => "Adresse e-mail invalide"
            ));
        }
    }
}