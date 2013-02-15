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
class Blocks_AddthisfollowController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array()); 
		foreach($blockConfig["networks"] as $network)
		{
			$fields["network"]=$network["name"];
			$fields["userId"]=$network['userId'];
			$data[]=$fields;
		}
		$output['networks']=$data;
		$output['type']=($blockConfig["horizontal"]==true)? 'horizontal':'vertical';
		$output['small']=$blockConfig['small'];
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/addthisfollow.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}