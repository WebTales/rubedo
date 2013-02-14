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
class Blocks_AudioController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());  
        $site = $this->getParam('site');
        $output['audioAutoPlay'] = isset($blockConfig['audioAutoplay']) ? $blockConfig['audioAutoplay'] : false;
        $output['audioPreload'] = isset($blockConfig['audioPreload']) ? $blockConfig['audioPreload'] : false;
		$output['audioControls'] = isset($blockConfig['audioControls']) ? $blockConfig['audioControls'] : false;
		$output['audioLoop'] = isset($blockConfig['audioLoop']) ? $blockConfig['audioLoop'] : false;
        $output['audioFile'] = isset($blockConfig['audioFile']) ? $blockConfig['audioFile'] : null;

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/audio.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
