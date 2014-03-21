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
namespace Rubedo\Payment\Controller;

use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Templates\Raw\RawViewModel;

/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractController extends AbstractActionController
{

    /**
     * name of the the payment means
     *
     * @var string
     */
    protected $paymentMeans;

    /**
     * native config for this payment means
     *
     * @var array
     */
    protected $nativePMConfig;

    /**
     * order being handled for payment
     *
     * @var array
     */
    protected $currentOrder;

    public function __construct()
    {
        if (empty($this->paymentMeans)) {
            throw new \Rubedo\Exceptions\Server("Payment means name is not set");
        }
        $pmConfig=Manager::getService("PaymentConfigs")->getConfigForPM($this->paymentMeans);
        if (!$pmConfig['success']){
            throw new \Rubedo\Exceptions\Server("Unable to retrieve payment means config config");
        }
        if (!$pmConfig['data']['active']){
            throw new \Rubedo\Exceptions\Server("Payment means is not activated");
        }
        $this->nativePMConfig=$pmConfig['data']['nativePMConfig'];
    }

    protected function getParamFromQuery($name = null, $default = null)
    {
        if ($this->getRequest()->getMethod() == 'POST') {
            return $this->params()->fromPost($name, $this->params()->fromQuery($name, $default));
        } else {
            return $this->params()->fromQuery($name, $default);
        }
    }

    protected function sendResponse(array $output, $template, array $css = null, array $js = null)
    {
        $output['classHtml'] = $this->getParamFromQuery('classHtml', '');
        $output['idHtml'] = $this->getParamFromQuery('idHtml', '');

        $output['lang'] = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        $this->_serviceTemplate = Manager::getService('FrontOfficeTemplates');
        $this->_servicePage = Manager::getService('PageContent');

        if (is_array($css)) {
            foreach ($css as $value) {
                $this->_servicePage->appendCss($value);
            }
        }
        if (is_array($js)) {
            foreach ($js as $value) {
                $this->_servicePage->appendJs($value);
            }
        }

        $viewModel = new RawViewModel($output);
        $viewModel->setTemplate($template);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    protected function initOrder (){
        $orderId=$this->params()->fromPost("order-id", null);
        if (empty($orderId)) {
            throw new \Rubedo\Exceptions\Server("Missing order param");
        }
        $order=Manager::getService("Orders")->findById($orderId);
        if (empty($order)) {
            throw new \Rubedo\Exceptions\Server("Order not found");
        }
        $this->currentOrder=$order;
    }

    protected function getOrderPrice (){
        if (empty($this->currentOrder)) {
            throw new \Rubedo\Exceptions\Server("Order not set");
        }
        return $this->currentOrder['finalPrice'];
    }


}
