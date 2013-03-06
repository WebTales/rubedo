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
class Blocks_VideoController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());  
        $site = $this->getParam('site');
        $output = $this->getAllParams();
        $output['videoAutoPlay'] = isset($blockConfig['videoAutoplay']) ? $blockConfig['videoAutoplay'] : false;
        $output['videoPreload'] = isset($blockConfig['videoPreload']) ? $blockConfig['videoPreload'] : false;
		$output['videoControls'] = isset($blockConfig['videoControls']) ? $blockConfig['videoControls'] : false;
		$output['videoLoop'] = isset($blockConfig['videoLoop']) ? $blockConfig['videoLoop'] : false;
		$output['videoPoster'] = isset($blockConfig['videoPoster']) ? $blockConfig['videoPoster'] : null;
        $output['videoFile'] = isset($blockConfig['videoFile']) ? $blockConfig['videoFile'] : null;
        $output['videoWidth'] = isset($blockConfig['videoWidth']) ? $blockConfig['videoWidth'] : null;
        $output['videoHeight'] = isset($blockConfig['videoHeight']) ? $blockConfig['videoHeight'] : null;
                                      
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/video.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
