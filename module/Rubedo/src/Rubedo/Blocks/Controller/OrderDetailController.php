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
namespace Rubedo\Blocks\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class OrderDetailController extends AbstractController
{

    public function indexAction ()
    {
        $output = $this->params()->fromQuery();
        $currentUser = Manager::getService("CurrentUser")->getCurrentUser();
        if (!$currentUser) {
            $output['errorText'] = "Authenticated user required to view order.";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/genericError.html.twig");
            return $this->_sendResponse($output, $template);
        }
        if ((!isset($output['order']))||(empty($output['order']))){
            $output['errorText'] = "Missing order parameter.";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/genericError.html.twig");
            return $this->_sendResponse($output, $template);
        }
        $filters=Filter::factory()->addFilter(Filter::factory('Value')->setName('userId')->setValue($currentUser['id']))->addFilter(Filter::factory('Value')->setName('orderNumber')->setValue($output['order']));
        $order=Manager::getService("Orders")->findOne($filters);
        if (!$order){
            $output['errorText'] = "Order not found.";
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/genericError.html.twig");
            return $this->_sendResponse($output, $template);
        }
        $output['order']=$order;
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/orderDetail.html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
    
}
