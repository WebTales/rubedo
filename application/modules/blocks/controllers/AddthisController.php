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
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_AddthisController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array()); 
		$output['type']=($blockConfig["horizontal"]==true)? 'horizontal':'vertical';
		$output['small']=$blockConfig['small'];
		$output['like']=$blockConfig['like']; 
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/addthis.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}

