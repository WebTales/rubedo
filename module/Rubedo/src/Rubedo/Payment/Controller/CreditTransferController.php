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
/**
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class CreditTransferController extends AbstractController
{

    public function __construct()
    {
        $this->paymentMeans = 'creditTransfer';
        parent::__construct();
    }


    public function indexAction ()
    {
        $this->initOrder();
        $content = array();
        if ($this->nativePMConfig["contentId"]) {
            $content = Manager::getService('Contents')->findById($this->nativePMConfig["contentId"], true, false);
        }
        if (! $content) {
            return $this->sendResponse(array(), "block.html.twig");
        }
        $output = $this->params()->fromQuery();
        $output['contentId'] = $this->nativePMConfig["contentId"];
        $price=$this->getOrderPrice();
        $output['price']=$price;
        $output['text'] = str_replace("###price###", $price." €", $content["fields"]["body"]);
        $output["locale"] = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/richtext.html.twig");
        $css = array();
        $js = array();
        return $this->sendResponse($output, $template, $css, $js);
    }
}
