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
namespace Rubedo\Collection;

use Rubedo\Services\Events;
use Rubedo\Services\Manager;

/**
 * Service to handle Orders
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class Orders extends AbstractCollection
{
    const POST_CREATE_ORDERS = 'rubedo_orders_create_post';
    const POST_UPDATE_STATUS_ORDERS = 'rubedo_orders_status_update_post';

    public function __construct()
    {
        $this->_collectionName = 'Orders';
        parent::__construct();
    }

    /**
     * Creates order, performs stock decrement
     *
     * @param $orderData
     * @return array
     */
    public function createOrder($orderData)
    {
        $siteId = Manager::getService('PageContent')->getCurrentSite();
        $date = new \DateTime();
        $date = $date->format('Y-m-d');
        $date = str_replace('-', '', $date);
        $incremental = $this->getIncrement($date);
        $orderData['dateCode'] = $date;
        if ($siteId) {
            $orderData['siteId'] = $siteId;
        }
        $orderData['incrementalCode'] = $incremental;
        $orderData['orderNumber'] = $date . $incremental;
        $createdOrder = $this->create($orderData);
        if (!$createdOrder['success']) {
            return $createdOrder;
        }
        $orderData = $createdOrder['data'];
        $contentTypesService = Manager::getService('ContentTypes');
        $contentsService = Manager::getService('Contents');
        $stockService = Manager::getService('Stock');
        foreach ($orderData['detailedCart']['cart'] as $value) {
            $content = $contentsService->findById($value['productId'], true, false);
            $productType = $contentTypesService->findById($content['typeId']);
            if ($productType['manageStock']) {
                $stockExtraction = $stockService->decreaseStock($value['productId'], $value['variationId'], $value['amount']);
                if (!$stockExtraction['success']) {
                    $orderData['hasStockDecrementIssues'] = true;
                    $orderData['stockDecrementIssues'][] = $value;
                }
            }
        }
        $updatedOrder = $this->update($orderData);
        return $updatedOrder;
    }

    public function getIncrement($dateCode)
    {
        $pipeline = array();
        $pipeline[] = array(
            '$match' => array(
                'dateCode' => $dateCode
            )
        );
        $pipeline[] = array(
            '$group' => array(
                '_id' => '$dateCode',
                'latestCode' => array(
                    '$max' => '$incrementalCode'
                ),
            )
        );
        $response = $this->_dataService->aggregate($pipeline);
        if (empty($response['result'])) {
            return 1;
        }
        return ($response['result'][0]['latestCode'] + 1);

    }

    public function create(array $obj, $options = array())
    {
        $result = parent::create($obj, $options);
        if ($result !== null) {
            Events::getEventManager()->trigger(self::POST_CREATE_ORDERS, $this, $result);
        }
        return $result;
    }

    public function update(array $obj, $options = array())
    {
        $beforeUpdate = $this->findById($obj['id']); //Not greedy if object cache is correctly used
        $result = parent::update($obj, $options);
        if ($result !== null && $beforeUpdate['status'] != $result['data']['status']) {
            Events::getEventManager()->trigger(self::POST_UPDATE_STATUS_ORDERS, $this, $result);
        }
        return $result;
    }

    public function sendCustomerNotification(\Zend\EventManager\Event $event)
    {
        $data = $event->getParam('data');
        /** @var \Rubedo\Collection\Users $usersCollection */
        $usersCollection = Manager::getService('Users');
        $user = $usersCollection->findById($data['userId']);
        $data['user'] = &$user;

        /** @var \Rubedo\Collection\Sites $sitesCollection */
        $sitesCollection = Manager::getService('Sites');
        $data['site'] = $sitesCollection->findById($data['siteId']);

        /** @var \Rubedo\Mail\Mailer $mailer */
        $mailer = Manager::getService('Mailer');

        $message = $mailer->getNewMessage();
        $config = Manager::getService('Config');
        $message->setFrom(
            array(
                $config['rubedo_config']['fromEmailNotification'] => 'Rubedo',
            )
        );
        $message->setTo(
            array(
                $user['email'] => $user['name'],
            )
        );
        /** @var \Rubedo\Internationalization\Translate $translateService */
        $translateService = Manager::getService('Translate');
        if ($event->getName() === self::POST_CREATE_ORDERS) {
            $subject = $translateService->translateInWorkingLanguage('Notification.Orders.CustomerNotification.Create.Subject');
        } else {
            $subject = $translateService->translateInWorkingLanguage('Notification.Orders.CustomerNotification.Update.Subject');
        }
        $message->setSubject($subject);
        /** @var \Rubedo\Templates\FrontOfficeTemplates $foTemplates */
        $foTemplates = Manager::getService('FrontOfficeTemplates');

        $template = $foTemplates->getFileThemePath('notification/orders/customerNotification.html.twig');
        $mailBody = $foTemplates->render($template, $data);

        $message->setBody($mailBody, 'text/html');

        return $mailer->sendMessage($message);
    }

    public function sendShopNotification(\Zend\EventManager\Event $event)
    {
        $data = $event->getParam('data');
        if (empty($data['siteId'])) {
            return false;
        }
        /** @var Sites $sitesCollection */
        $sitesCollection = Manager::getService('Sites');
        $site = $sitesCollection->findById($data['siteId']);

        if (empty($site['ecommerceNotificationEmails'])) {
            return false;
        }

        /** @var Users $usersCollection */
        $usersCollection = Manager::getService('Users');
        $user = $usersCollection->findById($data['userId']);
        $data['user'] = &$user;
        /** @var \Rubedo\Mail\Mailer $mailer */
        $mailer = Manager::getService('Mailer');
        $config = Manager::getService('Config');
        /** @var \Rubedo\Internationalization\Translate $translateService */
        $translateService = Manager::getService('Translate');
        $subject = $translateService->translateInWorkingLanguage(
            'Notification.Orders.ShopNotification.Create.Subject',
            '',
            array('%site%' => $site['title'])
        );
        /** @var \Rubedo\Templates\FrontOfficeTemplates $foTemplates */
        $foTemplates = Manager::getService('FrontOfficeTemplates');
        $template = $foTemplates->getFileThemePath('notification/orders/shopNotification.html.twig');
        $mailBody = $foTemplates->render($template, $data);
        $result = true;
        foreach ($site['ecommerceNotificationEmails'] as $email) {
            $message = $mailer->getNewMessage();
            $message->setFrom(
                array(
                    $config['rubedo_config']['fromEmailNotification'] => 'Rubedo',
                )
            );
            $message->setTo($email);
            $message->setSubject($subject);
            $message->setBody($mailBody, 'text/html');
            $result = $result && $mailer->sendMessage($message);
        }
        return $result;
    }
}
