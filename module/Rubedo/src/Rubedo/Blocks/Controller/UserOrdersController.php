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
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/userOrders.html.twig");
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
