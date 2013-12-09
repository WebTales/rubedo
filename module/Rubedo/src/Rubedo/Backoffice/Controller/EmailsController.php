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
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\View\Model\JsonModel;

/**
 * Controller providing CRUD API for the emails JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class EmailsController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Emails');
    }

    public function previewAction()
    {
        $mail =$this->_dataService->findById($this->getRequest()->getQuery('id'));

        return $this->getResponse()->setContent($this->_dataService->htmlConstructor($mail['text'], $mail["bodyProperties"], $mail["rows"], false)) ;
    }

    public function sendAction()
    {
        $mail = $this->_dataService->findById($this->params()->fromPost('id'));
        $list = Manager::getService('MailingList')->findById($this->params()->fromPost('list'));

        $this->_dataService->setSubject(!empty($mail['subject']) ? $mail['subject'] : $mail['text']);
        $this->_dataService->setMessageHTML($this->_dataService->htmlConstructor($mail['text'], $mail["bodyProperties"], $mail["rows"], true));
        if (!empty($mail['plainText'])) {
            $this->_dataService->setMessageTXT($mail['plainText']);
        }
        $this->_dataService->setFrom($list);

        $filters =  Filter::factory()
            ->addFilter(Filter::factory('Value')
                ->setName('mailingLists.' . $list['id'] . '.status')
                ->setValue(true));
        $users = Manager::getService('Users')->getList($filters);

        $to = array();
        //@todo : refactor
        foreach ($users['data'] as $user) {
            $to[$user['email']] = ($user['name'])?:$user['login'];
        }
        $this->_dataService->setTo($to);
        return new JsonModel(array('success' => $this->_dataService->send() > 0 ? true : false));
    }
}