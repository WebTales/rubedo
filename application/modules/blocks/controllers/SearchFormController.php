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
class Blocks_SearchFormController extends Blocks_AbstractController
{

    /**
     * Default Action
     */
    public function indexAction ()
    {
        // get block config
		$blockConfig = $this->getParam('block-config', array());
		
        if (isset($blockConfig['searchPage'])) {
            $searchPage = $blockConfig['searchPage'];
        } else {
            $searchPage = null;
        }		
        
        
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/searchForm.html.twig");
        
        $css = array();
        $js = array();
        
        $output = $this->getAllParams();
		$output['searchPage'] = $searchPage;
		$output['placeholder']=isset($blockConfig['placeholder'])?$blockConfig['placeholder']:null;
		
        $this->_sendResponse($output, $template, $css, $js);
    }
}
