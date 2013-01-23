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
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ImageController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
            
        $site = $this->getParam('site');
        $output['imageLink'] = isset($site['imageLink']) ? $site['imageLink'] : null;
        $output['imageAlt'] = isset($site['imageAlt']) ? $site['imageAlt'] : null;
        $output['imageFile'] = isset($site['imageFile']) ? $site['imageFile'] : null;
        $output['imageWidth'] = isset($site['imageWidth']) ? $site['imageWidth'] : null;
        $output['imageHeight'] = isset($site['imageHeight']) ? $site['imageHeight'] : null;
                                      
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/image.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
