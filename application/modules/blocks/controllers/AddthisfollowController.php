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
		//\Zend_Debug::dump($blockConfig);die();
		$networks=$blockConfig;
		unset($networks["disposition"]);
		unset($networks["small"]);
		foreach($networks as $name=>$user)
		{
			$fields["network"]=$name;
			$fields["userId"]=$user;
			$data[]=$fields;
		}
		$output = $this->getAllParams();
		$output['networks']=$data;
		$output["type"]=isset($blockConfig["disposition"])?$blockConfig["disposition"]:"Horizontal";
		$output['small']=isset($blockConfig['small'])?$blockConfig['small']:false;
		//\Zend_Debug::dump($data);die();
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/addthisfollow.html.twig");
        
        $css = array();
        $js = array('//s7.addthis.com/js/300/addthis_widget.js');
        $this->_sendResponse($output, $template, $css, $js);
    }
}