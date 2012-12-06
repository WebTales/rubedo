<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

Use Rubedo\Services\Manager;

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_TwigController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output = $$lang;
        $output['id'] = $id;
		
		$templateName = $this->getRequest()->getParam('template','block.html');
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath($templateName);

        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
