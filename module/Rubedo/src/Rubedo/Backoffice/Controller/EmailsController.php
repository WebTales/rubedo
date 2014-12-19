<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
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
    const NUM_BY_MAIL = 500;

    /**
     * Data Access Service
     *
     * @var \Rubedo\Collection\Emails
     */
    protected $_dataService;

    public function __construct()
    {
        parent::__construct();
        // init the data access service
        $this->_dataService = Manager::getService('Emails');
    }

    public function previewAction()
    {
        $mail = $this->_dataService->findById($this->getRequest()->getQuery('id'));
        $html = $this->_dataService->htmlConstructor($mail['text'], $mail["bodyProperties"], $mail["rows"], false);
        return $this->getResponse()->setContent($html);
    }

    public function sendAction()
    {
        /**
         * @var $mailingListService \Rubedo\Interfaces\Collection\IMailingList
         * @var $usersService \Rubedo\Interfaces\Collection\IUsers
         * @var $valueFilter \WebTales\MongoFilters\ValueFilter
         */
        $mailingListService = Manager::getService('MailingList');
        $usersService = Manager::getService('Users');

        $mail = $this->_dataService->findById($this->params()->fromPost('id'));
        $list = $mailingListService->findById($this->params()->fromPost('list'));

        //Get Recipients
        $valueFilter = Filter::factory('Value');
        $valueFilter
            ->setName('mailingLists.' . $list['id'] . '.status')
            ->setValue(true);
        $filters = Filter::factory()
            ->addFilter($valueFilter);
        $users = $usersService->getList($filters);

        $to = array();
        $allSendResult = true;
        $count = 0;
        foreach ($users['data'] as $user) {
            $index = (int)floor($count++ / static::NUM_BY_MAIL);
            $to[$index][$user['email']] = ($user['name']) ?: $user['login'];
        }

        $html = $this->_dataService->htmlConstructor($mail['text'], $mail["bodyProperties"], $mail["rows"], true);
        foreach ($to as $recipients) {
            $this->_dataService
                ->setSubject(!empty($mail['subject']) ? $mail['subject'] : $mail['text'])
                ->setMessageHTML($html)
                ->setFrom($list)
                ->setTo($recipients);

            if (!empty($mail['plainText'])) {
                $this->_dataService->setMessageTXT($mail['plainText']);
            }
            $sendResult = $this->_dataService->send();
            if ($sendResult <= 0) {
                $allSendResult = false;
                break;
            }
        }

        return new JsonModel(array('success' => $allSendResult));
    }
}