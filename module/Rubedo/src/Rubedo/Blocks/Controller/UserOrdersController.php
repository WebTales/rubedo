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
use WebTales\MongoFilters\Filter;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class UserOrdersController extends AbstractController
{

    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $output=$this->params()->fromQuery();
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            $output['errorMessage'] = "Blocks.UserProfile.error.noUser";
            $template = "blocks/userProfile/error.html.twig";
            return $this->_sendResponse($output, $template);
        }
        $filter=Filter::factory()->addFilter(Filter::factory('Value')->setName('userId')->setValue($currentUser['id']));
        $orders = Manager::getService("Orders")->getList($filter, array(array('property' => 'createTime', 'direction' => 'desc')));
        $paymentMeansService = Manager::getService("PaymentConfigs");
        $output['orders']=$orders['data'];
        $dateService=Manager::getService("Date");
        foreach($output['orders'] as &$value){
            $value['hrDate']=$dateService->convertToYmd($value['createTime']);
            $paymentMeansConfig=$paymentMeansService->getConfigForPM($value['paymentMeans']);
            $value['paymentMeansLabel'] = $paymentMeansConfig['data']['displayName'];
        }
        $output['orderDetailPage'] = isset($blockConfig['orderDetailPage']) ? $blockConfig['orderDetailPage'] : false;
        if ($output["orderDetailPage"]) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true
            );

            $output['orderDetailPageUrl'] = $this->url()->fromRoute(null, array(
                'pageId' => $output["orderDetailPage"]
            ), $urlOptions);
        }
        $template = 'blocks/userOrders.html.twig';
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
